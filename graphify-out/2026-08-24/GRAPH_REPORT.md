# Graph Report - disekiar2  (2026-08-24)

## Corpus Check
- 242 files · ~158,383 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 6808 nodes · 19133 edges · 254 communities (236 shown, 18 thin omitted)
- Extraction: 88% EXTRACTED · 12% INFERRED · 0% AMBIGUOUS · INFERRED: 2282 edges (avg confidence: 0.71)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `6670eff4`
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
- draw
- markdown-editor.js
- _update
- prop
- components/select.js
- closeDropdown
- create
- fromObject
- reduce
- ApifyAccount
- eq
- _update
- child
- Je
- nodeAt
- Illuminate\Database\Eloquent\Model
- slice
- getContext
- ne
- dx
- constructor
- notifications.js
- support.js
- te
- pl
- updateElements
- slider.js
- _each
- i
- match
- buildTicks
- V
- l
- parse
- Category
- parse
- t
- A
- u
- lineAt
- get
- ar
- findIndex
- o
- Filament\Schemas\Schema
- ERD.md
- Cn
- draw
- _resolveAnimations
- MODULES.md
- dl
- public/app.js
- echo.js
- Ye
- 00-VISION.md
- create
- getDatasetMeta
- find
- DuskTestCase.php
- 05-CODING-STANDARD.md
- xt
- toString
- Filament\Resources\Pages\ListRecords
- home.blade.php
- ei
- fo
- file-upload.js
- tables.js
- filament/app.js
- devDependencies
- 08-UI-UX.md
- t
- E
- S
- fn
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
- Si
- Illuminate\Console\Command
- Xt
- config
- r
- cl
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
- photoWorker.js
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
- W
- exporters/index.js
- 07-FEATURES.md
- PROMPT_REFACTOR.md
- GoogleMapsImportService
- h
- color-picker.js
- PROMPT_DEVELOPMENT.md
- URL_STRUCTURE.md
- PROMPT_ONBOARDING.md
- Filament\Tables\Table
- Analisis Project DISEKITAR
- ROLE_MATRIX.md
- En
- fn
- columns/select.js
- Google Maps Data Extractor v2 (Node.js & Apify Edition)
- tasks/README.md
- 01-ROADMAP.md
- Cloud-Native Ready Philosophy
- pagination.blade.php
- nr
- FE‑001 — Public Frontend Foundation
- closeDropdown
- constructor
- Phase 1 — Core CMS
- P
- Core Content
- src/index.js
- Discovery Features
- README.md
- Production Readiness Checklist
- selectOption
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
- ApifyScraperService
- cc
- autoload-dev
- landmark-show.blade.php
- of

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
- `cm()` --indirect_call--> `me()`  [INFERRED]
  public/js/filament/forms/components/file-upload.js → public/js/filament/forms/components/code-editor.js
- `xQ()` --indirect_call--> `ay()`  [INFERRED]
  public/js/filament/forms/components/code-editor.js → public/js/filament/forms/components/rich-editor.js
- `constructor()` --indirect_call--> `i()`  [INFERRED]
  public/js/filament/forms/components/color-picker.js → public/js/filament/forms/components/date-time-picker.js
- `[x]()` --indirect_call--> `H()`  [INFERRED]
  public/js/filament/forms/components/color-picker.js → public/js/filament/forms/components/markdown-editor.js

## Import Cycles
- None detected.

## Communities (254 total, 18 thin omitted)

### Community 0 - "code-editor.js"
Cohesion: 0.01
Nodes (101): Ac(), addCompletion(), addCompletions(), addEventListener(), addNamespace(), addNamespaceObject(), addWindowListeners(), Ag() (+93 more)

### Community 1 - "components/chart.js"
Cohesion: 0.01
Nodes (121): Il(), $a(), abutsStart(), addControllers(), addPlugins(), addScales(), afterDraw(), bd() (+113 more)

### Community 2 - "rich-editor.js"
Cohesion: 0.01
Nodes (144): _0(), aa(), addHackNode(), addOptions(), addTextblockHacks(), applyAspectRatio(), applyConstraints(), atEnd() (+136 more)

