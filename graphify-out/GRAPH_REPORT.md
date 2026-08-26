# Graph Report - disekiar2  (2026-08-26)

## Corpus Check
- 243 files · ~160,125 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 6814 nodes · 19148 edges · 251 communities (234 shown, 17 thin omitted)
- Extraction: 88% EXTRACTED · 12% INFERRED · 0% AMBIGUOUS · INFERRED: 2283 edges (avg confidence: 0.71)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `08440692`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- code-editor.js
- components/chart.js
- rich-editor.js
- stat/chart.js
- .forEach
- update
- field
- draw
- markdown-editor.js
- _update
- prop
- components/select.js
- selectOption
- create
- fromObject
- advance
- ApifyAccount
- constructor
- _update
- resolve
- o
- e
- Illuminate\Database\Eloquent\Model
- slice
- A
- y
- dx
- jy
- notifications.js
- support.js
- te
- next
- updateElements
- slider.js
- _each
- get
- match
- parse
- f
- l
- marks
- Category
- parse
- t
- ls
- fn
- getDatasetMeta
- E
- inRange
- ar
- Oc
- Filament\Schemas\Schema
- ERD.md
- Cn
- fn
- Si
- MODULES.md
- dl
- public/app.js
- echo.js
- Ye
- 00-VISION.md
- facet
- getDatasetMeta
- find
- DuskTestCase.php
- 05-CODING-STANDARD.md
- V
- constructor
- Filament\Resources\Pages\ListRecords
- home.blade.php
- ei
- constructor
- file-upload.js
- tables.js
- filament/app.js
- devDependencies
- 08-UI-UX.md
- t
- N
- S
- closeDropdown
- .slice
- composer.json
- dependencies
- require-dev
- scripts
- HomePage
- 04-API.md
- require
- server/index.js
- PERMISSION_MODEL.md
- renderOptions
- Illuminate\Console\Command
- Xt
- config
- r
- addSingleBadge
- Post
- actions/actions.js
- PROMPT_REVIEW.md
- AppServiceProvider
- RahasiaPanelProvider.php
- post-show.blade.php
- psr-4
- 12-TODO.md
- 06-SEO.md
- schemas.js
- post-autoload-dump
- post-create-project-cmd
- scrapePlacePhotos
- graphify.js
- DOMAIN_MODEL.md
- components/actions.js
- _notify
- RunGoogleMapsScrapeJob.php
- SEO_STRATEGY.md
- post-list.blade.php
- db/index.js
- 03-DATABASE.md
- 13-DOMAIN.md
- 02-ARCHITECTURE.md
- 09-DEPLOYMENT.md
- 11-AGENTS.md
- accountCreator.js
- 10-CONTRIBUTING.md
- API_SPEC.md
- sliceDoc
- exporters/index.js
- 07-FEATURES.md
- PROMPT_REFACTOR.md
- GoogleMapsImportService
- n
- color-picker.js
- PROMPT_DEVELOPMENT.md
- URL_STRUCTURE.md
- PROMPT_ONBOARDING.md
- Filament\Tables\Table
- Analisis Project DISEKITAR
- ROLE_MATRIX.md
- En
- Nn
- columns/select.js
- Google Maps Data Extractor v2 (Node.js & Apify Edition)
- tasks/README.md
- 01-ROADMAP.md
- Cloud-Native Ready Philosophy
- pagination.blade.php
- nr
- FE‑001 — Public Frontend Foundation
- extra
- constructor
- Phase 1 — Core CMS
- P
- Core Content
- src/index.js
- Discovery Features
- README.md
- Production Readiness Checklist
- Phase 0 — Foundation
- Administration
- Business Platform
- AI Features
- Community Features
- Core Platform
- FE‑003 — Smoke Path Frontend
- FE‑002 — Vite Asset Build
- Phase 2 — Public Website
- DISEKITAR — Task Board
- Phase 5 — Business Platform
- Phase 4 — Community
- Phase 3 — Geo Intelligence
- Technical Improvements
- Environment
- opencode.json
- Backlog
- Current Sprint
- Next
- AGENTS.md
- Job Platform
- SEO Features
- Media Features
- Notification
- BUGS.md
- TECH-DEBT.md
- Milestone: Editorial Platform & AI Automation Engine (21 - 23 Agustus 2026)
- landmark-show.blade.php
- yP

## God Nodes (most connected - your core abstractions)
1. `o()` - 188 edges
2. `t()` - 154 edges
3. `i()` - 147 edges
4. `constructor()` - 145 edges
5. `update()` - 145 edges
6. `l()` - 140 edges
7. `resolve()` - 94 edges
8. `h()` - 93 edges
9. `u()` - 90 edges
10. `y()` - 89 edges

## Surprising Connections (you probably didn't know these)
- `scrapeGooglePlaces()` --indirect_call--> `err()`  [INFERRED]
  tools/google-map-extractor/src/scraper.js → public/js/filament/forms/components/rich-editor.js
- `node()` --indirect_call--> `t()`  [INFERRED]
  public/js/filament/forms/components/code-editor.js → public/js/filament/forms/components/date-time-picker.js
- `cm()` --indirect_call--> `me()`  [INFERRED]
  public/js/filament/forms/components/file-upload.js → public/js/filament/forms/components/code-editor.js
- `xQ()` --indirect_call--> `ay()`  [INFERRED]
  public/js/filament/forms/components/code-editor.js → public/js/filament/forms/components/rich-editor.js
