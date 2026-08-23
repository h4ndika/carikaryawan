# Graph Report - disekiar2  (2026-08-22)

## Corpus Check
- 206 files · ~142,682 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 6630 nodes · 18736 edges · 247 communities (231 shown, 16 thin omitted)
- Extraction: 88% EXTRACTED · 12% INFERRED · 0% AMBIGUOUS · INFERRED: 2217 edges (avg confidence: 0.71)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `58e76530`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- code-editor.js
- components/chart.js
- rich-editor.js
- stat/chart.js
- .slice
- constructor
- facet
- draw
- markdown-editor.js
- _update
- W
- components/select.js
- columns/select.js
- create
- fromObject
- slice
- date-time-picker.js
- resolve
- _update
- invert
- o
- .forEach
- create
- reduce
- getContext
- y
- dx
- by
- notifications.js
- support.js
- te
- S
- updateElements
- slider.js
- isHorizontal
- fd
- sliceDoc
- hd
- l
- vd
- addElementByRule
- City
- get
- ne
- A
- u
- lineAt
- E
- inRange
- constructor
- constructor
- Filament\Schemas\Schema
- ERD.md
- Cn
- getDatasetMeta
- Im
- MODULES.md
- dl
- public/app.js
- echo.js
- Ye
- 00-VISION.md
- i
- match
- _calculateBarIndexPixels
- User
- 05-CODING-STANDARD.md
- xt
- _each
- Filament\Resources\Pages\CreateRecord
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
- Filament\Resources\Pages\ListRecords
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
- search.blade.php
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
- Wi
- fn
- fn
- Google Maps Data Extractor v2 (Node.js & Apify Edition)
- tasks/README.md
- 01-ROADMAP.md
- Cloud-Native Ready Philosophy
- closeDropdown
- closeDropdown
- FE‑001 — Public Frontend Foundation
- renderOptions
- renderOptions
- ar
- Phase 1 — Core CMS
- i
- Core Content
- src/index.js
- Discovery Features
- No
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

## Communities (247 total, 16 thin omitted)

### Community 0 - "code-editor.js"
Cohesion: 0.01
Nodes (126): Ac(), addCompletion(), addCompletions(), addNamespace(), addNamespaceObject(), attrs(), bi(), Blockquote() (+118 more)

### Community 1 - "components/chart.js"
Cohesion: 0.01
Nodes (129): Il(), abutsStart(), addControllers(), addPlugins(), addScales(), ah(), Be(), beforeDraw() (+121 more)

### Community 2 - "rich-editor.js"
Cohesion: 0.01
Nodes (156): aa(), addHackNode(), addNode(), addNodeMark(), addTextblockHacks(), applyAspectRatio(), applyConstraints(), atEnd() (+148 more)

### Community 3 - "stat/chart.js"
Cohesion: 0.03
Nodes (80): themeClasses(), applyStack(), ba(), be(), beforeDatasetsDraw(), beforeDraw(), Bn(), color() (+72 more)

### Community 4 - ".slice"
Cohesion: 0.05
Nodes (116): Ac(), addCommands(), ag(), allowedMarks(), allowsMarks(), apply(), applyInner(), applyTransaction() (+108 more)

### Community 5 - "constructor"
Cohesion: 0.03
Nodes (127): add(), addChunk(), addEventListener(), addInfoPane(), addInner(), addWindowListeners(), adjust(), al() (+119 more)

### Community 6 - "facet"
Cohesion: 0.04
Nodes (79): accept(), active(), baseTheme(), blur(), bu(), build(), changeByRange(), changes() (+71 more)

### Community 7 - "draw"
Cohesion: 0.04
Nodes (111): acquireContext(), adjustHitBoxes(), Ao(), aspectRatio(), B(), bh(), buildTicks(), calculateLabelRotation() (+103 more)

### Community 8 - "markdown-editor.js"
Cohesion: 0.05
Nodes (95): node(), m(), t(), _a(), ad(), af(), al(), ar() (+87 more)