### Community 3 - "stat/chart.js"
Cohesion: 0.03
Nodes (80): applyStack(), ba(), be(), beforeDatasetsDraw(), beforeDraw(), Bn(), color(), ct() (+72 more)

### Community 4 - "resolve"
Cohesion: 0.06
Nodes (124): p(), Ac(), addKeyboardShortcuts(), after(), al(), ay(), before(), blockRange() (+116 more)

### Community 5 - "constructor"
Cohesion: 0.03
Nodes (137): add(), addChunk(), addInfoPane(), addInner(), adjust(), al(), annotation(), applyEdits() (+129 more)

### Community 6 - "facet"
Cohesion: 0.05
Nodes (55): a$(), accept(), active(), b1(), blur(), charCategorizer(), compute(), cP() (+47 more)

### Community 7 - "draw"
Cohesion: 0.04
Nodes (97): _freeze(), getAllExtensions(), acquireContext(), adjustHitBoxes(), bh(), bu(), calculateLabelRotation(), _calculatePadding() (+89 more)

### Community 8 - "markdown-editor.js"
Cohesion: 0.05
Nodes (83): Ac(), ad(), af(), al(), ao(), bl(), bo(), Bt() (+75 more)

### Community 9 - "_update"
Cohesion: 0.04
Nodes (102): addBox(), addElements(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterDatasetsUpdate(), afterFit(), afterSetDimensions() (+94 more)

### Community 10 - "prop"
Cohesion: 0.06
Nodes (67): acceptToken(), allows(), AQ(), atLastNode(), au(), child(), childAfter(), childBefore() (+59 more)

### Community 11 - "components/select.js"
Cohesion: 0.07
Nodes (37): g(), addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), b(), Bt(), createBadgeElement(), createRemoveButton() (+29 more)

### Community 12 - "closeDropdown"
Cohesion: 0.20
Nodes (25): closeDropdown(), constructor(), deferPositionDropdown(), destroy(), filterOptions(), focusNextOption(), focusPreviousOption(), getVisibleOptions() (+17 more)

### Community 13 - "create"
Cohesion: 0.04
Nodes (71): cg(), Cl(), clone(), create(), dc(), Dl(), dtFormatter(), Ec() (+63 more)

### Community 14 - "fromObject"
Cohesion: 0.04
Nodes (105): Oe(), ac(), ae(), after(), ag(), Al(), Am(), before() (+97 more)

### Community 15 - "reduce"
Cohesion: 0.06
Nodes (59): addActions(), advanceFully(), advanceStack(), allActions(), apply(), b0(), c0(), canShift() (+51 more)

### Community 16 - "ApifyAccount"
Cohesion: 0.09
Nodes (12): ListGoogleMapsScrapeJobs, ApifyAccount, self, GoogleMapsScrapeJob, User, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Factories\HasFactory (+4 more)

### Community 17 - "eq"
Cohesion: 0.06
Nodes (51): activeForPoint(), addBlock(), addBlockWidget(), addBreak(), addDelimiter(), addInlineWidget(), addLine(), addLineDeco() (+43 more)