- `constructor()` --indirect_call--> `i()`  [INFERRED]
  public/js/filament/forms/components/color-picker.js → public/js/filament/forms/components/date-time-picker.js

## Import Cycles
- None detected.

## Communities (251 total, 17 thin omitted)

### Community 0 - "code-editor.js"
Cohesion: 0.01
Nodes (125): aa(), Ac(), addCompletion(), addCompletions(), addNamespace(), addNamespaceObject(), Ag(), AX() (+117 more)

### Community 1 - "components/chart.js"
Cohesion: 0.01
Nodes (113): Il(), abutsStart(), addControllers(), addPlugins(), addScales(), afterDraw(), bd(), Be() (+105 more)

### Community 2 - "rich-editor.js"
Cohesion: 0.01
Nodes (167): [g](), aa(), addHackNode(), addNodeMark(), addTextblockHacks(), applyAspectRatio(), applyConstraints(), atEnd() (+159 more)

### Community 3 - "stat/chart.js"
Cohesion: 0.03
Nodes (82): themeClasses(), an(), applyStack(), ba(), beforeDatasetsDraw(), beforeDraw(), Bn(), color() (+74 more)

### Community 4 - ".forEach"
Cohesion: 0.05
Nodes (64): addAttributes(), addOptions(), af(), an(), $b(), B0(), by(), c1() (+56 more)

### Community 5 - "update"
Cohesion: 0.04
Nodes (87): activeForPoint(), addChunk(), addInner(), adjust(), al(), annotation(), boundChange(), bS() (+79 more)

### Community 6 - "field"
Cohesion: 0.07
Nodes (41): active(), apply(), b0(), between(), blur(), checkAsyncSchedule(), compute(), Ed() (+33 more)

### Community 7 - "draw"
Cohesion: 0.04
Nodes (102): _freeze(), getAllExtensions(), acquireContext(), adjustHitBoxes(), bh(), bu(), buildTicks(), calculateLabelRotation() (+94 more)

### Community 8 - "markdown-editor.js"
Cohesion: 0.04
Nodes (140): xe(), Ac(), Ae(), af(), al(), An(), ao(), ar() (+132 more)

### Community 9 - "_update"
Cohesion: 0.04
Nodes (91): $a(), addBox(), addElements(), addEventListener(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit() (+83 more)

### Community 10 - "prop"
Cohesion: 0.05
Nodes (79): addChild(), AQ(), atLastNode(), au(), child(), childAfter(), childBefore(), continue() (+71 more)

### Community 11 - "components/select.js"
Cohesion: 0.09
Nodes (27): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), Bt(), createBadgeElement(), createRemoveButton(), D(), en() (+19 more)

### Community 12 - "selectOption"
Cohesion: 0.15
Nodes (33): addSingleSelectionDisplay(), closeDropdown(), constructor(), createOptionElement(), deferPositionDropdown(), destroy(), filterOptions(), focusNextOption() (+25 more)

### Community 13 - "create"
Cohesion: 0.05
Nodes (63): Cl(), clone(), create(), Ct(), dc(), Dl(), dtFormatter(), Ec() (+55 more)

### Community 14 - "fromObject"
Cohesion: 0.04
Nodes (98): Oe(), ac(), ae(), after(), ag(), Al(), Am(), before() (+90 more)

### Community 15 - "advance"
Cohesion: 0.05
Nodes (74): acceptToken(), addActions(), advance(), advanceFully(), advanceStack(), allActions(), allows(), break() (+66 more)

### Community 16 - "ApifyAccount"
Cohesion: 0.10
Nodes (10): ApifyAccount, self, GoogleMapsScrapeJob, User, ApifyScraperService, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Relations\BelongsTo, Illuminate\Database\Eloquent\Relations\HasMany (+2 more)

### Community 17 - "constructor"
Cohesion: 0.04
Nodes (69): addNode(), applyInitialSize(), Bo(), bt(), constructor(), Cr(), createCommandManager(), createContainer() (+61 more)

