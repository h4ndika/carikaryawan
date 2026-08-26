# Graph Report - .  (2026-08-03)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 5073 nodes · 16116 edges · 163 communities (157 shown, 6 thin omitted)
- Extraction: 89% EXTRACTED · 11% INFERRED · 0% AMBIGUOUS · INFERRED: 1820 edges (avg confidence: 0.7)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5cdbffcb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- code-editor.js
- components/chart.js
- rich-editor.js
- stat/chart.js
- resolve
- update
- constructor
- draw
- markdown-editor.js
- _update
- get
- components/select.js
- columns/select.js
- constructor
- fromObject
- slice
- t
- Ti
- _update
- .slice
- .forEach
- e
- create
- advance
- draw
- y
- Fn
- pa
- notifications.js
- support.js
- te
- getPixelForValue
- I
- slider.js
- isHorizontal
- updateSelection
- of
- buildTicks
- h
- vd
- create
- Post
- sync
- j0
- D
- m
- lineAt
- add
- le
- getContext
- getDatasetMeta
- Filament\Tables\Table
- _notify
- _a
- getDatasetMeta
- Kn
- L
- ph
- getSortedVisibleDatasetMetas
- echo.js
- serializeFragment
- wc
- eq
- match
- addEventListener
- User
- qt
- _each
- Filament\Resources\Pages\CreateRecord
- Filament\Schemas\Schema
- fn
- T
- file-upload.js
- tables.js
- filament/app.js
- devDependencies
- init
- t
- addEventListener
- add
- Illuminate\Database\Eloquent\Model
- Filament\Resources\Pages\ListRecords
- composer.json
- getChild
- require-dev
- scripts
- HomePage
- le
- require
- vl
- lt
- Illuminate\Console\Command
- yl
- config
- e
- d
- SitemapController.php
- actions/actions.js
- a
- AppServiceProvider
- RahasiaPanelProvider.php
- post-show.blade.php
- psr-4
- c
- clickPercent
- schemas.js
- post-autoload-dump
- post-create-project-cmd
- extra
- graphify.js
- Dm
- components/actions.js
- search.blade.php
- post-list.blade.php

## God Nodes (most connected - your core abstractions)
1. `update()` - 137 edges
2. `t()` - 137 edges
3. `i()` - 133 edges
4. `constructor()` - 129 edges
5. `r()` - 121 edges
6. `resolve()` - 93 edges
7. `_update()` - 88 edges
8. `_update()` - 86 edges
9. `y()` - 85 edges
10. `te()` - 81 edges

## Surprising Connections (you probably didn't know these)
- `getExtension()` --indirect_call--> `Ht()`  [INFERRED]
  public/js/filament/forms/components/file-upload.js → public/js/filament/filament/echo.js
- `te()` --indirect_call--> `Pr()`  [INFERRED]
  public/js/filament/forms/components/markdown-editor.js → public/js/filament/filament/echo.js
- `range()` --indirect_call--> `r()`  [INFERRED]
  public/js/filament/forms/components/code-editor.js → public/js/filament/forms/components/textarea.js
- `node()` --indirect_call--> `t()`  [INFERRED]
  public/js/filament/forms/components/code-editor.js → public/js/filament/forms/components/date-time-picker.js
- `wd()` --indirect_call--> `t()`  [INFERRED]
  public/js/filament/forms/components/code-editor.js → public/js/filament/forms/components/date-time-picker.js

## Import Cycles
- None detected.

## Communities (163 total, 6 thin omitted)

### Community 0 - "code-editor.js"
Cohesion: 0.01
Nodes (114): addSelection(), after(), am(), as(), attrs(), b1(), balance(), before() (+106 more)

### Community 1 - "components/chart.js"
Cohesion: 0.01
Nodes (104): acquireContext(), addControllers(), addPlugins(), addScales(), afterDraw(), An(), ba(), Br() (+96 more)

### Community 2 - "rich-editor.js"
Cohesion: 0.01
Nodes (243): Ab(), add(), addExtensions(), addHackNode(), addNode(), addNodeMark(), addOptions(), addTextblockHacks() (+235 more)

### Community 3 - "stat/chart.js"
Cohesion: 0.03
Nodes (84): themeClasses(), Wa(), _a(), aa(), alpha(), as(), ba(), bl() (+76 more)