### Community 18 - "_update"
Cohesion: 0.07
Nodes (42): themeClasses(), Ot(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion() (+34 more)

### Community 19 - "child"
Cohesion: 0.05
Nodes (106): addCommands(), addNodeMark(), addProseMirrorPlugins(), ag(), allowedMarks(), allowsMarks(), AS(), Bf() (+98 more)

### Community 20 - "Je"
Cohesion: 0.04
Nodes (85): addGlobalAttributes(), addInputRules(), addMark(), addPasteRules(), addStoredMark(), addToSet(), Ah(), allowsMarkType() (+77 more)

### Community 21 - "nodeAt"
Cohesion: 0.08
Nodes (41): Bc(), cellsInRect(), checkAttrs(), content(), ct(), dS(), endIndex(), findCell() (+33 more)

### Community 22 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.09
Nodes (9): EditPost, ListPosts, PostDetail, PostImage, DatabaseSeeder, LandmarkSeeder, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes (+1 more)

### Community 23 - "slice"
Cohesion: 0.03
Nodes (95): addChild(), addGaps(), addLeafElement(), addNode(), advance(), ATXHeading(), balance(), blank() (+87 more)

### Community 24 - "getContext"
Cohesion: 0.08
Nodes (49): acquireContext(), Ae(), buildTicks(), Ca(), calculateLabelRotation(), _calculatePadding(), _computeGridLineItems(), _computeLabelArea() (+41 more)

### Community 25 - "ne"
Cohesion: 0.07
Nodes (58): xe(), An(), Ba(), cd(), cl(), Do(), dt(), el() (+50 more)

### Community 26 - "dx"
Cohesion: 0.11
Nodes (30): Ei(), Aa(), ai(), Bi(), cf(), Jc(), Jr(), ki() (+22 more)

### Community 27 - "constructor"
Cohesion: 0.02
Nodes (135): add(), addNode(), ao(), applyInitialSize(), Bd(), Bg(), Bo(), bt() (+127 more)

### Community 28 - "notifications.js"
Cohesion: 0.06
Nodes (31): actions(), button(), c(), close(), configureAnimations(), configureTransitions(), constructor(), danger() (+23 more)

### Community 29 - "support.js"
Cohesion: 0.05
Nodes (60): acquireScrollLock(), ae(), Ao(), as(), close(), closeQuietly(), commit(), cs() (+52 more)

### Community 30 - "te"
Cohesion: 0.05
Nodes (35): at(), Be(), Bn(), br(), de(), Ee(), ei(), fe() (+27 more)

### Community 31 - "pl"
Cohesion: 0.09
Nodes (26): Ao(), bl(), ci(), dn(), Fs(), getBasePosition(), getBaseValue(), getIndexAngle() (+18 more)

### Community 32 - "updateElements"
Cohesion: 0.04
Nodes (71): addEventListener(), Ao(), aspectRatio(), au(), bindResponsiveEvents(), _calculateBarIndexPixels(), _calculateBarValuePixels(), calculateCircumference() (+63 more)

### Community 33 - "slider.js"
Cohesion: 0.09
Nodes (36): Ae(), ar(), Be(), Bt(), De(), _e(), Ee(), er() (+28 more)

### Community 34 - "_each"
Cohesion: 0.09
Nodes (27): active(), addControllers(), addPlugins(), addScales(), _animateOptions(), br(), cancel(), _createAnimations() (+19 more)

### Community 35 - "i"
Cohesion: 0.04
Nodes (84): addComposition(), addRange(), after(), aP(), AX(), before(), bP(), Ci() (+76 more)

### Community 36 - "match"
Cohesion: 0.11
Nodes (28): d0(), De(), Dg(), E$(), f0(), getCursor(), gT(), h0() (+20 more)

### Community 37 - "buildTicks"
Cohesion: 0.07
Nodes (42): af(), afterAutoSkip(), at(), Bf(), br(), buildLookupTable(), buildTicks(), determineDataLimits() (+34 more)

### Community 38 - "V"
Cohesion: 0.21
Nodes (41): d(), b(), $c(), ca(), D(), _e(), f(), G() (+33 more)

### Community 39 - "l"
Cohesion: 0.09
Nodes (39): m(), Ae(), ar(), as(), bf(), ci(), Cn(), Fc() (+31 more)

### Community 40 - "parse"
Cohesion: 0.10
Nodes (31): addAll(), addDOM(), addElement(), addElementByRule(), addTextNode(), Bm(), eat(), enter() (+23 more)

### Community 41 - "Category"
Cohesion: 0.07
Nodes (13): AddressParser, Controller, SitemapController, Home, LandmarkShow, PostList, Search, Category (+5 more)

### Community 42 - "parse"
Cohesion: 0.08
Nodes (41): Bt(), cl(), Cn(), determineDataLimits(), diff(), el(), En(), endOf() (+33 more)

### Community 43 - "t"
Cohesion: 0.08
Nodes (52): B(), me(), Nh(), node(), e(), r(), s(), t() (+44 more)

### Community 44 - "A"
Cohesion: 0.10
Nodes (27): A(), As(), buildOrUpdateScales(), _computeLabelSizes(), cr(), ensureScalesHaveIDs(), getScale(), gl() (+19 more)

### Community 45 - "u"
Cohesion: 0.23
Nodes (32): k(), _a(), ai(), ar(), c(), d(), di(), fn() (+24 more)

### Community 46 - "lineAt"
Cohesion: 0.05
Nodes (68): addElement(), applyChanges(), balanced(), baseIndent(), baseIndentFor(), blockAt(), changeByRange(), changes() (+60 more)

### Community 47 - "get"
Cohesion: 0.05
Nodes (52): add(), applyStack(), bo(), bs(), ca(), _cachedScopes(), Ch(), Ci() (+44 more)

### Community 48 - "ar"
Cohesion: 0.05
Nodes (54): ah(), ar(), average(), beforeDatasetDraw(), beforeDatasetsDraw(), beforeDraw(), beforeLayout(), bi() (+46 more)

### Community 49 - "findIndex"
Cohesion: 0.17
Nodes (16): addInner(), findIndex(), forChild(), io(), locals(), localsInner(), mapInner(), No() (+8 more)

### Community 50 - "o"
Cohesion: 0.04
Nodes (80): o(), Ad(), addAttributes(), addNodeView(), af(), ak(), an(), au() (+72 more)

### Community 51 - "Filament\Schemas\Schema"
Cohesion: 0.10
Nodes (11): ApifyAccountForm, CategoryForm, CityForm, LandmarkForm, PostForm, ProvinceForm, Province, Filament\Schemas\Schema (+3 more)

### Community 52 - "ERD.md"
Cohesion: 0.04
Nodes (47): 10. Relationship Summary, 11. Standard Columns, 12. Naming Convention, 13. Future Expansion, 1. Purpose, 2. Design Principles, 3. Conceptual ERD, 4. Master Tables (+39 more)

### Community 53 - "Cn"
Cohesion: 0.14
Nodes (36): Bi(), br(), Bt(), ca(), Cn(), ct(), Dn(), Ea() (+28 more)

### Community 54 - "draw"
Cohesion: 0.09
Nodes (35): Nn(), aa(), addElements(), addEventListener(), bi(), bindEvents(), bindResponsiveEvents(), bindUserEvents() (+27 more)

### Community 55 - "_resolveAnimations"
Cohesion: 0.10
Nodes (22): buildOrUpdateElements(), _dataCheck(), datasetAnimationScopeKeys(), datasetElementScopeKeys(), getDataset(), getMaxOverflow(), getPadding(), getScaleForId() (+14 more)

### Community 56 - "MODULES.md"
Cohesion: 0.04
Nodes (44): 10. System Modules, 11. Module Ownership, 12. Folder Structure, 13. Communication Rules, 14. Event Driven, 15. Future Modules, 16. Development Rules, 17. Module Lifecycle (+36 more)

### Community 57 - "dl"
Cohesion: 0.06
Nodes (39): ai(), al(), ar(), bs(), describe(), dl(), ei(), er() (+31 more)

### Community 58 - "public/app.js"
Cohesion: 0.14
Nodes (44): api(), appendLog(), closeModal(), connectSSE(), esc(), formatBytes(), formatDate(), formatNum() (+36 more)

### Community 59 - "echo.js"
Cohesion: 0.08
Nodes (19): a(), ar(), at(), cr(), d(), f(), H(), ii() (+11 more)

### Community 60 - "Ye"
Cohesion: 0.09
Nodes (48): Rd(), $a(), at(), bk(), bp(), Dk(), dp(), Ek() (+40 more)

### Community 61 - "00-VISION.md"
Cohesion: 0.05
Nodes (43): 1. Mempermudah masyarakat menemukan informasi lokal, 2. Menjadi sumber informasi yang terpercaya, 3. Menghubungkan masyarakat dengan bisnis lokal, 4. Mengembangkan ekosistem informasi lokal, About DISEKITAR, Administrator, AI Ready, API Ready (+35 more)

### Community 62 - "create"
Cohesion: 0.06
Nodes (51): addActive(), addChanges(), addSelection(), Ah(), applyTransaction(), Ar(), as(), asSingle() (+43 more)

### Community 63 - "getDatasetMeta"
Cohesion: 0.12
Nodes (24): afterDatasetsUpdate(), buildOrUpdateControllers(), _destroyDatasetMeta(), getController(), getDatasetMeta(), getElement(), _handleEvent(), hide() (+16 more)

### Community 64 - "find"
Cohesion: 0.10
Nodes (33): activateHover(), baseDirAt(), bidiIn(), bidiSpans(), bidiSpansAt(), cd(), checkHover(), coordsAtPos() (+25 more)

### Community 65 - "DuskTestCase.php"
Cohesion: 0.20
Nodes (7): Facebook\WebDriver\Remote\RemoteWebDriver, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Collection, Laravel\Dusk\TestCase, RemoteWebDriver, DuskTestCase, TestCase

### Community 66 - "05-CODING-STANDARD.md"
Cohesion: 0.05
Nodes (42): Action Rules, AI Coding Rules, API Response, Blade Standard, Class, Comments, Configuration, Constant (+34 more)

### Community 67 - "xt"
Cohesion: 0.14
Nodes (25): cr(), de(), dt(), Ee(), fr(), Ge(), Gt(), he() (+17 more)

### Community 68 - "toString"
Cohesion: 0.13
Nodes (21): addToSet(), bd(), Bh(), childString(), clearDelayedAndroidKey(), delayAndroidKey(), flushIOSKey(), ignore() (+13 more)

### Community 69 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.06
Nodes (19): CreateApifyAccount, EditApifyAccount, ListApifyAccounts, CreateCategory, EditCategory, ListCategories, CreateCity, EditCity (+11 more)

### Community 70 - "home.blade.php"
Cohesion: 0.50
Nodes (3): partials.category-tabs, partials.article-feed, partials.editor-picks

### Community 71 - "ei"
Cohesion: 0.22
Nodes (13): aa(), ba(), da(), ei(), gr(), Li(), mr(), Pn() (+5 more)

### Community 72 - "fo"
Cohesion: 0.09
Nodes (32): Qi(), alpha(), bo(), co(), es(), et(), fo(), go() (+24 more)

### Community 73 - "file-upload.js"
Cohesion: 0.05
Nodes (51): hc(), Bp(), c(), ca(), clickPercent(), cm(), constructor(), Cp() (+43 more)

### Community 74 - "tables.js"
Cohesion: 0.09
Nodes (64): A(), ae(), areRecordsPartiallySelected(), areRecordsSelected(), areRecordsToggleable(), B(), be(), C() (+56 more)

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

### Community 79 - "E"
Cohesion: 0.08
Nodes (40): aa(), B(), Ba(), cd(), _computeLabelSizes(), E(), formats(), getLabels() (+32 more)

### Community 80 - "S"
Cohesion: 0.07
Nodes (44): add(), afterAutoSkip(), buildLookupTable(), _cachedScopes(), configure(), createResolver(), da(), datasetScopeKeys() (+36 more)

### Community 81 - "fn"
Cohesion: 0.24
Nodes (17): Ce(), ei(), fn(), Ft(), Ie(), Le(), ni(), oe() (+9 more)

### Community 82 - ".slice"
Cohesion: 0.05
Nodes (57): accepts(), addMaps(), addStep(), addTransform(), append(), appendMap(), appendMapping(), appendMappingInverted() (+49 more)

### Community 83 - "composer.json"
Cohesion: 0.14
Nodes (13): description, extra, laravel, keywords, dont-discover, license, minimum-stability, name (+5 more)

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
Cohesion: 0.13
Nodes (26): clearPhotoQueue(), deleteAccount(), deleteTask(), enqueuePlacesForPhotoEnrichment(), getCategories(), getPhotoWorkerStats(), getStats(), getTaskById() (+18 more)

### Community 91 - "PERMISSION_MODEL.md"
Cohesion: 0.05
Nodes (36): 10. Middleware, 11. Laravel Policy, 12. UI Permission, 13. API Permission, 14. Audit Trail, 15. Future Multi-Tenant, 16. Database Structure, 17. Best Practices (+28 more)

### Community 92 - "Si"
Cohesion: 0.14
Nodes (38): At(), bi(), bn(), ci(), cn(), ct(), de(), di() (+30 more)

### Community 93 - "Illuminate\Console\Command"
Cohesion: 0.27
Nodes (5): GenerateCitySlug, GenerateProvinceSlug, ImportGoogleMapsData, Command, Illuminate\Console\Command

### Community 94 - "Xt"
Cohesion: 0.13
Nodes (39): ae(), At(), bi(), bn(), ci(), cn(), ct(), de() (+31 more)

### Community 95 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 96 - "r"
Cohesion: 0.21
Nodes (12): Be(), ei(), Fe(), He(), i(), le(), Mt(), ni() (+4 more)

### Community 97 - "cl"
Cohesion: 0.18
Nodes (15): cl(), gr(), gu(), iy(), kr(), Ln(), ly(), Md() (+7 more)

### Community 98 - "Post"
Cohesion: 0.07
Nodes (3): PostShow, Post, static

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

### Community 110 - "photoWorker.js"
Cohesion: 0.25
Nodes (12): apify-client, apify-client, getNextRotatedAccount(), getPendingPhotoQueue(), resetHangingQueueItems(), updatePlacePhotos(), updateQueueItemStatus(), getDuckDuckGoVQD() (+4 more)

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
Cohesion: 0.11
Nodes (33): ACCOUNT_COLUMNS, ACCOUNT_STATUSES, createTask(), DB_DIR, DB_PATH, FINAL_STATUSES, getAccountByEmail(), getAccountByToken() (+25 more)

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

### Community 174 - "W"
Cohesion: 0.05
Nodes (58): aa(), aO(), Bg(), ch(), cS(), _d(), enterUnfinishedNodesBefore(), eS() (+50 more)

### Community 175 - "exporters/index.js"
Cohesion: 0.14
Nodes (21): RFC-4180, DEFAULT_COLUMNS, escapeCsvField(), exportToCsv(), formatField(), COLUMNS, exportToExcel(), formatField() (+13 more)

### Community 176 - "07-FEATURES.md"
Cohesion: 0.08
Nodes (25): AI Coding Guidelines, API Features, Definition of Done, DISEKITAR Features Specification, Event Platform, Feature Dependencies, Feature Principles, Feature Status (+17 more)

### Community 177 - "PROMPT_REFACTOR.md"
Cohesion: 0.08
Nodes (24): AI Must NOT, Benefits, DISEKITAR AI Refactoring Prompt, Documentation Updated, Files Modified, Purpose, Refactoring Performed, Refactoring Principles (+16 more)

### Community 179 - "h"
Cohesion: 0.11
Nodes (28): h(), A(), An(), b(), Bt(), D(), E(), F() (+20 more)

### Community 180 - "color-picker.js"
Cohesion: 0.13
Nodes (5): constructor(), [g](), style(), update(), [x]()

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
Cohesion: 0.13
Nodes (19): Ra(), apply(), At(), B(), En(), fs(), go(), Hr() (+11 more)

### Community 188 - "fn"
Cohesion: 0.21
Nodes (18): An(), Ce(), ei(), fn(), Ft(), Ie(), Le(), ni() (+10 more)

### Community 189 - "columns/select.js"
Cohesion: 0.10
Nodes (23): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), applyDisabledState(), createBadgeElement(), createOptionElement(), createRemoveButton(), disable() (+15 more)

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

