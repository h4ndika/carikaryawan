# ERD.md

> Project: DISEKITAR
>
> Last Updated: YYYY-MM-DD
>
> Status: Draft

---

# 1. Purpose

Dokumen ini menjelaskan Entity Relationship Diagram (ERD) yang digunakan oleh project DISEKITAR.

Dokumen ini menjadi acuan untuk:

- Database Migration
- Eloquent Model
- API Development
- CMS Development
- Query Optimization

---

# 2. Design Principles

Seluruh desain database mengikuti prinsip berikut:

- Normalisasi hingga minimal 3NF.
- Hindari duplikasi data.
- Gunakan foreign key.
- Gunakan soft delete jika diperlukan.
- Semua entity memiliki audit field.

---

# 3. Conceptual ERD

```
Province
    │
    │ 1:N
    ▼
City
    │
    │ 1:N
    ▼
District
    │
    │ 1:N
    ▼
Village
    │
    │ 1:N
    ▼
Address
    │
    │ 1:1
    ▼
Place
    │
    ├──────────────┐
    │              │
    ▼              ▼
Category      Gallery
    │
    ├──────────────┐
    ▼              ▼
Facility      Contact
    │
    ├──────────────┐
    ▼              ▼
OpeningHour  SocialMedia
    │
    ├──────────────┐
    ▼              ▼
Review       Promotion
    │
    ├──────────────┐
    ▼              ▼
Job          Event
```

---

# 4. Master Tables

## provinces

Master provinsi.

PK

- id

---

## cities

PK

- id

FK

- province_id

---

## districts

PK

- id

FK

- city_id

---

## villages

PK

- id

FK

- district_id

---

## categories

PK

- id

FK

- parent_id (nullable)

Digunakan untuk nested category.

---

## tags

PK

- id

---

## facilities

PK

- id

---

# 5. Core Tables

## places

PK

- id

FK

- category_id
- province_id
- city_id
- district_id
- village_id
- postal_code
- address
- latitude
- longitude

Unique

- slug

---

## galleries

PK

- id

FK

- place_id

---

## contacts

PK

- id

FK

- place_id

---

## opening_hours

PK

- id

FK

- place_id

---

## social_media

PK

- id

FK

- place_id

---

## reviews

PK

- id

FK

- place_id

---

## promotions

PK

- id

FK

- place_id

---

# 6. Pivot Tables

## place_tag

Many-to-many.

```
Place

N

↓

place_tag

↑

N

Tag
```

---

## place_facility

Many-to-many.

```
Place

N

↓

place_facility

↑

N

Facility
```

---

# 7. Content Tables

## articles

Artikel.

---

## article_categories

Kategori artikel.

---

## article_tags

Pivot.

---

# 8. Business Tables

## jobs

FK

- place_id

---

## events

FK

- place_id

---

# 9. User Tables

## users

Laravel default.

---

## roles

---

## permissions

---

## role_user

Pivot.

---

## permission_role

Pivot.

---

# 10. Relationship Summary

## Province

Province

1

↓

N

City

---

## City

City

1

↓

N

District

---

## District

District

1

↓

N

Village

---

## Village

Village

1

↓

N

Address

---

## Address

Address

1

↓

1

Place

---

## Category

Category

1

↓

N

Place

---

## Place

Place

1

↓

N

Gallery

Place

1

↓

N

Contact

Place

1

↓

N

OpeningHour

Place

1

↓

N

SocialMedia

Place

1

↓

N

Promotion

Place

1

↓

N

Review

Place

1

↓

N

Job

Place

1

↓

N

Event

---

## Many To Many

Place

N

↓

place_tag

↑

N

Tag

Place

N

↓

place_facility

↑

N

Facility

---

# 11. Standard Columns

Semua tabel (kecuali pivot) menggunakan:

```
id
created_at
updated_at
deleted_at
created_by
updated_by
deleted_by
```

---

# 12. Naming Convention

Table

Plural

```
places
categories
galleries
reviews
```

Primary Key

```
id
```

Foreign Key

```
place_id
category_id
province_id
```

Index

```
slug
status
published_at
latitude
longitude
```

---

# 13. Future Expansion

ERD dirancang agar mudah dikembangkan untuk:

- Marketplace
- Booking
- Ticketing
- AI Recommendation
- Nearby Search
- Advertisement
- Analytics
- Membership
- Subscription
- Payment