### Community 9 - "_update"
Cohesion: 0.03
Nodes (108): $a(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterDatasetsUpdate(), afterDraw(), afterFit(), afterSetDimensions() (+100 more)

### Community 10 - "W"
Cohesion: 0.04
Nodes (94): AQ(), atLastNode(), au(), b1(), child(), childAfter(), childBefore(), continue() (+86 more)

### Community 11 - "components/select.js"
Cohesion: 0.08
Nodes (31): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), applyDisabledState(), bn(), Bt(), createBadgeElement(), createRemoveButton() (+23 more)

### Community 12 - "columns/select.js"
Cohesion: 0.09
Nodes (26): addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), applyDisabledState(), createBadgeElement(), createRemoveButton(), disable(), en() (+18 more)

### Community 13 - "create"
Cohesion: 0.05
Nodes (63): Cl(), clone(), create(), Ct(), dc(), Dl(), dtFormatter(), Ea() (+55 more)

### Community 14 - "fromObject"
Cohesion: 0.04
Nodes (104): Oe(), ac(), ae(), after(), ag(), Al(), Am(), before() (+96 more)

### Community 15 - "slice"
Cohesion: 0.03
Nodes (96): addChild(), addGaps(), addLeafElement(), addNode(), advance(), ATXHeading(), balance(), blank() (+88 more)

### Community 16 - "date-time-picker.js"
Cohesion: 0.38
Nodes (3): e(), r(), s()

### Community 17 - "resolve"
Cohesion: 0.07
Nodes (100): addKeyboardShortcuts(), after(), al(), before(), Bs(), canReplaceWith(), cellsInRect(), cg() (+92 more)