### Community 197 - "closeDropdown"
Cohesion: 0.23
Nodes (17): applyDisabledState(), closeDropdown(), constructor(), destroy(), disable(), enable(), focusNextOption(), focusPreviousOption() (+9 more)

### Community 199 - "constructor"
Cohesion: 0.04
Nodes (72): ad(), alpha(), apply(), Bc(), bg(), chartOptionScopes(), co(), constructor() (+64 more)

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
Cohesion: 0.24
Nodes (10): accountFromId(), getAccountById(), getAccounts(), incrementAccountUsage(), program, runInteractiveMenu(), DEFAULT_ACTOR, scrapeGooglePlaces() (+2 more)

### Community 204 - "Discovery Features"
Cohesion: 0.22
Nodes (9): Discovery Features, Features, Features, Features, Features, Interactive Map, Nearby, Recommendation (+1 more)

### Community 206 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 207 - "Production Readiness Checklist"
Cohesion: 0.25
Nodes (8): Application, Database, Infrastructure, Monitoring, Performance, Production Readiness Checklist, Security, SEO

### Community 208 - "selectOption"
Cohesion: 0.27
Nodes (17): createOptionElement(), deferPositionDropdown(), filterOptions(), handleSearch(), hideLoadingState(), hideMaxItemsMessage(), maintainFocusInMultipleMode(), openDropdown() (+9 more)

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