### Community 18 - "_update"
Cohesion: 0.05
Nodes (65): acquireContext(), addElements(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion() (+57 more)

### Community 19 - "resolve"
Cohesion: 0.06
Nodes (137): Ac(), addKeyboardShortcuts(), after(), ag(), al(), allowedMarks(), allowsMarks(), before() (+129 more)

### Community 20 - "o"
Cohesion: 0.03
Nodes (97): Mo(), Ym(), o(), _0(), addGlobalAttributes(), addInputRules(), addMark(), addPasteRules() (+89 more)

### Community 21 - "e"
Cohesion: 0.06
Nodes (82): add(), addCommands(), addNodeView(), addProseMirrorPlugins(), AS(), Bf(), Bm(), cellsInRect() (+74 more)

### Community 22 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.10
Nodes (9): EditPost, ListPosts, PostDetail, PostImage, DatabaseSeeder, LandmarkSeeder, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes (+1 more)

### Community 23 - "slice"
Cohesion: 0.03
Nodes (97): activateHover(), addGaps(), addLeafElement(), addNode(), ATXHeading(), balance(), balanced(), baseIndent() (+89 more)

### Community 24 - "A"
Cohesion: 0.06
Nodes (67): jo(), A(), Ae(), As(), bl(), buildTicks(), calculateLabelRotation(), _calculatePadding() (+59 more)

### Community 25 - "y"
Cohesion: 0.17
Nodes (53): at(), Be(), ca(), Cr(), Ct(), de(), dr(), dt() (+45 more)

### Community 26 - "dx"
Cohesion: 0.09
Nodes (44): Hr(), Li(), ue(), wP(), Ei(), Aa(), ai(), Bi() (+36 more)

### Community 27 - "jy"
Cohesion: 0.04
Nodes (79): Ad(), au(), ay(), Bd(), cl(), connectSelection(), cu(), cy() (+71 more)

### Community 28 - "notifications.js"
Cohesion: 0.06
Nodes (31): actions(), button(), c(), close(), configureAnimations(), configureTransitions(), constructor(), danger() (+23 more)

### Community 29 - "support.js"
Cohesion: 0.07
Nodes (38): acquireScrollLock(), ai(), close(), closeQuietly(), commit(), destroy(), distribute(), es() (+30 more)

### Community 30 - "te"
Cohesion: 0.05
Nodes (6): Bn(), ji(), Ri(), te(), Vi(), Xc()

### Community 31 - "next"
Cohesion: 0.07
Nodes (38): addActive(), addChanges(), addSelection(), Ar(), as(), be(), chunkEnd(), compose() (+30 more)

### Community 32 - "updateElements"
Cohesion: 0.04
Nodes (73): afterAutoSkip(), Ao(), aspectRatio(), buildLookupTable(), _calculateBarIndexPixels(), _calculateBarValuePixels(), calculateCircumference(), _circumference() (+65 more)

### Community 33 - "slider.js"
Cohesion: 0.09
Nodes (37): Ae(), ar(), Be(), Bt(), De(), _e(), Ee(), er() (+29 more)

### Community 34 - "_each"
Cohesion: 0.10
Nodes (25): addControllers(), addPlugins(), addScales(), br(), cancel(), _createDescriptors(), _descriptors(), _each() (+17 more)

### Community 35 - "get"
Cohesion: 0.03
Nodes (109): addBlock(), addBlockWidget(), addBreak(), addComposition(), addDelimiter(), addInlineWidget(), addLine(), addLineDeco() (+101 more)

### Community 36 - "match"
Cohesion: 0.10
Nodes (29): bd(), Bh(), clearDelayedAndroidKey(), d0(), De(), delayAndroidKey(), Dg(), E$() (+21 more)

### Community 37 - "parse"
Cohesion: 0.07
Nodes (44): gX(), af(), apply(), at(), Ba(), Bf(), determineDataLimits(), Fa() (+36 more)

### Community 38 - "f"
Cohesion: 0.13
Nodes (52): In(), mu(), W(), yS(), d(), g(), p(), b() (+44 more)

### Community 39 - "l"
Cohesion: 0.11
Nodes (32): cP(), hd(), map(), scrollIntoView(), TS(), wa(), Ba(), lf() (+24 more)

### Community 40 - "marks"
Cohesion: 0.07
Nodes (52): addAll(), addDOM(), addElement(), addElementByRule(), addStoredMark(), addTextNode(), addToSet(), allowsMarkType() (+44 more)

### Community 41 - "Category"
Cohesion: 0.07
Nodes (14): AddressParser, Controller, SitemapController, Home, LandmarkShow, PostList, PostShow, Search (+6 more)

### Community 42 - "parse"
Cohesion: 0.06
Nodes (46): Bt(), cl(), Cn(), determineDataLimits(), diff(), el(), En(), endOf() (+38 more)

### Community 43 - "t"
Cohesion: 0.06
Nodes (70): B(), bu(), coordsForChar(), dynamicSlot(), fromJSON(), _h(), Hs(), me() (+62 more)

### Community 44 - "ls"
Cohesion: 0.24
Nodes (11): cs(), Io(), kn(), ls(), os(), si(), us(), ws() (+3 more)

### Community 45 - "fn"
Cohesion: 0.14
Nodes (46): ar(), ba(), c(), cr(), d(), da(), di(), ei() (+38 more)

### Community 46 - "getDatasetMeta"
Cohesion: 0.09
Nodes (31): afterDatasetsUpdate(), An(), datasetElementScopeKeys(), ef(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth() (+23 more)

### Community 47 - "E"
Cohesion: 0.05
Nodes (67): ch(), Yg(), aa(), add(), B(), bo(), br(), bs() (+59 more)

### Community 48 - "inRange"
Cohesion: 0.05
Nodes (59): ad(), ah(), applyStack(), average(), beforeDatasetsDraw(), beforeLayout(), bi(), dataset() (+51 more)

### Community 49 - "ar"
Cohesion: 0.11
Nodes (22): ar(), beforeDatasetDraw(), dn(), getMinDaysInFirstWeek(), getMinimumDaysInFirstWeek(), getStartOfWeek(), getWeekendDays(), getWeekendWeekdays() (+14 more)

### Community 50 - "Oc"
Cohesion: 0.11
Nodes (27): ao(), append(), Cc(), cn(), co(), err(), finish(), fromArray() (+19 more)

### Community 51 - "Filament\Schemas\Schema"
Cohesion: 0.10
Nodes (11): ApifyAccountForm, CategoryForm, CityForm, LandmarkForm, PostForm, ProvinceForm, Province, Filament\Schemas\Schema (+3 more)

### Community 52 - "ERD.md"
Cohesion: 0.04
Nodes (47): 10. Relationship Summary, 11. Standard Columns, 12. Naming Convention, 13. Future Expansion, 1. Purpose, 2. Design Principles, 3. Conceptual ERD, 4. Master Tables (+39 more)

### Community 53 - "Cn"
Cohesion: 0.14
Nodes (37): _a(), Bi(), br(), Bt(), ca(), Cn(), ct(), Dn() (+29 more)

### Community 54 - "fn"
Cohesion: 0.15
Nodes (27): An(), Ce(), de(), di(), dn(), Dt(), ei(), fn() (+19 more)

### Community 55 - "Si"
Cohesion: 0.17
Nodes (27): At(), bi(), bn(), ci(), cn(), ct(), fi(), gn() (+19 more)

### Community 56 - "MODULES.md"
Cohesion: 0.04
Nodes (44): 10. System Modules, 11. Module Ownership, 12. Folder Structure, 13. Communication Rules, 14. Event Driven, 15. Future Modules, 16. Development Rules, 17. Module Lifecycle (+36 more)

### Community 57 - "dl"
Cohesion: 0.07
Nodes (38): Nn(), aa(), active(), addEventListener(), ai(), _animateOptions(), bindEvents(), bindResponsiveEvents() (+30 more)

### Community 58 - "public/app.js"
Cohesion: 0.14
Nodes (44): api(), appendLog(), closeModal(), connectSSE(), esc(), formatBytes(), formatDate(), formatNum() (+36 more)

### Community 59 - "echo.js"
Cohesion: 0.08
Nodes (19): a(), ar(), at(), cr(), d(), f(), H(), ii() (+11 more)

### Community 60 - "Ye"
Cohesion: 0.08
Nodes (49): Rd(), $a(), at(), bk(), bp(), Dk(), dp(), Ek() (+41 more)

### Community 61 - "00-VISION.md"
Cohesion: 0.05
Nodes (43): 1. Mempermudah masyarakat menemukan informasi lokal, 2. Menjadi sumber informasi yang terpercaya, 3. Menghubungkan masyarakat dengan bisnis lokal, 4. Mengembangkan ekosistem informasi lokal, About DISEKITAR, Administrator, AI Ready, API Ready (+35 more)

### Community 62 - "facet"
Cohesion: 0.04
Nodes (70): accept(), addElement(), Ah(), applyTransaction(), asSingle(), changeByRange(), changes(), create() (+62 more)

### Community 63 - "getDatasetMeta"
Cohesion: 0.12
Nodes (23): afterDatasetsUpdate(), buildOrUpdateControllers(), _destroyDatasetMeta(), getController(), getDatasetMeta(), getElement(), _handleEvent(), hide() (+15 more)

### Community 64 - "find"
Cohesion: 0.07
Nodes (40): baseDirAt(), bidiIn(), bidiSpans(), bidiSpansAt(), checkHover(), computeBlockGapDeco(), coordsAtPos(), _d() (+32 more)

### Community 65 - "DuskTestCase.php"
Cohesion: 0.20
Nodes (7): Facebook\WebDriver\Remote\RemoteWebDriver, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Collection, Laravel\Dusk\TestCase, RemoteWebDriver, DuskTestCase, TestCase

### Community 66 - "05-CODING-STANDARD.md"
Cohesion: 0.05
Nodes (42): Action Rules, AI Coding Rules, API Response, Blade Standard, Class, Comments, Configuration, Constant (+34 more)

### Community 67 - "V"
Cohesion: 0.16
Nodes (21): ae(), B(), de(), dt(), Ee(), Ge(), Gt(), he() (+13 more)

### Community 68 - "constructor"
Cohesion: 0.03
Nodes (90): add(), addEventListener(), addInfoPane(), addToSet(), addWindowListeners(), applyChanges(), applyEdits(), bf() (+82 more)

### Community 69 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.06
Nodes (20): CreateApifyAccount, EditApifyAccount, ListApifyAccounts, CreateCategory, EditCategory, ListCategories, CreateCity, EditCity (+12 more)

### Community 70 - "home.blade.php"
Cohesion: 0.50
Nodes (3): partials.category-tabs, partials.article-feed, partials.editor-picks

### Community 71 - "ei"
Cohesion: 0.10
Nodes (22): ar(), describe(), ei(), er(), gn(), Gt(), ir(), ji() (+14 more)

### Community 72 - "constructor"
Cohesion: 0.06
Nodes (49): Ot(), alpha(), apply(), be(), bo(), chartOptionScopes(), co(), constructor() (+41 more)

### Community 73 - "file-upload.js"
Cohesion: 0.05
Nodes (53): baseTheme(), hc(), Bp(), c(), ca(), clickPercent(), cm(), constructor() (+45 more)

### Community 74 - "tables.js"
Cohesion: 0.09
Nodes (63): A(), ae(), areRecordsPartiallySelected(), areRecordsSelected(), areRecordsToggleable(), B(), be(), C() (+55 more)

### Community 75 - "filament/app.js"
Cohesion: 0.14
Nodes (8): B(), close(), G(), init(), P(), setUpResizeObserver(), x(), Y()

### Community 76 - "devDependencies"
Cohesion: 0.10
Nodes (19): axios, concurrently, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss (+11 more)

### Community 77 - "08-UI-UX.md"
Cohesion: 0.05
Nodes (42): Accessibility, Admin UX, Administrator, AI Coding Guidelines, Business Dashboard, Business Owner, Buttons, Card Design (+34 more)

### Community 78 - "t"
Cohesion: 0.18
Nodes (12): Ce(), De(), di(), e(), Ht(), Ie(), Re(), t() (+4 more)

### Community 79 - "N"
Cohesion: 0.10
Nodes (21): afterAutoSkip(), Ao(), buildLookupTable(), ci(), dn(), _exec(), Fs(), getDecimalForValue() (+13 more)

### Community 80 - "S"
Cohesion: 0.07
Nodes (44): add(), _cachedScopes(), cr(), createResolver(), da(), fn(), fr(), ft() (+36 more)

### Community 81 - "closeDropdown"
Cohesion: 0.23
Nodes (17): applyDisabledState(), closeDropdown(), constructor(), destroy(), disable(), enable(), focusNextOption(), focusPreviousOption() (+9 more)

### Community 82 - ".slice"
Cohesion: 0.05
Nodes (62): accepts(), addInner(), addMaps(), addStep(), addTransform(), appendMap(), appendMapping(), appendMappingInverted() (+54 more)

### Community 83 - "composer.json"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 84 - "dependencies"
Cohesion: 0.05
Nodes (40): chalk, commander, dotenv, exceljs, googleapis, inquirer, ora, puppeteer (+32 more)

### Community 85 - "require-dev"
Cohesion: 0.18
Nodes (11): require-dev, fakerphp/faker, filament/upgrade, laravel/dusk, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+3 more)

### Community 86 - "scripts"
Cohesion: 0.18
Nodes (11): scripts, dev, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite, @php artisan config:clear --ansi (+3 more)

### Community 87 - "HomePage"
Cohesion: 0.20
Nodes (4): Laravel\Dusk\Browser, Laravel\Dusk\Page, HomePage, Page

### Community 88 - "04-API.md"
Cohesion: 0.05
Nodes (40): AI Coding Guidelines, API Documentation, API Goals, API Philosophy, Authentication, Authentication, Base URL, Bookmark (+32 more)

### Community 89 - "require"
Cohesion: 0.20
Nodes (10): require, dedoc/scramble, filament/filament, laravel/framework, laravel/sanctum, laravel/tinker, maatwebsite/excel, openai-php/laravel (+2 more)

### Community 90 - "server/index.js"
Cohesion: 0.11
Nodes (39): clearPhotoQueue(), deleteAccount(), deleteTask(), enqueuePlacesForPhotoEnrichment(), getAllPlaces(), getCategories(), getPendingPhotoQueue(), getPhotoQueueList() (+31 more)

### Community 91 - "PERMISSION_MODEL.md"
Cohesion: 0.05
Nodes (36): 10. Middleware, 11. Laravel Policy, 12. UI Permission, 13. API Permission, 14. Audit Trail, 15. Future Multi-Tenant, 16. Database Structure, 17. Best Practices (+28 more)

### Community 92 - "renderOptions"
Cohesion: 0.37
Nodes (13): createOptionElement(), deferPositionDropdown(), filterOptions(), handleSearch(), hideLoadingState(), openDropdown(), populateLabelRepositoryFromOptions(), positionDropdown() (+5 more)

### Community 93 - "Illuminate\Console\Command"
Cohesion: 0.27
Nodes (5): GenerateCitySlug, GenerateProvinceSlug, ImportGoogleMapsData, Command, Illuminate\Console\Command

### Community 94 - "Xt"
Cohesion: 0.14
Nodes (38): ae(), At(), bi(), bn(), ci(), cn(), ct(), de() (+30 more)

### Community 95 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 96 - "r"
Cohesion: 0.21
Nodes (12): Be(), ei(), Fe(), He(), i(), le(), Mt(), ni() (+4 more)

### Community 97 - "addSingleBadge"
Cohesion: 0.33
Nodes (6): addBadgesForSelectedOptions(), addSingleBadge(), createBadgeElement(), createRemoveButton(), getLabelForSingleSelection(), getSelectedOptionLabel()

### Community 98 - "Post"
Cohesion: 0.08
Nodes (4): Post, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 99 - "actions/actions.js"
Cohesion: 0.44
Nodes (8): closeModal(), generateModalId(), getActionNestingIndexFromModalId(), init(), openModal(), rememberPreviouslyFocusedElement(), restorePreviouslyFocusedElement(), syncActionModals()

### Community 100 - "PROMPT_REVIEW.md"
Cohesion: 0.05
Nodes (36): AI Must NOT, API, Architecture, Coding Standard, Critical, Database, DISEKITAR AI Code Review Prompt, Documentation (+28 more)

### Community 102 - "RahasiaPanelProvider.php"
Cohesion: 0.60
Nodes (3): RahasiaPanelProvider, Filament\Panel, Filament\PanelProvider

### Community 103 - "post-show.blade.php"
Cohesion: 0.50
Nodes (3): closeLightbox, openLightbox(@js($img->image_url)), openLightbox(@js($mainImg))

### Community 104 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 105 - "12-TODO.md"
Cohesion: 0.06
Nodes (34): Admin API, AI Coding Guidelines, Authentication, Authentication API, Blockers, Category, Completed Tasks, Core Architecture (+26 more)

### Community 106 - "06-SEO.md"
Cohesion: 0.06
Nodes (35): 1. Halaman Berbasis Landmark & Titik Transit (Transit / Proximity SEO), 2. Internal Linking Lintas Kategori Berbasis Lokasi (Cross-Category Proximity Linking), AI Coding Guidelines, AI Search Readiness, Breadcrumb, Canonical URL, Content Quality, Definition of Done (SEO) (+27 more)

### Community 108 - "post-autoload-dump"
Cohesion: 0.50
Nodes (4): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan filament:upgrade, @php artisan package:discover --ansi

### Community 109 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 110 - "scrapePlacePhotos"
Cohesion: 0.43
Nodes (6): apify-client, apify-client, getNextRotatedAccount(), getDuckDuckGoVQD(), isValidImageUrl(), scrapePlacePhotos()

### Community 126 - "DOMAIN_MODEL.md"
Cohesion: 0.06
Nodes (31): 10. Audit, 11. Future Modules, 12. Naming Convention, 13. Out of Scope, 1. Purpose, 2. Domain Overview, 3.1 Single Source of Truth, 3.2 Reusable Entity (+23 more)

### Community 128 - "_notify"
Cohesion: 0.15
Nodes (17): active(), _animateOptions(), cancel(), _createAnimations(), _createDescriptors(), _d(), _descriptors(), _notify() (+9 more)

### Community 129 - "RunGoogleMapsScrapeJob.php"
Cohesion: 0.11
Nodes (14): ProcessBatchAiQueueCommand, ListAiGenerationJobs, PostsTable, GeneratePostContentJob, ProcessBatchAiQueueJob, RunGoogleMapsScrapeJob, AiGenerationJob, PostAiContentService (+6 more)

### Community 133 - "SEO_STRATEGY.md"
Cohesion: 0.06
Nodes (30): 10. Internal Linking, 11. Sitemap, 12. Robots, 13. Canonical, 14. Open Graph, 15. Twitter Card, 16. Image SEO, 17. Performance (+22 more)

### Community 165 - "db/index.js"
Cohesion: 0.12
Nodes (29): ACCOUNT_COLUMNS, ACCOUNT_STATUSES, accountFromId(), createTask(), DB_DIR, DB_PATH, FINAL_STATUSES, getAccountByEmail() (+21 more)

### Community 166 - "03-DATABASE.md"
Cohesion: 0.06
Nodes (30): AI Coding Guidelines, Audit, Bookmark, Categories, Core Tables, Database Goals, Database Philosophy, Detail Modules (+22 more)

### Community 167 - "13-DOMAIN.md"
Cohesion: 0.06
Nodes (30): AI Coding Guidelines, Business Domains, CMS, Core Domains, Definition of Done, DISEKITAR Domain Guide, Domain Checklist, Domain Communication (+22 more)

### Community 168 - "02-ARCHITECTURE.md"
Cohesion: 0.07
Nodes (29): Action, AI Coding Guidelines, API First, Architecture Goals, Architecture Principles, Cache Strategy, Controller, Database Layer (+21 more)

### Community 169 - "09-DEPLOYMENT.md"
Cohesion: 0.07
Nodes (29): AI Coding Guidelines, Application Server, Backup Strategy, Cache, CI/CD, Configuration, Database Migration, Definition of Done (+21 more)

### Community 170 - "11-AGENTS.md"
Cohesion: 0.07
Nodes (28): AI Agent Oath, API Rules, Architecture Rules, Before Writing Code, Code Generation Rules, Core Principles, Database Rules, Decision Priority (+20 more)

### Community 171 - "accountCreator.js"
Cohesion: 0.14
Nodes (24): createApifyAccount(), DOMAINS, findSystemBrowserExecutable(), generateRandomEmail(), generateRandomPassword(), sleep(), updateEnvFile(), createApifyAccountWithGithub() (+16 more)

### Community 172 - "10-CONTRIBUTING.md"
Cohesion: 0.07
Nodes (27): AI Coding Agent Guidelines, API Changes, Backward Compatibility, Before You Start, Branch Strategy, Bug Report, Code Review, Commit Standard (+19 more)

### Community 173 - "API_SPEC.md"
Cohesion: 0.07
Nodes (26): 10. Opening Hour API, 11. Facility API, 12. Tag API, 13. Review API, 14. Event API, 15. Job API, 16. Article API, 17. Master API (+18 more)

### Community 174 - "sliceDoc"
Cohesion: 0.08
Nodes (33): a$(), aO(), b1(), charCategorizer(), Fc(), getCursor(), getDeco(), gT() (+25 more)

### Community 175 - "exporters/index.js"
Cohesion: 0.14
Nodes (21): RFC-4180, DEFAULT_COLUMNS, escapeCsvField(), exportToCsv(), formatField(), COLUMNS, exportToExcel(), formatField() (+13 more)

### Community 176 - "07-FEATURES.md"
Cohesion: 0.08
Nodes (25): AI Coding Guidelines, API Features, Definition of Done, DISEKITAR Features Specification, Event Platform, Feature Dependencies, Feature Principles, Feature Status (+17 more)

### Community 177 - "PROMPT_REFACTOR.md"
Cohesion: 0.08
Nodes (24): AI Must NOT, Benefits, DISEKITAR AI Refactoring Prompt, Documentation Updated, Files Modified, Purpose, Refactoring Performed, Refactoring Principles (+16 more)

### Community 179 - "n"
Cohesion: 0.11
Nodes (28): A(), An(), b(), Bt(), D(), F(), gt(), ht() (+20 more)

### Community 180 - "color-picker.js"
Cohesion: 0.14
Nodes (4): constructor(), style(), update(), [x]()

### Community 181 - "PROMPT_DEVELOPMENT.md"
Cohesion: 0.11
Nodes (18): AI Must NOT, Completion Checklist, Development Workflow, DISEKITAR AI Development Prompt, Escalation Rules, Purpose, Step 10 — Update Task, Step 1 — Identify Task (+10 more)

### Community 182 - "URL_STRUCTURE.md"
Cohesion: 0.11
Nodes (16): 10. Sitemap Structure, 11. Robots, 12. URL Priority, 13. Reserved Routes, 14. Future URL, 15. URL Naming Convention, 16. SEO Notes, 1. Purpose (+8 more)

### Community 183 - "PROMPT_ONBOARDING.md"
Cohesion: 0.11
Nodes (17): AI Rules, Communication Rules, DISEKITAR AI Onboarding Prompt, Mandatory Documents, Purpose, Step 10 — Update Task, Step 1 — Read Documentation, Step 2 — Understand the Project (+9 more)

### Community 184 - "Filament\Tables\Table"
Cohesion: 0.08
Nodes (20): AiGenerationJobResource, AiGenerationJobsTable, ApifyAccountResource, ApifyAccountsTable, CategoryResource, CategoriesTable, CityResource, CitiesTable (+12 more)

### Community 185 - "Analisis Project DISEKITAR"
Cohesion: 0.12
Nodes (16): 10. Insight dari Graphify, 11. Ringkasan Eksekutif, 1. Identitas & Visi Produk, 2. Tech Stack, 3. Arsitektur, 4. Domain Model & Database, 5. Routing & Controller, 6. Model & Relasi Eloquent (+8 more)

### Community 186 - "ROLE_MATRIX.md"
Cohesion: 0.12
Nodes (16): AI May Suggest, AI Must NOT, AI Responsibilities, Communication Rules, Decision Authority, DISEKITAR Task Role Matrix, Documentation Rules, Escalation Rules (+8 more)

### Community 187 - "En"
Cohesion: 0.11
Nodes (24): Ra(), Ao(), apply(), as(), At(), En(), fs(), go() (+16 more)

### Community 188 - "Nn"
Cohesion: 0.29
Nodes (7): aa(), gr(), mr(), Nn(), sr(), ue(), Yt()

### Community 189 - "columns/select.js"
Cohesion: 0.10
Nodes (26): applyDisabledState(), Ce(), disable(), ei(), en(), enable(), fn(), Ft() (+18 more)

### Community 190 - "Google Maps Data Extractor v2 (Node.js & Apify Edition)"
Cohesion: 0.12
Nodes (16): 1. Masuk ke direktori `v2`, 1. Mode Interaktif (Paling Mudah), 2. Command Line Langsung (Scraper), 2. Install Dependensi, 3. Konfigurasi `.env`, 3. Otomasi Buat Akun Apify (Account Creator), 📖 Cara Penggunaan, Contoh Dasar: (+8 more)

### Community 191 - "tasks/README.md"
Cohesion: 0.12
Nodes (15): AI Coding Agent Rules, BACKLOG.md, Benefits, Definition of Done, DISEKITAR Task Management Guide, Folder Structure, History, Index Files (+7 more)

### Community 192 - "01-ROADMAP.md"
Cohesion: 0.13
Nodes (14): Development Principles, Development Status Legend, DISEKITAR Development Roadmap, Future Integrations, Notes for AI Coding Agent, Overview, Phase 10 — Mobile Application, Phase 11 — AI Platform (+6 more)

### Community 193 - "Cloud-Native Ready Philosophy"
Cohesion: 0.13
Nodes (15): AI Coding Guidelines, Cache Strategy, CDN Ready, Cloud-Native Ready Philosophy, Database Strategy, Design Principles, File Storage Strategy, Horizontal Scaling (+7 more)

### Community 194 - "pagination.blade.php"
Cohesion: 0.50
Nodes (3): gotoPage({{ $page }}, , nextPage(, previousPage(

### Community 195 - "nr"
Cohesion: 0.50
Nodes (4): Dt(), ir(), nr(), rt()

### Community 196 - "FE‑001 — Public Frontend Foundation"
Cohesion: 0.15
Nodes (12): Controller, Docs, FE‑001 — Public Frontend Foundation, Files Changed, Goal, History, Livewire, Notes (+4 more)

### Community 197 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 199 - "constructor"
Cohesion: 0.05
Nodes (59): alpha(), Bc(), bg(), chartOptionScopes(), co(), constructor(), darken(), desaturate() (+51 more)

### Community 200 - "Phase 1 — Core CMS"
Cohesion: 0.17
Nodes (12): Category Module, Features, Features, Features, Features, Features, Gallery Module, Location Module (+4 more)

### Community 201 - "P"
Cohesion: 0.50
Nodes (4): E(), b(), g(), P()

### Community 202 - "Core Content"
Cohesion: 0.18
Nodes (11): Category, Core Content, Features, Features, Features, Features, Features, Gallery (+3 more)

### Community 203 - "src/index.js"
Cohesion: 0.27
Nodes (7): getAccounts(), getStats(), program, runInteractiveMenu(), DEFAULT_ACTOR, colors, logger

### Community 204 - "Discovery Features"
Cohesion: 0.22
Nodes (9): Discovery Features, Features, Features, Features, Features, Interactive Map, Nearby, Recommendation (+1 more)

### Community 206 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 207 - "Production Readiness Checklist"
Cohesion: 0.25
Nodes (8): Application, Database, Infrastructure, Monitoring, Performance, Production Readiness Checklist, Security, SEO

### Community 209 - "Phase 0 — Foundation"
Cohesion: 0.29
Nodes (7): Authentication, Authorization, Features, Features, Phase 0 — Foundation, Project Setup, Tasks

### Community 210 - "Administration"
Cohesion: 0.29
Nodes (7): Administration, Audit Log, Dashboard, Features, Features, Features, Scheduler

### Community 211 - "Business Platform"
Cohesion: 0.29
Nodes (7): Advertisement, Business Listing, Business Platform, Features, Features, Features, Premium Listing

### Community 212 - "AI Features"
Cohesion: 0.29
Nodes (7): AI Content, AI Features, AI Recommendation, AI Search, Features, Features, Features

### Community 213 - "Community Features"
Cohesion: 0.29
Nodes (7): Bookmark, Community Features, Features, Features, Features, Review, User Profile

### Community 214 - "Core Platform"
Cohesion: 0.29
Nodes (7): Core Platform, Features, Features, Features, Role & Permission, Settings, User Management

### Community 215 - "FE‑003 — Smoke Path Frontend"
Cohesion: 0.29
Nodes (6): Bug ditemukan & fixed, FE‑003 — Smoke Path Frontend, Goal, History, Result (HTTP), Sample data

### Community 216 - "FE‑002 — Vite Asset Build"
Cohesion: 0.33
Nodes (5): FE‑002 — Vite Asset Build, Goal, History, Result, Scope

### Community 217 - "Phase 2 — Public Website"
Cohesion: 0.40
Nodes (5): Category Page, Detail Page, Homepage, Phase 2 — Public Website, Search

### Community 218 - "DISEKITAR — Task Board"
Cohesion: 0.25
Nodes (7): 1. Fondasi & CMS (Filament Admin), 2. AI Content Generation Engine, 3. Frontend & Desain Editorial (Livewire & Tailwind), DISEKITAR — Task Board, Done (21 - 23 Agustus 2026), Notes, Now / Next

### Community 219 - "Phase 5 — Business Platform"
Cohesion: 0.50
Nodes (4): Advertisement, Business Listing, Phase 5 — Business Platform, Premium Listing

### Community 220 - "Phase 4 — Community"
Cohesion: 0.50
Nodes (4): Bookmark, Phase 4 — Community, Review, User Profile

### Community 221 - "Phase 3 — Geo Intelligence"
Cohesion: 0.50
Nodes (4): Geolocation, Interactive Map, Nearby, Phase 3 — Geo Intelligence

### Community 222 - "Technical Improvements"
Cohesion: 0.50
Nodes (4): Monitoring, Performance, Security, Technical Improvements

### Community 223 - "Environment"
Cohesion: 0.50
Nodes (4): Development, Environment, Production, Staging

### Community 224 - "opencode.json"
Cohesion: 0.50
Nodes (3): plugin, $schema, .opencode/plugins/graphify.js

### Community 243 - "Milestone: Editorial Platform & AI Automation Engine (21 - 23 Agustus 2026)"
Cohesion: 0.33
Nodes (5): 1. Google Maps Extractor & Multi-Format Importer (21 - 22 Agustus), 2. Batch AI Parallel Content Generation (22 - 23 Agustus), 3. Editorial UI/UX & Dark Mode (22 - 23 Agustus), DISEKITAR — Milestone & Progress Log, Milestone: Editorial Platform & AI Automation Engine (21 - 23 Agustus 2026)

### Community 256 - "yP"
Cohesion: 0.08
Nodes (31): attrs(), bi(), build(), cc(), cO(), cS(), cX(), Ex() (+23 more)

## Knowledge Gaps
- **1022 isolated node(s):** `$schema`, `.opencode/plugins/graphify.js`, `$schema`, `name`, `type` (+1017 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **17 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `o()` connect `o` to `yP`, `code-editor.js`, `rich-editor.js`, `_notify`, `components/chart.js`, `update`, `field`, `draw`, `stat/chart.js`, `_update`, `prop`, `create`, `fromObject`, `advance`, `constructor`, `_update`, `resolve`, `e`, `slice`, `A`, `jy`, `notifications.js`, `next`, `updateElements`, `_each`, `get`, `parse`, `f`, `l`, `t`, `fn`, `E`, `inRange`, `ar`, `Oc`, `n`, `Cn`, `fn`, `Si`, `dl`, `En`, `columns/select.js`, `facet`, `nr`, `constructor`, `constructor`, `constructor`, `file-upload.js`, `ei`, `t`, `N`, `S`, `.slice`, `Xt`, `r`?**
  _High betweenness centrality (0.090) - this node is a cross-community bridge._
- **Why does `l()` connect `l` to `_notify`, `components/chart.js`, `rich-editor.js`, `stat/chart.js`, `.forEach`, `update`, `draw`, `markdown-editor.js`, `_update`, `prop`, `create`, `fromObject`, `advance`, `constructor`, `resolve`, `o`, `slice`, `A`, `y`, `dx`, `jy`, `next`, `updateElements`, `parse`, `f`, `marks`, `parse`, `t`, `fn`, `inRange`, `ar`, `Oc`, `n`, `fn`, `dl`, `echo.js`, `facet`, `find`, `constructor`, `constructor`, `ei`, `file-upload.js`, `tables.js`, `.slice`, `Xt`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **Why does `scrapeGooglePlaces()` connect `db/index.js` to `src/index.js`, `scrapePlacePhotos`, `exporters/index.js`, `Oc`, `server/index.js`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 186 inferred relationships involving `o()` (e.g. with `Be()` and `i()`) actually correct?**
  _`o()` has 186 INFERRED edges - model-reasoned connections that need verification._
- **Are the 153 inferred relationships involving `t()` (e.g. with `add()` and `addCompletions()`) actually correct?**
  _`t()` has 153 INFERRED edges - model-reasoned connections that need verification._
- **Are the 144 inferred relationships involving `i()` (e.g. with `add()` and `addElement()`) actually correct?**
  _`i()` has 144 INFERRED edges - model-reasoned connections that need verification._
- **Are the 17 inferred relationships involving `constructor()` (e.g. with `gQ()` and `o()`) actually correct?**
  _`constructor()` has 17 INFERRED edges - model-reasoned connections that need verification._