### Community 18 - "_update"
Cohesion: 0.05
Nodes (63): aa(), addElements(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion() (+55 more)

### Community 19 - "invert"
Cohesion: 0.11
Nodes (28): addMaps(), addStep(), addTransform(), appendMap(), appendMapping(), appendMappingInverted(), aw(), compress() (+20 more)

### Community 20 - "o"
Cohesion: 0.03
Nodes (132): o(), _0(), addGlobalAttributes(), addInputRules(), addMark(), addStoredMark(), Ah(), ak() (+124 more)

### Community 21 - ".forEach"
Cohesion: 0.04
Nodes (75): accepts(), Ad(), addAttributes(), addPasteRules(), addProseMirrorPlugins(), af(), an(), $b() (+67 more)

### Community 22 - "create"
Cohesion: 0.05
Nodes (53): addActive(), addChanges(), addSelection(), Ah(), applyTransaction(), Ar(), as(), asSingle() (+45 more)

### Community 23 - "reduce"
Cohesion: 0.06
Nodes (61): addActions(), advanceFully(), advanceStack(), allActions(), apply(), b0(), canShift(), checkAsyncSchedule() (+53 more)

### Community 24 - "getContext"
Cohesion: 0.09
Nodes (38): acquireContext(), Ae(), bi(), Ca(), clear(), _computeGridLineItems(), _computeLabelArea(), dr() (+30 more)

### Community 25 - "y"
Cohesion: 0.14
Nodes (55): An(), at(), cd(), Cr(), Ct(), de(), df(), dr() (+47 more)

### Community 26 - "dx"
Cohesion: 0.12
Nodes (24): Ei(), Aa(), ai(), Bi(), cf(), Gr(), Jc(), ka() (+16 more)

### Community 27 - "by"
Cohesion: 0.03
Nodes (100): au(), ay(), Bd(), Bg(), bt(), by(), cl(), connectSelection() (+92 more)

### Community 28 - "notifications.js"
Cohesion: 0.06
Nodes (31): actions(), button(), c(), close(), configureAnimations(), configureTransitions(), constructor(), danger() (+23 more)

### Community 29 - "support.js"
Cohesion: 0.05
Nodes (59): acquireScrollLock(), ai(), Ao(), apply(), as(), At(), close(), closeQuietly() (+51 more)

### Community 30 - "te"
Cohesion: 0.05
Nodes (8): Pr(), Bn(), br(), ji(), Ri(), te(), Vi(), Xc()

### Community 31 - "S"
Cohesion: 0.05
Nodes (61): afterAutoSkip(), Bt(), buildLookupTable(), cl(), Cn(), da(), determineDataLimits(), diff() (+53 more)

### Community 32 - "updateElements"
Cohesion: 0.05
Nodes (69): at(), Ba(), Bf(), _calculateBarValuePixels(), calculateCircumference(), _circumference(), _computeAngle(), countVisibleElements() (+61 more)

### Community 33 - "slider.js"
Cohesion: 0.09
Nodes (36): Ae(), ar(), Be(), Bt(), De(), _e(), Ee(), er() (+28 more)

### Community 34 - "isHorizontal"
Cohesion: 0.07
Nodes (49): Ao(), bl(), buildTicks(), calculateLabelRotation(), _calculatePadding(), ci(), _computeLabelItems(), _computeLabelSizes() (+41 more)

### Community 35 - "fd"
Cohesion: 0.04
Nodes (85): activateHover(), addRange(), baseDirAt(), bidiIn(), bidiSpans(), bidiSpansAt(), bP(), cd() (+77 more)

### Community 36 - "sliceDoc"
Cohesion: 0.12
Nodes (24): aO(), charCategorizer(), cS(), Fc(), getCursor(), getDeco(), gT(), highlight() (+16 more)

### Community 37 - "hd"
Cohesion: 0.08
Nodes (28): aa(), ad(), afterAutoSkip(), bd(), br(), buildLookupTable(), cd(), Gd() (+20 more)

### Community 38 - "l"
Cohesion: 0.09
Nodes (75): hd(), wo(), yf(), yS(), d(), g(), p(), b() (+67 more)

### Community 39 - "vd"
Cohesion: 0.11
Nodes (41): B(), me(), Nh(), Ae(), as(), ci(), da(), er() (+33 more)

### Community 40 - "addElementByRule"
Cohesion: 0.14
Nodes (25): addAll(), addDOM(), addElement(), addElementByRule(), addTextNode(), addToSet(), allowsMarkType(), closeExtra() (+17 more)

### Community 41 - "City"
Cohesion: 0.13
Nodes (9): PostForm, Home, PostList, PostShow, Search, Category, City, Livewire\Component (+1 more)

### Community 42 - "get"
Cohesion: 0.08
Nodes (41): addBlockWidget(), addBreak(), addComposition(), addDelimiter(), addInlineWidget(), addLine(), addLineStart(), addLineStartIfNotCovered() (+33 more)

### Community 43 - "ne"
Cohesion: 0.06
Nodes (58): xe(), Ac(), ao(), bl(), Cc(), ce(), cl(), Cn() (+50 more)

### Community 44 - "A"
Cohesion: 0.10
Nodes (25): A(), As(), buildOrUpdateScales(), cr(), ensureScalesHaveIDs(), fn(), getScale(), gl() (+17 more)

### Community 45 - "u"
Cohesion: 0.20
Nodes (31): ar(), c(), d(), di(), g(), Hi(), Hn(), I() (+23 more)

### Community 46 - "lineAt"
Cohesion: 0.06
Nodes (50): addBlock(), addElement(), addLineDeco(), applyChanges(), balanced(), baseIndent(), baseIndentFor(), blankContent() (+42 more)

### Community 47 - "E"
Cohesion: 0.05
Nodes (69): add(), addBox(), addElements(), As(), beforeUpdate(), bi(), bo(), bs() (+61 more)

### Community 48 - "inRange"
Cohesion: 0.04
Nodes (64): active(), _animateOptions(), applyStack(), average(), beforeDatasetsDraw(), beforeLayout(), bu(), cf() (+56 more)

### Community 49 - "constructor"
Cohesion: 0.04
Nodes (68): af(), alpha(), apply(), Bc(), bg(), chartOptionScopes(), co(), constructor() (+60 more)

### Community 50 - "constructor"
Cohesion: 0.04
Nodes (66): add(), addExtensions(), addNodeView(), addOptions(), applyInitialSize(), bl(), Bo(), bw() (+58 more)

### Community 51 - "Filament\Schemas\Schema"
Cohesion: 0.14
Nodes (12): CategoryResource, CategoryForm, CityResource, CityForm, PostResource, ProvinceResource, ProvinceForm, BackedEnum (+4 more)

### Community 52 - "ERD.md"
Cohesion: 0.04
Nodes (47): 10. Relationship Summary, 11. Standard Columns, 12. Naming Convention, 13. Future Expansion, 1. Purpose, 2. Design Principles, 3. Conceptual ERD, 4. Master Tables (+39 more)

### Community 53 - "Cn"
Cohesion: 0.14
Nodes (36): _a(), Bi(), br(), Bt(), ca(), Cn(), ct(), Dn() (+28 more)

### Community 54 - "getDatasetMeta"
Cohesion: 0.11
Nodes (25): afterDatasetsUpdate(), buildOrUpdateControllers(), _destroyDatasetMeta(), getController(), getDatasetMeta(), getElement(), _handleEvent(), hide() (+17 more)

### Community 55 - "Im"
Cohesion: 0.20
Nodes (14): Bm(), defaultType(), eat(), err(), hasRequiredAttrs(), Im(), isInGroup(), Lm() (+6 more)

### Community 56 - "MODULES.md"
Cohesion: 0.04
Nodes (44): 10. System Modules, 11. Module Ownership, 12. Folder Structure, 13. Communication Rules, 14. Event Driven, 15. Future Modules, 16. Development Rules, 17. Module Lifecycle (+36 more)

### Community 57 - "dl"
Cohesion: 0.07
Nodes (39): ai(), ar(), bs(), describe(), dl(), ei(), er(), first() (+31 more)

### Community 58 - "public/app.js"
Cohesion: 0.14
Nodes (44): api(), appendLog(), closeModal(), connectSSE(), esc(), formatBytes(), formatDate(), formatNum() (+36 more)

### Community 59 - "echo.js"
Cohesion: 0.09
Nodes (16): a(), ar(), at(), cr(), d(), f(), H(), ji() (+8 more)

### Community 60 - "Ye"
Cohesion: 0.11
Nodes (41): at(), bk(), bp(), Dk(), dp(), Ek(), fail(), Fk() (+33 more)

### Community 61 - "00-VISION.md"
Cohesion: 0.05
Nodes (43): 1. Mempermudah masyarakat menemukan informasi lokal, 2. Menjadi sumber informasi yang terpercaya, 3. Menghubungkan masyarakat dengan bisnis lokal, 4. Mengembangkan ekosistem informasi lokal, About DISEKITAR, Administrator, AI Ready, API Ready (+35 more)

### Community 62 - "i"
Cohesion: 0.05
Nodes (67): a$(), aa(), activeForPoint(), after(), AX(), before(), Bg(), boundChange() (+59 more)

### Community 63 - "match"
Cohesion: 0.07
Nodes (42): addToSet(), bd(), between(), Bh(), c0(), childString(), clearDelayedAndroidKey(), d0() (+34 more)

### Community 64 - "_calculateBarIndexPixels"
Cohesion: 0.14
Nodes (18): addEventListener(), au(), bindResponsiveEvents(), _calculateBarIndexPixels(), fu(), _getAxis(), _getAxisCount(), getFirstScaleIdForIndexAxis() (+10 more)

### Community 65 - "User"
Cohesion: 0.09
Nodes (15): User, UserFactory, DatabaseSeeder, Facebook\WebDriver\Remote\RemoteWebDriver, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Seeder, Illuminate\Foundation\Auth\User (+7 more)

### Community 66 - "05-CODING-STANDARD.md"
Cohesion: 0.05
Nodes (42): Action Rules, AI Coding Rules, API Response, Blade Standard, Class, Comments, Configuration, Constant (+34 more)

### Community 67 - "xt"
Cohesion: 0.13
Nodes (27): Ra(), ae(), B(), cr(), de(), dt(), Ee(), fr() (+19 more)

### Community 68 - "_each"
Cohesion: 0.12
Nodes (21): addControllers(), addPlugins(), addScales(), br(), cancel(), _createDescriptors(), _descriptors(), _each() (+13 more)

### Community 69 - "Filament\Resources\Pages\CreateRecord"
Cohesion: 0.11
Nodes (9): CreateCategory, EditCategory, CreateCity, EditCity, CreatePost, CreateProvince, EditProvince, Filament\Resources\Pages\CreateRecord (+1 more)

### Community 70 - "home.blade.php"
Cohesion: 0.50
Nodes (3): partials.article-feed, partials.category-tabs, partials.editor-picks

### Community 71 - "fn"
Cohesion: 0.19
Nodes (20): aa(), ba(), da(), ei(), Fi(), fn(), gr(), jt() (+12 more)

### Community 72 - "constructor"
Cohesion: 0.07
Nodes (38): Ot(), alpha(), apply(), bo(), chartOptionScopes(), co(), constructor(), es() (+30 more)

### Community 73 - "file-upload.js"
Cohesion: 0.05
Nodes (54): hc(), Bp(), c(), ca(), clickPercent(), cm(), constructor(), Cp() (+46 more)

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

### Community 79 - "addEventListener"
Cohesion: 0.21
Nodes (12): addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), ga(), Hn(), isAttached(), Ja() (+4 more)