### Community 249 - "cc"
Cohesion: 0.38
Nodes (7): attrs(), cc(), JQ(), m$(), Ow(), rc(), read()

### Community 250 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 256 - "of"
Cohesion: 0.04
Nodes (62): baseTheme(), between(), bi(), bu(), build(), cO(), configure(), cX() (+54 more)

## Knowledge Gaps
- **1022 isolated node(s):** `$schema`, `.opencode/plugins/graphify.js`, `$schema`, `name`, `type` (+1017 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **18 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `o()` connect `o` to `_notify`, `components/chart.js`, `rich-editor.js`, `stat/chart.js`, `resolve`, `constructor`, `facet`, `draw`, `_update`, `prop`, `components/select.js`, `create`, `fromObject`, `reduce`, `eq`, `_update`, `child`, `Je`, `nodeAt`, `slice`, `getContext`, `constructor`, `notifications.js`, `support.js`, `pl`, `updateElements`, `_each`, `i`, `buildTicks`, `l`, `parse`, `t`, `A`, `u`, `W`, `lineAt`, `ar`, `findIndex`, `get`, `h`, `draw`, `_resolveAnimations`, `dl`, `fn`, `create`, `find`, `nr`, `toString`, `xt`, `constructor`, `fo`, `file-upload.js`, `tables.js`, `t`, `E`, `S`, `fn`, `Si`, `Xt`, `r`, `cl`?**
  _High betweenness centrality (0.055) - this node is a cross-community bridge._
- **Why does `l()` connect `l` to `of`, `code-editor.js`, `rich-editor.js`, `_notify`, `resolve`, `constructor`, `facet`, `draw`, `markdown-editor.js`, `_update`, `prop`, `components/select.js`, `components/chart.js`, `create`, `fromObject`, `reduce`, `stat/chart.js`, `eq`, `_update`, `child`, `Je`, `slice`, `getContext`, `ne`, `constructor`, `te`, `pl`, `updateElements`, `i`, `buildTicks`, `V`, `parse`, `parse`, `t`, `u`, `lineAt`, `W`, `ar`, `findIndex`, `o`, `h`, `draw`, `dl`, `echo.js`, `create`, `xt`, `toString`, `constructor`, `file-upload.js`, `tables.js`, `E`, `S`, `.slice`, `Si`, `Xt`, `cl`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Why does `scrapeGooglePlaces()` connect `src/index.js` to `db/index.js`, `parse`, `photoWorker.js`, `exporters/index.js`, `server/index.js`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 186 inferred relationships involving `o()` (e.g. with `Be()` and `i()`) actually correct?**
  _`o()` has 186 INFERRED edges - model-reasoned connections that need verification._
- **Are the 153 inferred relationships involving `t()` (e.g. with `add()` and `addCompletions()`) actually correct?**
  _`t()` has 153 INFERRED edges - model-reasoned connections that need verification._
- **Are the 144 inferred relationships involving `i()` (e.g. with `add()` and `addElement()`) actually correct?**
  _`i()` has 144 INFERRED edges - model-reasoned connections that need verification._
- **Are the 17 inferred relationships involving `constructor()` (e.g. with `gQ()` and `o()`) actually correct?**
  _`constructor()` has 17 INFERRED edges - model-reasoned connections that need verification._