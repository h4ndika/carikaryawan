# Graph Report - disekiar2  (2026-08-23)

## Corpus Check
- 231 files · ~154,426 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 6764 nodes · 19039 edges · 249 communities (232 shown, 17 thin omitted)
- Extraction: 88% EXTRACTED · 12% INFERRED · 0% AMBIGUOUS · INFERRED: 2272 edges (avg confidence: 0.71)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d26797b9`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- code-editor.js
- components/chart.js
- rich-editor.js
- stat/chart.js
- resolve
- constructor
- facet
- E
- markdown-editor.js
- _update
- prop
- components/select.js
- columns/select.js
- create
- fromObject
- advance
- ApifyAccount.php
- getDatasetMeta
- _update
- .slice
- o
- toString
- create
- reduce
- getContext
- y
- n
- start
- notifications.js
- support.js
- te
- S
- updateElements
- slider.js
- pl
- get
- sliceDoc
- initOffsets
- h
- l
- eq
- City
- _generate
- t
- A
- u
- lineAt
- get
- inRange
- constructor
- e
- Filament\Schemas\Schema
- ERD.md
- Cn
- buildOrUpdateControllers
- parse
- MODULES.md
- dl
- public/app.js
- echo.js
- .get
- 00-VISION.md
- eq
- slice
- find
- static
- 05-CODING-STANDARD.md
- xt
- _notify
- Filament\Resources\Pages\ListRecords
- home.blade.php
- fn
- constructor
- file-upload.js
- tables.js
- filament/app.js
- devDependencies
- 08-UI-UX.md
- t
- addEventListener
- add
- Illuminate\Database\Eloquent\Model
- selectRecords
- composer.json
- dependencies
- require-dev
- scripts
- HomePage
- 04-API.md
- require
- server/index.js
- PERMISSION_MODEL.md
- Si
- Illuminate\Console\Command
- Xt
- config
- r
- ls
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
- extra
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
- g$
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
- Mt
- fn
- fn
- Google Maps Data Extractor v2 (Node.js & Apify Edition)
- tasks/README.md
- 01-ROADMAP.md
- Cloud-Native Ready Philosophy
- pagination.blade.php
- nr
- FE‑001 — Public Frontend Foundation
- selectOption
- draw
- Phase 1 — Core CMS
- P
- Core Content
- src/index.js
- Discovery Features
- README.md
- Production Readiness Checklist
- scrapePlacePhotos
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
- ii

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

## Communities (249 total, 17 thin omitted)

### Community 0 - "code-editor.js"
Cohesion: 0.01
Nodes (94): a$(), Ac(), addCompletion(), addCompletions(), addNamespace(), addNamespaceObject(), after(), b0() (+86 more)

### Community 1 - "components/chart.js"
Cohesion: 0.01
Nodes (107): $a(), addControllers(), addPlugins(), addScales(), bd(), beforeDraw(), buildFormatParser(), contains() (+99 more)

### Community 2 - "rich-editor.js"
Cohesion: 0.01
Nodes (191): Rd(), readPos(), $a(), aa(), addExtensions(), addHackNode(), addNode(), addNodeMark() (+183 more)

### Community 3 - "stat/chart.js"
Cohesion: 0.03
Nodes (83): themeClasses(), addControllers(), addPlugins(), addScales(), an(), applyStack(), ba(), beforeDatasetsDraw() (+75 more)

### Community 4 - "resolve"
Cohesion: 0.05
Nodes (162): Ac(), addCommands(), addKeyboardShortcuts(), after(), ag(), al(), allowedMarks(), allowsMarks() (+154 more)

### Community 5 - "constructor"
Cohesion: 0.03
Nodes (132): add(), addChunk(), addEventListener(), addInfoPane(), addInner(), addWindowListeners(), adjust(), al() (+124 more)

### Community 6 - "facet"
Cohesion: 0.05
Nodes (72): accept(), activateHover(), active(), apply(), b1(), baseTheme(), blur(), build() (+64 more)

### Community 7 - "E"
Cohesion: 0.05
Nodes (59): aa(), B(), Bf(), buildTicks(), calculateCircumference(), calculateLabelRotation(), cd(), _circumference() (+51 more)

### Community 8 - "markdown-editor.js"
Cohesion: 0.05
Nodes (90): Og(), ad(), af(), al(), An(), ao(), bc(), bo() (+82 more)

### Community 9 - "_update"
Cohesion: 0.04
Nodes (91): Z(), Q(), addBox(), addElements(), addEventListener(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits() (+83 more)

### Community 10 - "prop"
Cohesion: 0.06
Nodes (73): acceptToken(), allows(), AQ(), atLastNode(), au(), child(), childAfter(), childBefore() (+65 more)

### Community 11 - "components/select.js"
Cohesion: 0.07
Nodes (29): addBadgesForSelectedOptions(), addSingleBadge(), applyDisabledState(), Bt(), createBadgeElement(), createRemoveButton(), D(), destroy() (+21 more)

### Community 12 - "columns/select.js"
Cohesion: 0.09
Nodes (48): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), applyDisabledState(), closeDropdown(), constructor(), createBadgeElement(), createOptionElement() (+40 more)

### Community 13 - "create"
Cohesion: 0.04
Nodes (71): Cl(), clone(), create(), Ct(), dc(), Dl(), dtFormatter(), Ec() (+63 more)

### Community 14 - "fromObject"
Cohesion: 0.03
Nodes (120): Il(), Oe(), abutsStart(), ac(), ae(), after(), ag(), Al() (+112 more)

### Community 15 - "advance"
Cohesion: 0.05
Nodes (61): addChild(), addGaps(), addLeafElement(), addNode(), advance(), ATXHeading(), balance(), blank() (+53 more)

### Community 16 - "ApifyAccount.php"
Cohesion: 0.07
Nodes (13): ListGoogleMapsScrapeJobs, ApifyAccount, self, GoogleMapsScrapeJob, User, ApifyScraperService, DatabaseSeeder, Illuminate\Database\Eloquent\Factories\HasFactory (+5 more)

### Community 17 - "getDatasetMeta"
Cohesion: 0.08
Nodes (35): afterDatasetsUpdate(), An(), _f(), generateLabels(), getDatasetMeta(), getDataVisibility(), getIndexAngle(), getMaxBorderWidth() (+27 more)

### Community 18 - "_update"
Cohesion: 0.07
Nodes (50): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterDatasetsUpdate(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+42 more)

### Community 19 - ".slice"
Cohesion: 0.05
Nodes (64): accepts(), addInner(), addMaps(), addStep(), addTransform(), appendMap(), appendMapping(), appendMappingInverted() (+56 more)

### Community 20 - "o"
Cohesion: 0.03
Nodes (130): Mo(), nY(), readOnly(), Ym(), o(), _0(), addGlobalAttributes(), addInputRules() (+122 more)

### Community 21 - "toString"
Cohesion: 0.08
Nodes (33): ao(), append(), Bc(), Cc(), check(), checkAttrs(), checkContent(), cn() (+25 more)

### Community 22 - "create"
Cohesion: 0.07
Nodes (45): addChanges(), addSelection(), Ah(), applyTransaction(), asSingle(), changeByRange(), changes(), compose() (+37 more)

### Community 23 - "reduce"
Cohesion: 0.05
Nodes (67): addActions(), advanceFully(), advanceStack(), allActions(), attrs(), bi(), c0(), canShift() (+59 more)

### Community 24 - "getContext"
Cohesion: 0.06
Nodes (62): jo(), acquireContext(), Ae(), al(), bi(), buildTicks(), Ca(), calculateLabelRotation() (+54 more)

### Community 25 - "y"
Cohesion: 0.16
Nodes (57): at(), Be(), ca(), cd(), Cr(), Ct(), de(), dr() (+49 more)

### Community 26 - "n"
Cohesion: 0.08
Nodes (49): Ci(), Li(), Aa(), ai(), Ba(), Bi(), cf(), Et() (+41 more)

### Community 27 - "start"
Cohesion: 0.03
Nodes (117): Ad(), add(), ay(), Bg(), bt(), by(), cl(), closest() (+109 more)

### Community 28 - "notifications.js"
Cohesion: 0.06
Nodes (31): actions(), button(), c(), close(), configureAnimations(), configureTransitions(), constructor(), danger() (+23 more)

### Community 29 - "support.js"
Cohesion: 0.07
Nodes (42): acquireScrollLock(), At(), close(), closeQuietly(), commit(), destroy(), distribute(), En() (+34 more)

### Community 30 - "te"
Cohesion: 0.05
Nodes (8): Bn(), br(), ji(), Ri(), te(), Vi(), Xc(), Bd()

### Community 31 - "S"
Cohesion: 0.07
Nodes (42): afterAutoSkip(), buildLookupTable(), buildOrUpdateElements(), cl(), da(), Ds(), fr(), Fs() (+34 more)

### Community 32 - "updateElements"
Cohesion: 0.03
Nodes (111): gX(), acquireContext(), af(), Ao(), apply(), applyStack(), aspectRatio(), at() (+103 more)

### Community 33 - "slider.js"
Cohesion: 0.09
Nodes (34): Ae(), ar(), Be(), Bt(), De(), Ee(), er(), et() (+26 more)

### Community 34 - "pl"
Cohesion: 0.14
Nodes (18): Ao(), bl(), ci(), dn(), getIndexAngle(), getPointPosition(), hi(), interpolate() (+10 more)

### Community 35 - "get"
Cohesion: 0.03
Nodes (103): addBlockWidget(), addBreak(), addComposition(), addDelimiter(), addInlineWidget(), addLine(), addLineStart(), addLineStartIfNotCovered() (+95 more)

### Community 36 - "sliceDoc"
Cohesion: 0.08
Nodes (39): aO(), charCategorizer(), d0(), De(), Dg(), E$(), f0(), Fc() (+31 more)

### Community 37 - "initOffsets"
Cohesion: 0.14
Nodes (16): afterAutoSkip(), br(), buildLookupTable(), Gd(), getDecimalForPixel(), getDecimalForValue(), getValueForPixel(), If() (+8 more)

### Community 38 - "h"
Cohesion: 0.10
Nodes (60): forRange(), getSkippingParser(), kd(), load(), matchLanguageName(), W(), yf(), yS() (+52 more)

### Community 39 - "l"
Cohesion: 0.09
Nodes (57): B(), Hr(), Hs(), me(), mu(), ue(), wP(), Ae() (+49 more)

### Community 40 - "eq"
Cohesion: 0.08
Nodes (51): addAll(), addDOM(), addElement(), addElementByRule(), addStoredMark(), addTextNode(), addToSet(), allowsMarkType() (+43 more)

### Community 41 - "City"
Cohesion: 0.09
Nodes (9): AddressParser, Home, PostList, PostShow, Search, Category, City, Livewire\Component (+1 more)

### Community 42 - "_generate"
Cohesion: 0.09
Nodes (33): Bt(), determineDataLimits(), diff(), el(), En(), endOf(), _generate(), getAllParsedValues() (+25 more)

### Community 43 - "t"
Cohesion: 0.04
Nodes (94): between(), bu(), cd(), coordsForChar(), cP(), ef(), EP(), fP() (+86 more)

### Community 44 - "A"
Cohesion: 0.10
Nodes (26): A(), As(), buildOrUpdateScales(), cr(), ensureScalesHaveIDs(), fn(), getScale(), gl() (+18 more)

### Community 45 - "u"
Cohesion: 0.14
Nodes (40): ai(), Ao(), ar(), as(), c(), d(), di(), Ee() (+32 more)

### Community 46 - "lineAt"
Cohesion: 0.06
Nodes (45): addBlock(), addElement(), addLineDeco(), applyChanges(), balanced(), baseIndent(), baseIndentFor(), blankContent() (+37 more)

### Community 47 - "get"
Cohesion: 0.08
Nodes (38): Yg(), add(), bo(), bs(), ca(), _cachedScopes(), Ch(), Ci() (+30 more)

### Community 48 - "inRange"
Cohesion: 0.05
Nodes (62): ah(), average(), beforeDatasetsDraw(), beforeLayout(), bi(), bu(), cf(), dataset() (+54 more)

### Community 49 - "constructor"
Cohesion: 0.04
Nodes (69): ad(), alpha(), Bc(), Be(), bg(), $c(), chartOptionScopes(), co() (+61 more)

### Community 50 - "e"
Cohesion: 0.05
Nodes (64): addAttributes(), addNodeView(), B0(), c1(), cf(), chain(), Ck(), coordsAtPos() (+56 more)

### Community 51 - "Filament\Schemas\Schema"
Cohesion: 0.11
Nodes (10): ApifyAccountForm, CategoryForm, CityForm, PostForm, ProvinceForm, Province, Filament\Schemas\Schema, partials.searchable-select (+2 more)

### Community 52 - "ERD.md"
Cohesion: 0.04
Nodes (47): 10. Relationship Summary, 11. Standard Columns, 12. Naming Convention, 13. Future Expansion, 1. Purpose, 2. Design Principles, 3. Conceptual ERD, 4. Master Tables (+39 more)

### Community 53 - "Cn"
Cohesion: 0.17
Nodes (26): _a(), Bi(), Bt(), ca(), Cn(), ct(), eo(), Fa() (+18 more)

### Community 54 - "buildOrUpdateControllers"
Cohesion: 0.09
Nodes (25): addElements(), buildOrUpdateControllers(), _checkEventBindings(), _dataCheck(), datasetAnimationScopeKeys(), _destroy(), _destroyDatasetMeta(), di() (+17 more)

### Community 55 - "parse"
Cohesion: 0.16
Nodes (18): Yd(), Bm(), Dc(), defaultType(), eat(), err(), forEachCell(), hasRequiredAttrs() (+10 more)

### Community 56 - "MODULES.md"
Cohesion: 0.04
Nodes (44): 10. System Modules, 11. Module Ownership, 12. Folder Structure, 13. Communication Rules, 14. Event Driven, 15. Future Modules, 16. Development Rules, 17. Module Lifecycle (+36 more)

### Community 57 - "dl"
Cohesion: 0.07
Nodes (37): ar(), bs(), describe(), dl(), ei(), er(), first(), getMatchingVisibleMetas() (+29 more)

### Community 58 - "public/app.js"
Cohesion: 0.14
Nodes (44): api(), appendLog(), closeModal(), connectSSE(), esc(), formatBytes(), formatDate(), formatNum() (+36 more)

### Community 59 - "echo.js"
Cohesion: 0.09
Nodes (15): a(), ar(), at(), cr(), d(), f(), H(), ji() (+7 more)

### Community 60 - ".get"
Cohesion: 0.09
Nodes (47): at(), bk(), bp(), colCount(), colSelection(), Dk(), dp(), edge() (+39 more)

### Community 61 - "00-VISION.md"
Cohesion: 0.05
Nodes (43): 1. Mempermudah masyarakat menemukan informasi lokal, 2. Menjadi sumber informasi yang terpercaya, 3. Menghubungkan masyarakat dengan bisnis lokal, 4. Mengembangkan ekosistem informasi lokal, About DISEKITAR, Administrator, AI Ready, API Ready (+35 more)

### Community 62 - "eq"
Cohesion: 0.06
Nodes (46): activeForPoint(), addActive(), Ar(), as(), be(), boundChange(), chunkEnd(), commit() (+38 more)

### Community 63 - "slice"
Cohesion: 0.05
Nodes (61): addToSet(), bd(), Bh(), childString(), clearDelayedAndroidKey(), createParse(), dd(), decompose() (+53 more)

### Community 64 - "find"
Cohesion: 0.13
Nodes (26): baseDirAt(), bidiIn(), bidiSpans(), bidiSpansAt(), checkHover(), coordsAtPos(), Df(), dirAt() (+18 more)

### Community 65 - "static"
Cohesion: 0.12
Nodes (10): UserFactory, Facebook\WebDriver\Remote\RemoteWebDriver, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Collection, Laravel\Dusk\TestCase, RemoteWebDriver, static (+2 more)

### Community 66 - "05-CODING-STANDARD.md"
Cohesion: 0.05
Nodes (42): Action Rules, AI Coding Rules, API Response, Blade Standard, Class, Comments, Configuration, Constant (+34 more)

### Community 67 - "xt"
Cohesion: 0.14
Nodes (26): Ra(), ae(), B(), cr(), de(), dt(), fr(), Ge() (+18 more)

### Community 68 - "_notify"
Cohesion: 0.29
Nodes (10): br(), cancel(), _createDescriptors(), _descriptors(), getPlugin(), _notify(), _notifyStateChanges(), remove() (+2 more)

### Community 69 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.06
Nodes (17): CreateApifyAccount, EditApifyAccount, ListApifyAccounts, CreateCategory, EditCategory, ListCategories, CreateCity, EditCity (+9 more)

### Community 70 - "home.blade.php"
Cohesion: 0.50
Nodes (3): partials.category-tabs, partials.article-feed, partials.editor-picks

### Community 71 - "fn"
Cohesion: 0.13
Nodes (30): aa(), ba(), br(), da(), Dn(), Ea(), ei(), Fi() (+22 more)

### Community 72 - "constructor"
Cohesion: 0.05
Nodes (50): Ot(), alpha(), apply(), be(), bo(), chartOptionScopes(), co(), constructor() (+42 more)

### Community 73 - "file-upload.js"
Cohesion: 0.05
Nodes (54): hc(), Bp(), c(), ca(), clickPercent(), cm(), constructor(), Cp() (+46 more)

### Community 74 - "tables.js"
Cohesion: 0.13
Nodes (45): A(), ae(), B(), be(), C(), ce(), E(), ee() (+37 more)

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
Cohesion: 0.15
Nodes (14): Ce(), De(), di(), e(), Ht(), Ie(), Me(), Re() (+6 more)

### Community 79 - "addEventListener"
Cohesion: 0.09
Nodes (26): Nn(), aa(), active(), addEventListener(), ai(), _animateOptions(), bindEvents(), bindResponsiveEvents() (+18 more)

### Community 80 - "add"
Cohesion: 0.10
Nodes (28): add(), _cachedScopes(), createResolver(), datasetElementScopeKeys(), get(), _getAnims(), getMaxOverflow(), getOptionScopes() (+20 more)

### Community 81 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.15
Nodes (6): EditPost, ListPosts, PostDetail, PostImage, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes

### Community 82 - "selectRecords"
Cohesion: 0.20
Nodes (18): areRecordsPartiallySelected(), areRecordsSelected(), areRecordsToggleable(), canSelectAllRecords(), deselectAllRecords(), deselectRecords(), getRecordsOnPage(), getSelectedRecordsCount() (+10 more)

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

### Community 92 - "Si"
Cohesion: 0.13
Nodes (39): At(), bi(), bn(), ci(), cn(), ct(), de(), di() (+31 more)

### Community 93 - "Illuminate\Console\Command"
Cohesion: 0.29
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

### Community 97 - "ls"
Cohesion: 0.24
Nodes (11): cs(), Io(), kn(), ls(), os(), si(), us(), ws() (+3 more)

### Community 98 - "Post"
Cohesion: 0.09
Nodes (4): Controller, SitemapController, Post, Illuminate\Http\Response

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
Nodes (32): AI Coding Guidelines, AI Search Readiness, Breadcrumb, Canonical URL, Content Quality, Definition of Done (SEO), DISEKITAR SEO Guidelines, Duplicate Content (+24 more)

### Community 108 - "post-autoload-dump"
Cohesion: 0.50
Nodes (4): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan filament:upgrade, @php artisan package:discover --ansi

### Community 109 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 110 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 126 - "DOMAIN_MODEL.md"
Cohesion: 0.06
Nodes (31): 10. Audit, 11. Future Modules, 12. Naming Convention, 13. Out of Scope, 1. Purpose, 2. Domain Overview, 3.1 Single Source of Truth, 3.2 Reusable Entity (+23 more)

### Community 128 - "_notify"
Cohesion: 0.15
Nodes (17): active(), _animateOptions(), cancel(), _createAnimations(), _createDescriptors(), _d(), _descriptors(), _notify() (+9 more)

### Community 129 - "RunGoogleMapsScrapeJob.php"
Cohesion: 0.11
Nodes (13): ProcessBatchAiQueueCommand, ListAiGenerationJobs, GeneratePostContentJob, ProcessBatchAiQueueJob, RunGoogleMapsScrapeJob, AiGenerationJob, PostAiContentService, Illuminate\Bus\Queueable (+5 more)

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

### Community 174 - "g$"
Cohesion: 0.04
Nodes (61): aa(), Ag(), AX(), Bg(), combine(), continue(), cS(), cX() (+53 more)

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
Nodes (28): A(), An(), b(), Bt(), D(), E(), F(), gt() (+20 more)

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
Nodes (18): AiGenerationJobResource, AiGenerationJobsTable, ApifyAccountResource, ApifyAccountsTable, CategoryResource, CategoriesTable, CityResource, CitiesTable (+10 more)

### Community 185 - "Analisis Project DISEKITAR"
Cohesion: 0.12
Nodes (16): 10. Insight dari Graphify, 11. Ringkasan Eksekutif, 1. Identitas & Visi Produk, 2. Tech Stack, 3. Arsitektur, 4. Domain Model & Database, 5. Routing & Controller, 6. Model & Relasi Eloquent (+8 more)

### Community 186 - "ROLE_MATRIX.md"
Cohesion: 0.12
Nodes (16): AI May Suggest, AI Must NOT, AI Responsibilities, Communication Rules, Decision Authority, DISEKITAR Task Role Matrix, Documentation Rules, Escalation Rules (+8 more)

### Community 187 - "Mt"
Cohesion: 0.27
Nodes (10): apply(), fs(), go(), Hr(), ir(), it(), Mt(), Wa() (+2 more)

### Community 188 - "fn"
Cohesion: 0.22
Nodes (18): An(), Ce(), ei(), fn(), Ft(), Ie(), Le(), ni() (+10 more)

### Community 189 - "fn"
Cohesion: 0.24
Nodes (17): Ce(), ei(), fn(), Ft(), Ie(), Le(), ni(), oe() (+9 more)

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

### Community 197 - "selectOption"
Cohesion: 0.17
Nodes (31): addSingleSelectionDisplay(), closeDropdown(), constructor(), createOptionElement(), deferPositionDropdown(), filterOptions(), focusNextOption(), focusPreviousOption() (+23 more)

### Community 199 - "draw"
Cohesion: 0.08
Nodes (38): adjustHitBoxes(), afterDraw(), ar(), beforeDatasetDraw(), dn(), draw(), drawBackground(), drawBody() (+30 more)

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

### Community 208 - "scrapePlacePhotos"
Cohesion: 0.43
Nodes (6): apify-client, apify-client, getNextRotatedAccount(), getDuckDuckGoVQD(), isValidImageUrl(), scrapePlacePhotos()

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

## Knowledge Gaps
- **1018 isolated node(s):** `$schema`, `.opencode/plugins/graphify.js`, `$schema`, `name`, `type` (+1013 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **17 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `o()` connect `o` to `_notify`, `components/chart.js`, `rich-editor.js`, `stat/chart.js`, `resolve`, `constructor`, `facet`, `E`, `_update`, `prop`, `components/select.js`, `create`, `fromObject`, `advance`, `getDatasetMeta`, `.slice`, `create`, `getContext`, `start`, `notifications.js`, `S`, `updateElements`, `pl`, `get`, `initOffsets`, `h`, `l`, `eq`, `_generate`, `t`, `A`, `u`, `g$`, `get`, `inRange`, `constructor`, `e`, `n`, `Cn`, `parse`, `dl`, `fn`, `fn`, `eq`, `slice`, `find`, `nr`, `_notify`, `draw`, `constructor`, `file-upload.js`, `t`, `addEventListener`, `add`, `Si`, `Xt`, `r`?**
  _High betweenness centrality (0.047) - this node is a cross-community bridge._
- **Why does `l()` connect `l` to `code-editor.js`, `_notify`, `rich-editor.js`, `components/chart.js`, `resolve`, `constructor`, `stat/chart.js`, `markdown-editor.js`, `_update`, `prop`, `create`, `fromObject`, `advance`, `.slice`, `o`, `toString`, `create`, `reduce`, `getContext`, `y`, `n`, `start`, `S`, `updateElements`, `pl`, `h`, `eq`, `_generate`, `t`, `lineAt`, `inRange`, `constructor`, `e`, `n`, `parse`, `dl`, `echo.js`, `eq`, `slice`, `xt`, `fn`, `draw`, `file-upload.js`, `tables.js`, `addEventListener`, `Si`, `Xt`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `_s()` connect `components/chart.js` to `inRange`, `rich-editor.js`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Are the 186 inferred relationships involving `o()` (e.g. with `Be()` and `i()`) actually correct?**
  _`o()` has 186 INFERRED edges - model-reasoned connections that need verification._
- **Are the 153 inferred relationships involving `t()` (e.g. with `add()` and `addCompletions()`) actually correct?**
  _`t()` has 153 INFERRED edges - model-reasoned connections that need verification._
- **Are the 144 inferred relationships involving `i()` (e.g. with `add()` and `addElement()`) actually correct?**
  _`i()` has 144 INFERRED edges - model-reasoned connections that need verification._
- **Are the 17 inferred relationships involving `constructor()` (e.g. with `gQ()` and `o()`) actually correct?**
  _`constructor()` has 17 INFERRED edges - model-reasoned connections that need verification._