### Community 80 - "add"
Cohesion: 0.08
Nodes (35): active(), add(), _animateOptions(), _cachedScopes(), _createAnimations(), createResolver(), datasetElementScopeKeys(), get() (+27 more)

### Community 81 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.12
Nodes (6): EditPost, PostDetail, PostImage, Province, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes

### Community 82 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.21
Nodes (5): ListCategories, ListCities, ListPosts, ListProvinces, Filament\Resources\Pages\ListRecords

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
Cohesion: 0.12
Nodes (38): clearPhotoQueue(), deleteAccount(), deleteTask(), enqueuePlacesForPhotoEnrichment(), getAllPlaces(), getCategories(), getPendingPhotoQueue(), getPhotoQueueList() (+30 more)

### Community 91 - "PERMISSION_MODEL.md"
Cohesion: 0.05
Nodes (36): 10. Middleware, 11. Laravel Policy, 12. UI Permission, 13. API Permission, 14. Audit Trail, 15. Future Multi-Tenant, 16. Database Structure, 17. Best Practices (+28 more)

### Community 92 - "Si"
Cohesion: 0.15
Nodes (38): ae(), At(), bi(), ci(), cn(), ct(), de(), di() (+30 more)

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
Cohesion: 0.22
Nodes (10): Be(), ei(), ii(), le(), ni(), oi(), r(), ri() (+2 more)

