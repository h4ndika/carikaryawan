# Photo scrape queue: implementation and operator handoff

## Root causes addressed

- The posts bulk action dispatched 25 IDs even though the queue page used chunks of 3.
- One worker claimed all rows, synchronously polled multiple runs, then retried empty results per place. A single batch could exceed its 600s timeout.
- Worker timeout (600s) exceeded the default database/Redis `retry_after` (90s), permitting duplicate delivery while the original worker was still running.
- API errors were collapsed into empty arrays; empty results could appear successful because an old gallery existed.
- Header/bulk/reset actions could reset a genuinely active row. There was no terminal failure callback or crash recovery.

## New execution model

`PhotoScrapeQueue::enqueue` is the single entry point from all three Filament actions/pages. It reserves eligible rows under a DB lock with an attempt UUID, then dispatches at most 3 rows per batch. Pending reserved or processing rows are skipped, not reset. Broker dispatch failures become explicit failed rows.

Each invocation does short work:
1. Claim a concurrency slot (default 3 active batches site-wide) under a shared cache lock.
2. Validate posts, preserve the separate company-logo path, start one Apify run.
3. Persist run/dataset IDs and encrypted credential snapshot, then **release the queue job for 15 seconds**.
4. Next invocation polls that same run once. No worker sleeps waiting for Apify.
5. Once succeeded, fetch the bounded dataset and commit each post independently. Empty/invalid results fail that row without starting another paid run or deleting the old gallery.

Actor: `lurkapi/google-maps-photos-and-videos-scraper` (same actor as prior opaque ID `KpQF2B9mcQFkExt8X`). Input category remains **`all`**. Valid categories: all, menu, food, vibe, by_owner, latest, videos, street_view. TLS verification enabled for these requests.

`timeout=75`, lock expiry=80, default retry_after=90. Poll/capacity releases can repeat (`tries=0`), but each run has a persisted 600-second deadline. The actor also receives a 600-second runtime cap. Read-only transient HTTP failures are retried at most twice with delay; invalid input/auth and terminal actor states fail with safe error codes. An ambiguous start/network error is **not retried automatically**: an upstream paid run may already exist.

`failed()` handles framework timeout/fatal failure using the UUID serialized before dispatch. Old messages cannot overwrite a newer retry. The `photos:recover-stale` scheduler marks overdue processing rows (deadline +90s grace), legacy processing rows older than 690s, and reserved pending rows older than 30 minutes as failed, with an explanation. This also covers SIGKILL/OOM/lost messages where failed() is not invoked. A very large backlog may hit the pending limit; increase worker capacity or submit smaller batches rather than repeatedly resetting it.

Filament polls every 5s, displays stage, deadline, errors and optional run ID. Retry/bulk actions skip active rows; recovery only touches overdue work. Finished/failed rows alone may be deleted. The original 2-photo auto-publish threshold for venues is unchanged.

## Deployment (Lead owns main)

There is **one additive migration**. Do not deploy mixed old/new workers: old workers would not honor the ownership token.

1. Pause photo submissions and gracefully drain/stop existing workers using the current Supervisor setup. Inspect Apify for still-active legacy runs before retrying them. Do not clear unrelated queue jobs.
2. After PR merge: `git pull origin main`, `composer install --no-dev --optimize-autoloader` if needed, then `php artisan migrate --force`.
3. `php artisan optimize:clear && php artisan queue:restart`, then resume Supervisor workers with the new code. Ensure all workers actually restarted.
4. Require an asynchronous queue (database or Redis), a shared cache lock backend (database or Redis, same configuration on every worker), stable APP_KEY for the encrypted credential snapshot, and active minute cron (`php artisan schedule:run`). Keep **retry_after > 80 seconds**; 90 is sufficient for this job, but review longer unrelated jobs separately. Do not lower their configuration as part of this change.
5. Optional `.env`: `PHOTO_SCRAPE_MAX_ACTIVE_RUNS=3`, `APIFY_TOKEN=...` fallback if no DB accounts. Lower max runs if Apify account memory/concurrency limits occur. This cap applies to this Laravel queue only, not standalone scraping tools or other actors.
6. `php artisan photos:recover-stale` can reconcile legacy abandoned rows. It does not automatically resubmit them or abort remote runs. Review the error/run ID, then use retry in Filament.
7. Controlled live smoke test with 1–3 known places: pending -> preparing -> polling -> completed/failed; verify category all, gallery, thumbnail, run ID, and no duplicate actor start. Then try one failing place and mixed batches. Check Apify costs before increasing concurrency.

No production deployment or paid Apify execution was performed for this PR. Actual actor runtime, success rate and throughput still depend on Google Maps/Apify and must be verified in the controlled smoke test.

Rollback: stop new workers first; restore prior code while leaving additive columns in place. New token-bearing payloads/remote runs must be reconciled before old workers consume that queue. Do not drop the migration while new workers or jobs exist.

## Validation

Focused suite: 40 tests / 156 assertions. Uses SQLite in-memory, actual photo-domain migrations, HTTP fakes (no API cost), real DB queue serialization/release and Filament Livewire action tests:

```sh
php -d extension=pdo_sqlite vendor/pestphp/pest/bin/pest tests/Feature/PhotoScrapePipelineTest.php
```

On Linux where pdo_sqlite is already enabled, omit `-d extension=pdo_sqlite`.

Tests isolate photo migrations because this local vendor install cannot load the unrelated `Laravel\Ai\Migrations\AiMigration` from the full migration chain. No global migrations/test configuration were changed. The existing homepage example test also has no database fixture setup; it is not a production health test.
