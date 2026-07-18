# DISEKITAR — Task Board

Status: living doc. Update tiap milestone selesai.

## Done

- [x] Fondasi Laravel 12 + Filament admin (`/rahasia`)
- [x] Model: Post, Category, City, Province, PostDetail, PostImage
- [x] SEO fields: title/desc/robots/canonical/og + helpers
- [x] Public frontend Livewire: Home, PostList, PostShow
- [x] URL SEO: `/{category}/{city}`, `/{category}/{city}/{slug}`
- [x] Image: URL + upload + preview + multi
- [x] Lightbox detail
- [x] Home: filter kategori + kota → list
- [x] Related posts (same category + city)
- [x] 404 ramah
- [x] `/sitemap.xml` + `public/robots.txt`
- [x] JSON-LD Article di detail
- [x] Search `/cari` (keyword + kategori + kota)
- [x] Vite: `npm install && npm run build` → `public/build/manifest.json` OK
- [x] Smoke path: home → list → detail → related → search → sitemap
- [x] Fix: PostList Livewire implicit bind (`public Category $category` 404 on slug)

## Now / Next

- [ ] Subdomain per kategori (hotel.disekitar.com/…) — nanti
- [ ] API + frontend terpisah — nanti
- [ ] Full-text search (MySQL FULLTEXT / Scout) jika LIKE lambat
- [ ] Monetisasi / UGC — out of scope fondasi

## Notes

- Hosting shared: favor image URL/CDN, jangan full local upload massal.
- Publish gate: hanya `published` + `published_at <= now` di frontend.
- Jangan pakai public Eloquent props bernama sama dengan route param di Livewire (implicit id bind).