### Community 97 - "ls"
Cohesion: 0.24
Nodes (11): cs(), Io(), kn(), ls(), os(), si(), us(), ws() (+3 more)

### Community 98 - "Post"
Cohesion: 0.11
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
Cohesion: 0.40
Nodes (4): closeLightbox, openLightbox(@js($image->image_url)), openLightbox(@js($post->thumbnail)), partials.post-card

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

### Community 133 - "SEO_STRATEGY.md"
Cohesion: 0.06
Nodes (30): 10. Internal Linking, 11. Sitemap, 12. Robots, 13. Canonical, 14. Open Graph, 15. Twitter Card, 16. Image SEO, 17. Performance (+22 more)

### Community 165 - "db/index.js"
Cohesion: 0.12
Nodes (30): ACCOUNT_COLUMNS, ACCOUNT_STATUSES, accountFromId(), createTask(), DB_DIR, DB_PATH, FINAL_STATUSES, getAccountByEmail() (+22 more)

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
Cohesion: 0.10
Nodes (28): acceptToken(), Ag(), allows(), eh(), g$(), GO(), ih(), kh() (+20 more)

### Community 175 - "exporters/index.js"
Cohesion: 0.14
Nodes (21): RFC-4180, DEFAULT_COLUMNS, escapeCsvField(), exportToCsv(), formatField(), COLUMNS, exportToExcel(), formatField() (+13 more)

