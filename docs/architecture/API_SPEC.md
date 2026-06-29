# API_SPEC.md

> Project: DISEKITAR
>
> Version: v1
>
> Status: Draft

---

# 1. Purpose

Dokumen ini mendefinisikan spesifikasi REST API untuk project DISEKITAR.

Dokumen ini menjadi kontrak antara:

- Backend
- Frontend
- Mobile App
- AI Agent
- Third Party Integration

Semua endpoint wajib mengikuti spesifikasi ini.

---

# 2. API Principles

Semua endpoint menggunakan:

- RESTful
- JSON
- Stateless
- Bearer Token Authentication
- Versioning

Base URL

```
/api/v1
```

---

# 3. Standard Response

## Success

```json
{
    "success": true,
    "message": "Success",
    "data": {}
}
```

---

## Error

```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {}
}
```

---

## Pagination

```json
{
    "success": true,
    "data": [],
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 250,
        "last_page": 13
    }
}
```

---

# 4. Authentication

Public

- Login
- Refresh Token

Protected

Semua endpoint admin.

Authorization

```
Authorization: Bearer {token}
```

---

# 5. Authentication API

POST

```
/auth/login
```

POST

```
/auth/logout
```

POST

```
/auth/refresh
```

GET

```
/auth/me
```

---

# 6. Place API

GET

```
/places
```

List Place.

Support:

- Search
- Filter
- Sort
- Pagination

---

GET

```
/places/{id}
```

Detail Place.

---

POST

```
/places
```

Create Place.

---

PUT

```
/places/{id}
```

Update Place.

---

DELETE

```
/places/{id}
```

Soft Delete.

---

# 7. Category API

GET

```
/categories
```

GET

```
/categories/{id}
```

POST

```
/categories
```

PUT

```
/categories/{id}
```

DELETE

```
/categories/{id}
```

---

# 8. Gallery API

GET

```
/places/{id}/gallery
```

POST

```
/places/{id}/gallery
```

DELETE

```
/gallery/{id}
```

---

# 9. Contact API

GET

```
/places/{id}/contacts
```

POST

```
/places/{id}/contacts
```

PUT

```
/contacts/{id}
```

DELETE

```
/contacts/{id}
```

---

# 10. Opening Hour API

GET

```
/places/{id}/opening-hours
```

POST

```
/places/{id}/opening-hours
```

PUT

```
/opening-hours/{id}
```

DELETE

```
/opening-hours/{id}
```

---

# 11. Facility API

GET

```
/facilities
```

POST

```
/facilities
```

PUT

```
/facilities/{id}
```

DELETE

```
/facilities/{id}
```

---

# 12. Tag API

GET

```
/tags
```

POST

```
/tags
```

PUT

```
/tags/{id}
```

DELETE

```
/tags/{id}
```

---

# 13. Review API

GET

```
/places/{id}/reviews
```

POST

```
/places/{id}/reviews
```

PUT

```
/reviews/{id}
```

DELETE

```
/reviews/{id}
```

---

# 14. Event API

GET

```
/events
```

GET

```
/events/{id}
```

POST

```
/events
```

PUT

```
/events/{id}
```

DELETE

```
/events/{id}
```

---

# 15. Job API

GET

```
/jobs
```

GET

```
/jobs/{id}
```

POST

```
/jobs
```

PUT

```
/jobs/{id}
```

DELETE

```
/jobs/{id}
```

---

# 16. Article API

GET

```
/articles
```

GET

```
/articles/{slug}
```

POST

```
/articles
```

PUT

```
/articles/{id}
```

DELETE

```
/articles/{id}
```

---

# 17. Master API

Province

```
GET /provinces
```

City

```
GET /cities
```

District

```
GET /districts
```

Village

```
GET /villages
```

---

# 18. Query Parameters

Search

```
?q=hotel
```

Pagination

```
?page=2
&per_page=20
```

Sorting

```
?sort=name
?order=asc
```

Filter

```
?category=hotel
```

Multiple Filter

```
?city=cirebon
&district=kesambi
&status=published
```

---

# 19. HTTP Status Code

```
200 OK
```

```
201 Created
```

```
204 No Content
```

```
400 Bad Request
```

```
401 Unauthorized
```

```
403 Forbidden
```

```
404 Not Found
```

```
422 Validation Error
```

```
500 Internal Server Error
```

---

# 20. Validation Rules

Semua endpoint wajib:

- Validate Request
- Return JSON
- Gunakan Form Request
- Tidak me-return HTML

---

# 21. Resource Response

Semua response menggunakan Laravel API Resource.

Contoh

```
PlaceResource
CategoryResource
ArticleResource
ReviewResource
```

---

# 22. Rate Limit

Public API

```
60 request / minute
```

Authenticated API

```
300 request / minute
```

---

# 23. Future API

Direncanakan:

- AI Recommendation API
- Nearby API
- Search Suggestion API
- Favorite API
- Notification API
- Analytics API
- Sitemap API
- Public Widget API