### Community 4 - "resolve"
Cohesion: 0.06
Nodes (137): Ac(), addAttributes(), Ae(), after(), ag(), ap(), at(), ba() (+129 more)

### Community 5 - "update"
Cohesion: 0.03
Nodes (118): accept(), active(), addChunk(), addInfoPane(), addInner(), addLineDeco(), addToSet(), adjust() (+110 more)

### Community 6 - "constructor"
Cohesion: 0.04
Nodes (87): Ac(), add(), addEventListener(), addWindowListeners(), af(), applyEdits(), B(), blank() (+79 more)

### Community 7 - "draw"
Cohesion: 0.03
Nodes (125): p(), ac(), adjustHitBoxes(), ae(), af(), As(), at(), bd() (+117 more)

### Community 8 - "markdown-editor.js"
Cohesion: 0.05
Nodes (98): Aa(), ad(), af(), ai(), al(), ao(), ar(), Ba() (+90 more)

### Community 9 - "_update"
Cohesion: 0.04
Nodes (102): aa(), addBox(), addElements(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions() (+94 more)

### Community 10 - "get"
Cohesion: 0.04
Nodes (105): an(), Ap(), atLastNode(), bm(), Bs(), bu(), child(), childAfter() (+97 more)

### Community 11 - "components/select.js"
Cohesion: 0.06
Nodes (86): [g](), style(), update(), [x](), addBadgesForSelectedOptions(), addSingleSelectionDisplay(), applyDisabledState(), At() (+78 more)

### Community 12 - "columns/select.js"
Cohesion: 0.07
Nodes (92): A(), addBadgesForSelectedOptions(), addSingleBadge(), addSingleSelectionDisplay(), applyDisabledState(), At(), B(), be() (+84 more)

### Community 13 - "constructor"
Cohesion: 0.03
Nodes (86): Og(), Am(), apply(), bc(), clone(), constructor(), create(), dc() (+78 more)

### Community 14 - "fromObject"
Cohesion: 0.04
Nodes (87): _g(), pg(), abutsStart(), after(), before(), bg(), bs(), cc() (+79 more)

### Community 15 - "slice"
Cohesion: 0.04
Nodes (77): _a(), addActive(), addBlockWidget(), addChild(), addDelimiter(), addElement(), addGaps(), addLeafElement() (+69 more)

### Community 16 - "t"
Cohesion: 0.06
Nodes (80): compare(), domBoundsAround(), Ef(), eT(), ff(), from(), fromJSON(), fS() (+72 more)

### Community 17 - "Ti"
Cohesion: 0.18
Nodes (14): bf(), bidiSpans(), coordsAtPos(), elementAtHeight(), forRange(), lineBlockAtHeight(), PX(), readMeasured() (+6 more)

### Community 18 - "_update"
Cohesion: 0.06
Nodes (62): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterDraw(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+54 more)

### Community 19 - ".slice"
Cohesion: 0.05
Nodes (65): Kt(), accepts(), addInner(), addMaps(), addStep(), addTransform(), al(), appendMap() (+57 more)

### Community 20 - ".forEach"
Cohesion: 0.04
Nodes (107): $a(), addInputRules(), addKeyboardShortcuts(), addMark(), addPasteRules(), addProseMirrorPlugins(), addStoredMark(), addToSet() (+99 more)

### Community 21 - "e"
Cohesion: 0.05
Nodes (95): addCommands(), addNodeView(), _b(), Bb(), Bf(), Bl(), cellsInRect(), chain() (+87 more)

### Community 22 - "create"
Cohesion: 0.05
Nodes (59): addChanges(), addMapping(), applyTransaction(), asSingle(), At(), be(), between(), changeByRange() (+51 more)

### Community 23 - "advance"
Cohesion: 0.06
Nodes (64): acceptToken(), addActions(), advance(), advanceFully(), advanceStack(), allActions(), allows(), Bw() (+56 more)

### Community 24 - "draw"
Cohesion: 0.08
Nodes (46): adjustHitBoxes(), At(), bi(), bo(), _computeLabelArea(), _computeTitleHeight(), Ct(), draw() (+38 more)

### Community 25 - "y"
Cohesion: 0.15
Nodes (59): Ht(), _getTestState(), at(), Be(), cd(), Cr(), Ct(), de() (+51 more)

### Community 26 - "Fn"
Cohesion: 0.07
Nodes (63): tooltip(), An(), bh(), br(), bx(), ch(), ci(), dx() (+55 more)

### Community 27 - "pa"
Cohesion: 0.16
Nodes (19): setOrder(), aa(), append(), ca(), Cr(), findDiffEnd(), findDiffStart(), fromArray() (+11 more)

### Community 28 - "notifications.js"
Cohesion: 0.05
Nodes (28): actions(), button(), close(), configureAnimations(), configureTransitions(), constructor(), danger(), dispatch() (+20 more)

### Community 29 - "support.js"
Cohesion: 0.07
Nodes (38): apply(), as(), At(), ba(), bo(), close(), closeQuietly(), Cr() (+30 more)

### Community 30 - "te"
Cohesion: 0.05
Nodes (8): Rd(), Bi(), Bn(), ji(), Ri(), te(), Vi(), Xc()

### Community 31 - "getPixelForValue"
Cohesion: 0.05
Nodes (59): defaultZone(), afterAutoSkip(), Ar(), bc(), beforeLayout(), buildLookupTable(), _calculateBarValuePixels(), cc() (+51 more)

### Community 32 - "I"
Cohesion: 0.04
Nodes (84): A(), applyStack(), aspectRatio(), beforeLayout(), _calculateBarIndexPixels(), _calculateBarValuePixels(), cf(), Cl() (+76 more)

### Community 33 - "slider.js"
Cohesion: 0.09
Nodes (39): Ae(), ar(), Be(), Bt(), De(), Ee(), er(), et() (+31 more)

### Community 34 - "isHorizontal"
Cohesion: 0.07
Nodes (46): Ae(), an(), buildTicks(), calculateCircumference(), calculateLabelRotation(), _calculatePadding(), _circumference(), _computeAngle() (+38 more)

### Community 35 - "updateSelection"
Cohesion: 0.05
Nodes (52): addRange(), clear(), coordsAt(), covers(), Dc(), domAtPos(), enforceCursorAssoc(), flush() (+44 more)

### Community 36 - "of"
Cohesion: 0.06
Nodes (40): ag(), baseTheme(), bQ(), bv(), charCategorizer(), compositionend(), CQ(), dd() (+32 more)

### Community 37 - "buildTicks"
Cohesion: 0.05
Nodes (50): afterAutoSkip(), Al(), buildLookupTable(), buildTicks(), clear(), $e(), eu(), Fe() (+42 more)

### Community 38 - "h"
Cohesion: 0.20
Nodes (34): QP(), v(), h(), b(), $c(), D(), E(), Ea() (+26 more)

### Community 39 - "vd"
Cohesion: 0.11
Nodes (40): f(), Ae(), An(), as(), da(), fa(), ff(), ga() (+32 more)

### Community 40 - "create"
Cohesion: 0.07
Nodes (49): addAll(), addDOM(), addElement(), addElementByRule(), addTextNode(), allowedMarks(), allowsMarks(), allowsMarkType() (+41 more)

### Community 41 - "Post"
Cohesion: 0.10
Nodes (9): Home, PostList, PostShow, Search, Category, City, Post, Livewire\Component (+1 more)

### Community 42 - "sync"
Cohesion: 0.10
Nodes (30): become(), canReuseDOM(), childCursor(), childPos(), compositionView(), createDOM(), df(), findPos() (+22 more)

### Community 43 - "j0"
Cohesion: 0.10
Nodes (38): Ac(), bl(), ce(), Dc(), df(), Dn(), Do(), el() (+30 more)

### Community 44 - "D"
Cohesion: 0.14
Nodes (24): ch(), D(), Ea(), endOf(), Fn(), format(), formats(), getLabelAndValue() (+16 more)

### Community 45 - "m"
Cohesion: 0.12
Nodes (38): Dt(), ai(), ar(), ce(), co(), E(), es(), f() (+30 more)

### Community 46 - "lineAt"
Cohesion: 0.08
Nodes (37): addBlock(), applyChanges(), balanced(), baseIndent(), baseIndentFor(), blankContent(), blockAt(), Cg() (+29 more)

### Community 47 - "add"
Cohesion: 0.05
Nodes (54): add(), ag(), ah(), alpha(), Ar(), bh(), Bt(), ca() (+46 more)

### Community 48 - "le"
Cohesion: 0.05
Nodes (56): Pa(), ao(), au(), average(), beforeDatasetDraw(), beforeDatasetsDraw(), beforeDraw(), bl() (+48 more)

### Community 49 - "getContext"
Cohesion: 0.07
Nodes (46): acquireContext(), _calculateBarIndexPixels(), countVisibleElements(), _createItems(), datasetAnimationScopeKeys(), datasetElementScopeKeys(), datasetScopeKeys(), Dr() (+38 more)

### Community 50 - "getDatasetMeta"
Cohesion: 0.13
Nodes (23): afterDatasetsUpdate(), generateLabels(), getDatasetMeta(), getDataVisibility(), _getLegendItemAt(), getMaxBorderWidth(), getStyle(), _handleEvent() (+15 more)

### Community 51 - "Filament\Tables\Table"
Cohesion: 0.12
Nodes (13): CategoryResource, CategoriesTable, CityResource, CitiesTable, PostResource, PostsTable, ProvinceResource, ProvincesTable (+5 more)

### Community 52 - "_notify"
Cohesion: 0.15
Nodes (17): active(), _animateOptions(), bu(), cancel(), _createAnimations(), _createDescriptors(), _descriptors(), getPadding() (+9 more)

### Community 53 - "_a"
Cohesion: 0.17
Nodes (33): _a(), aa(), br(), Bt(), Ca(), ct(), Da(), Dn() (+25 more)

### Community 54 - "getDatasetMeta"
Cohesion: 0.09
Nodes (33): Rg(), afterDatasetsUpdate(), buildOrUpdateControllers(), _destroyDatasetMeta(), generateLabels(), getController(), getDatasetMeta(), getDataVisibility() (+25 more)

### Community 55 - "Kn"
Cohesion: 0.14
Nodes (14): ep(), focus(), Kn(), lm(), matchesContext(), matchStyle(), matchTag(), op() (+6 more)

### Community 56 - "L"
Cohesion: 0.10
Nodes (29): Dl(), ef(), fd(), Fi(), first(), Fo(), getBasePosition(), getBaseValue() (+21 more)

### Community 57 - "ph"
Cohesion: 0.06
Nodes (47): average(), bh(), Bt(), cr(), dataset(), En(), Ft(), getCenterPoint() (+39 more)

### Community 58 - "getSortedVisibleDatasetMetas"
Cohesion: 0.20
Nodes (12): Ao(), applyStack(), beforeDatasetDraw(), beforeDatasetsDraw(), beforeDraw(), co(), _getSortedDatasetMetas(), getSortedVisibleDatasetMetas() (+4 more)

### Community 59 - "echo.js"
Cohesion: 0.09
Nodes (15): ar(), b(), cr(), g(), ir(), Me(), nr(), P() (+7 more)

### Community 60 - "serializeFragment"
Cohesion: 0.33
Nodes (9): np(), renderSpec(), serializeFragment(), serializeMark(), serializeNode(), serializeNodeInner(), Sr(), tp() (+1 more)

### Community 61 - "wc"
Cohesion: 0.19
Nodes (13): ac(), cs(), Es(), getBaseValue(), lo(), ls(), nc(), path() (+5 more)

### Community 62 - "eq"
Cohesion: 0.10
Nodes (26): activeForPoint(), Ba(), boundChange(), commit(), comparePoint(), compareRange(), Ds(), ei() (+18 more)

### Community 63 - "match"
Cohesion: 0.07
Nodes (38): c1(), clearDelayedAndroidKey(), d1(), De(), delayAndroidKey(), f1(), find(), Fr() (+30 more)

### Community 64 - "addEventListener"
Cohesion: 0.10
Nodes (28): ad(), addEventListener(), bindResponsiveEvents(), cd(), contains(), data(), ed(), fa() (+20 more)

### Community 65 - "User"
Cohesion: 0.09
Nodes (15): User, UserFactory, DatabaseSeeder, Facebook\WebDriver\Remote\RemoteWebDriver, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Seeder, Illuminate\Foundation\Auth\User (+7 more)

### Community 67 - "qt"
Cohesion: 0.18
Nodes (26): ae(), B(), de(), dt(), Ee(), Ge(), Gt(), h() (+18 more)

### Community 68 - "_each"
Cohesion: 0.09
Nodes (26): addControllers(), addElements(), addPlugins(), addScales(), buildOrUpdateElements(), _dataCheck(), De(), dn() (+18 more)

### Community 69 - "Filament\Resources\Pages\CreateRecord"
Cohesion: 0.11
Nodes (9): CreateCategory, EditCategory, CreateCity, EditCity, CreatePost, CreateProvince, EditProvince, Filament\Resources\Pages\CreateRecord (+1 more)

### Community 70 - "Filament\Schemas\Schema"
Cohesion: 0.13
Nodes (9): CategoryForm, CityForm, PostForm, ProvinceForm, Province, Filament\Schemas\Schema, partials.post-card, partials.searchable-select (+1 more)

### Community 71 - "fn"
Cohesion: 0.17
Nodes (24): Cn(), ei(), En(), fa(), fn(), Ii(), jt(), Li() (+16 more)

### Community 72 - "T"
Cohesion: 0.08
Nodes (34): apply(), chartOptionScopes(), _computeLabelSizes(), constructor(), dc(), describe(), ga(), getDevicePixelRatio() (+26 more)

### Community 73 - "file-upload.js"
Cohesion: 0.09
Nodes (7): kc(), Wu(), constructor(), _freeze(), getExtension(), getType(), registerListeners()

### Community 74 - "tables.js"
Cohesion: 0.21
Nodes (20): areRecordsSelected(), areRecordsToggleable(), canSelectAllRecords(), deselectAllRecords(), deselectRecords(), f(), getRecordsOnPage(), getSelectedRecordsCount() (+12 more)

### Community 75 - "filament/app.js"
Cohesion: 0.13
Nodes (12): B(), C(), close(), init(), J(), S(), setUpResizeObserver(), T() (+4 more)

### Community 76 - "devDependencies"
Cohesion: 0.10
Nodes (19): axios, concurrently, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss (+11 more)

### Community 77 - "init"
Cohesion: 0.12
Nodes (22): apply(), checkAsyncSchedule(), createParse(), cS(), focus(), fromClass(), init(), isDone() (+14 more)

### Community 78 - "t"
Cohesion: 0.15
Nodes (19): Ce(), De(), ei(), Fe(), He(), Ie(), ii(), le() (+11 more)

### Community 79 - "addEventListener"
Cohesion: 0.14
Nodes (19): addEventListener(), al(), bindResponsiveEvents(), bs(), cl(), di(), dl(), getPlugin() (+11 more)

### Community 80 - "add"
Cohesion: 0.07
Nodes (38): active(), add(), _animateOptions(), beforeUpdate(), _cachedScopes(), cancel(), ci(), _createAnimations() (+30 more)

### Community 81 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.17
Nodes (5): EditPost, PostDetail, PostImage, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\SoftDeletes

### Community 82 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.19
Nodes (5): ListCategories, ListCities, ListPosts, ListProvinces, Filament\Resources\Pages\ListRecords

### Community 83 - "composer.json"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 84 - "getChild"
Cohesion: 0.22
Nodes (14): Ab(), Bl(), BP(), ci(), Eb(), Gb(), getChild(), hi() (+6 more)

### Community 85 - "require-dev"
Cohesion: 0.18
Nodes (11): require-dev, fakerphp/faker, filament/upgrade, laravel/dusk, laravel/pail, laravel/pint, laravel/sail, mockery/mockery (+3 more)

### Community 86 - "scripts"
Cohesion: 0.18
Nodes (11): scripts, dev, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite, @php artisan config:clear --ansi (+3 more)

### Community 87 - "HomePage"
Cohesion: 0.20
Nodes (4): Laravel\Dusk\Browser, Laravel\Dusk\Page, HomePage, Page

### Community 88 - "le"
Cohesion: 0.29
Nodes (11): be(), de(), em(), Fe(), Gt(), j(), je(), le() (+3 more)

### Community 89 - "require"
Cohesion: 0.20
Nodes (10): require, dedoc/scramble, filament/filament, laravel/framework, laravel/sanctum, laravel/tinker, maatwebsite/excel, openai-php/laravel (+2 more)

### Community 90 - "vl"
Cohesion: 0.22
Nodes (9): bi(), ll(), ol(), qp(), Rp(), vl(), vp(), xl() (+1 more)

### Community 91 - "lt"
Cohesion: 0.28
Nodes (9): e(), ha(), It(), jp(), lt(), ra(), va(), Wt() (+1 more)

### Community 93 - "Illuminate\Console\Command"
Cohesion: 0.36
Nodes (4): GenerateCitySlug, GenerateProvinceSlug, Command, Illuminate\Console\Command

### Community 94 - "yl"
Cohesion: 0.29
Nodes (8): Kp(), Lp(), sa(), Sp(), wp(), xt(), Ye(), yl()

### Community 95 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 96 - "e"
Cohesion: 0.29
Nodes (6): Be(), di(), e(), i(), Ut(), xr()

### Community 97 - "d"
Cohesion: 0.33
Nodes (7): d(), ns(), Oe(), Oo(), se(), $t(), ws()

### Community 98 - "SitemapController.php"
Cohesion: 0.40
Nodes (3): Controller, SitemapController, Illuminate\Http\Response

### Community 99 - "actions/actions.js"
Cohesion: 0.73
Nodes (5): closeModal(), generateModalId(), init(), openModal(), syncActionModals()

### Community 100 - "a"
Cohesion: 0.33
Nodes (6): a(), at(), H(), ji(), L(), pt()

### Community 102 - "RahasiaPanelProvider.php"
Cohesion: 0.60
Nodes (3): RahasiaPanelProvider, Filament\Panel, Filament\PanelProvider

### Community 103 - "post-show.blade.php"
Cohesion: 0.40
Nodes (4): closeLightbox, openLightbox(@js($image->image_url)), openLightbox(@js($post->thumbnail)), partials.post-card

### Community 104 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 105 - "c"
Cohesion: 0.40
Nodes (5): c(), o(), Oe(), s(), Se()

### Community 106 - "clickPercent"
Cohesion: 0.60
Nodes (5): clickPercent(), getPosition(), mouseUp(), moveplayhead(), timelineClicked()

### Community 108 - "post-autoload-dump"
Cohesion: 0.50
Nodes (4): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan filament:upgrade, @php artisan package:discover --ansi

### Community 109 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 110 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 126 - "Dm"
Cohesion: 0.67
Nodes (3): Dm(), Lm(), pi()

## Knowledge Gaps
- **70 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+65 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **6 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `j0()` connect `j0` to `rich-editor.js`, `stat/chart.js`, `draw`, `markdown-editor.js`, `components/select.js`, `constructor`, `t`, `.forEach`, `e`, `y`, `Fn`, `support.js`, `getPixelForValue`, `slider.js`, `isHorizontal`, `vd`, `m`, `ph`, `getSortedVisibleDatasetMetas`, `wc`, `addEventListener`?**
  _High betweenness centrality (0.064) - this node is a cross-community bridge._
- **Why does `m()` connect `m` to `stat/chart.js`, `resolve`, `constructor`, `draw`, `markdown-editor.js`, `_update`, `get`, `t`, `_update`, `.slice`, `y`, `support.js`, `getPixelForValue`, `I`, `isHorizontal`, `buildTicks`, `vd`, `j0`, `le`, `getContext`, `_a`, `ph`, `wc`, `match`, `qt`, `fn`, `filament/app.js`, `vl`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **Why does `p()` connect `draw` to `rich-editor.js`, `stat/chart.js`, `resolve`, `update`, `_update`, `get`, `constructor`, `t`, `.slice`, `e`, `draw`, `y`, `support.js`, `getPixelForValue`, `isHorizontal`, `buildTicks`, `h`, `vd`, `D`, `m`, `getContext`, `ph`, `echo.js`, `wc`, `match`, `fn`, `filament/app.js`, `e`, `c`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **Are the 19 inferred relationships involving `update()` (e.g. with `Ls()` and `a()`) actually correct?**
  _`update()` has 19 INFERRED edges - model-reasoned connections that need verification._
- **Are the 136 inferred relationships involving `t()` (e.g. with `add()` and `append()`) actually correct?**
  _`t()` has 136 INFERRED edges - model-reasoned connections that need verification._
- **Are the 131 inferred relationships involving `i()` (e.g. with `add()` and `addElement()`) actually correct?**
  _`i()` has 131 INFERRED edges - model-reasoned connections that need verification._
- **Are the 11 inferred relationships involving `constructor()` (e.g. with `a()` and `h()`) actually correct?**
  _`constructor()` has 11 INFERRED edges - model-reasoned connections that need verification._