### Community 176 - "07-FEATURES.md"
Cohesion: 0.08
Nodes (25): AI Coding Guidelines, API Features, Definition of Done, DISEKITAR Features Specification, Event Platform, Feature Dependencies, Feature Principles, Feature Status (+17 more)

### Community 177 - "PROMPT_REFACTOR.md"
Cohesion: 0.08
Nodes (24): AI Must NOT, Benefits, DISEKITAR AI Refactoring Prompt, Documentation Updated, Files Modified, Purpose, Refactoring Performed, Refactoring Principles (+16 more)

### Community 178 - "GoogleMapsImportService"
Cohesion: 0.14
Nodes (3): AddressParser, GoogleMapsImportService, self

### Community 179 - "n"
Cohesion: 0.15
Nodes (22): A(), An(), b(), Bt(), D(), F(), ht(), $i() (+14 more)

### Community 180 - "color-picker.js"
Cohesion: 0.11
Nodes (9): constructor(), [g](), style(), update(), [x](), Gl(), lx(), ta() (+1 more)

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
Cohesion: 0.18
Nodes (5): CategoriesTable, CitiesTable, PostsTable, ProvincesTable, Filament\Tables\Table

### Community 185 - "Analisis Project DISEKITAR"
Cohesion: 0.12
Nodes (16): 10. Insight dari Graphify, 11. Ringkasan Eksekutif, 1. Identitas & Visi Produk, 2. Tech Stack, 3. Arsitektur, 4. Domain Model & Database, 5. Routing & Controller, 6. Model & Relasi Eloquent (+8 more)

### Community 186 - "ROLE_MATRIX.md"
Cohesion: 0.12
Nodes (16): AI May Suggest, AI Must NOT, AI Responsibilities, Communication Rules, Decision Authority, DISEKITAR Task Role Matrix, Documentation Rules, Escalation Rules (+8 more)

### Community 187 - "Wi"
Cohesion: 0.17
Nodes (17): Rd(), $a(), closest(), Fx(), $h(), Ha(), ik(), jx() (+9 more)

### Community 188 - "fn"
Cohesion: 0.22
Nodes (17): An(), Ce(), ei(), fn(), Ft(), Ie(), Le(), ni() (+9 more)

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

### Community 194 - "closeDropdown"
Cohesion: 0.30
Nodes (14): closeDropdown(), constructor(), destroy(), focusNextOption(), focusPreviousOption(), getVisibleOptions(), handleDropdownKeydown(), handleSelectButtonKeydown() (+6 more)

### Community 195 - "closeDropdown"
Cohesion: 0.30
Nodes (14): closeDropdown(), constructor(), destroy(), focusNextOption(), focusPreviousOption(), getVisibleOptions(), handleDropdownKeydown(), handleSelectButtonKeydown() (+6 more)

### Community 196 - "FE‑001 — Public Frontend Foundation"
Cohesion: 0.15
Nodes (12): Controller, Docs, FE‑001 — Public Frontend Foundation, Files Changed, Goal, History, Livewire, Notes (+4 more)

### Community 197 - "renderOptions"
Cohesion: 0.37
Nodes (13): createOptionElement(), deferPositionDropdown(), filterOptions(), handleSearch(), hideLoadingState(), openDropdown(), populateLabelRepositoryFromOptions(), positionDropdown() (+5 more)

### Community 198 - "renderOptions"
Cohesion: 0.37
Nodes (13): createOptionElement(), deferPositionDropdown(), filterOptions(), handleSearch(), hideLoadingState(), openDropdown(), populateLabelRepositoryFromOptions(), positionDropdown() (+5 more)

### Community 199 - "ar"
Cohesion: 0.18
Nodes (13): ar(), beforeDatasetDraw(), getMinDaysInFirstWeek(), getMinimumDaysInFirstWeek(), getStartOfWeek(), getWeekendDays(), getWeekendWeekdays(), getWeekSettings() (+5 more)

### Community 200 - "Phase 1 — Core CMS"
Cohesion: 0.17
Nodes (12): Category Module, Features, Features, Features, Features, Features, Gallery Module, Location Module (+4 more)

### Community 201 - "i"
Cohesion: 0.18
Nodes (12): E(), b(), Dt(), Fe(), g(), He(), i(), ir() (+4 more)

### Community 202 - "Core Content"
Cohesion: 0.18
Nodes (11): Category, Core Content, Features, Features, Features, Features, Features, Gallery (+3 more)

### Community 203 - "src/index.js"
Cohesion: 0.27
Nodes (7): getAccounts(), getStats(), program, runInteractiveMenu(), DEFAULT_ACTOR, colors, logger

### Community 204 - "Discovery Features"
Cohesion: 0.22
Nodes (9): Discovery Features, Features, Features, Features, Features, Interactive Map, Nearby, Recommendation (+1 more)

### Community 205 - "No"
Cohesion: 0.36
Nodes (9): addInner(), mapInner(), No(), Nu(), off(), on(), once(), Ru() (+1 more)

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
Cohesion: 0.40
Nodes (4): DISEKITAR — Task Board, Done, Notes, Now / Next

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

## Knowledge Gaps
- **1009 isolated node(s):** `$schema`, `.opencode/plugins/graphify.js`, `$schema`, `name`, `type` (+1004 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `o()` connect `o` to `code-editor.js`, `components/chart.js`, `rich-editor.js`, `stat/chart.js`, `.slice`, `constructor`, `facet`, `draw`, `markdown-editor.js`, `_update`, `W`, `create`, `fromObject`, `slice`, `date-time-picker.js`, `resolve`, `_update`, `.forEach`, `create`, `reduce`, `getContext`, `by`, `notifications.js`, `support.js`, `S`, `updateElements`, `isHorizontal`, `fd`, `sliceDoc`, `hd`, `l`, `get`, `A`, `u`, `E`, `inRange`, `constructor`, `constructor`, `n`, `Cn`, `Im`, `dl`, `fn`, `fn`, `i`, `match`, `_calculateBarIndexPixels`, `_each`, `ar`, `constructor`, `i`, `file-upload.js`, `No`, `t`, `addEventListener`, `add`, `Si`, `Xt`, `r`?**
  _High betweenness centrality (0.063) - this node is a cross-community bridge._
- **Why does `l()` connect `l` to `code-editor.js`, `components/chart.js`, `rich-editor.js`, `stat/chart.js`, `.slice`, `constructor`, `facet`, `draw`, `markdown-editor.js`, `W`, `create`, `fromObject`, `slice`, `resolve`, `_update`, `o`, `.forEach`, `create`, `reduce`, `getContext`, `y`, `dx`, `by`, `S`, `updateElements`, `isHorizontal`, `vd`, `addElementByRule`, `ne`, `lineAt`, `E`, `inRange`, `constructor`, `n`, `Im`, `dl`, `echo.js`, `i`, `match`, `xt`, `fn`, `ar`, `file-upload.js`, `tables.js`, `Si`, `Xt`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Why does `scrapeGooglePlaces()` connect `db/index.js` to `src/index.js`, `exporters/index.js`, `scrapePlacePhotos`, `Im`, `server/index.js`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Are the 186 inferred relationships involving `o()` (e.g. with `Be()` and `i()`) actually correct?**
  _`o()` has 186 INFERRED edges - model-reasoned connections that need verification._
- **Are the 153 inferred relationships involving `t()` (e.g. with `add()` and `addCompletions()`) actually correct?**
  _`t()` has 153 INFERRED edges - model-reasoned connections that need verification._
- **Are the 144 inferred relationships involving `i()` (e.g. with `add()` and `addElement()`) actually correct?**
  _`i()` has 144 INFERRED edges - model-reasoned connections that need verification._
- **Are the 17 inferred relationships involving `constructor()` (e.g. with `gQ()` and `o()`) actually correct?**
  _`constructor()` has 17 INFERRED edges - model-reasoned connections that need verification._