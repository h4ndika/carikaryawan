# PERMISSION_MODEL.md

> Project: DISEKITAR
>
> Version: 1.0
>
> Status: Draft

---

# 1. Purpose

Dokumen ini mendefinisikan model otorisasi (Authorization) yang digunakan pada seluruh sistem DISEKITAR.

Mencakup:

- Roles
- Permissions
- Access Control
- Policy
- Ownership
- Future Multi-Tenant Support

---

# 2. Authorization Principles

Sistem menggunakan:

- Authentication
- Role-Based Access Control (RBAC)
- Laravel Policy
- Middleware Permission
- Least Privilege Principle

User hanya diberikan hak akses yang benar-benar dibutuhkan.

---

# 3. Authentication vs Authorization

Authentication

Menentukan:

> Siapa pengguna?

Authorization

Menentukan:

> Apa yang boleh dilakukan pengguna?

---

# 4. Role Hierarchy

```text
Super Admin
│
├── Administrator
│
├── Content Manager
│   ├── Editor
│   └── Reviewer
│
├── Moderator
│
├── Contributor
│
└── Viewer
```

Role bersifat hierarkis secara bisnis, tetapi implementasi tetap menggunakan permission eksplisit agar mudah dikontrol.

---

# 5. Default Roles

## Super Admin

Hak penuh terhadap seluruh sistem.

Dapat:

- Kelola User
- Kelola Role
- Kelola Permission
- Kelola Master Data
- Kelola Semua Module
- System Configuration

---

## Administrator

Mengelola operasional aplikasi.

Tidak dapat:

- Menghapus Super Admin
- Mengubah konfigurasi sistem inti

---

## Content Manager

Mengelola seluruh konten.

Dapat:

- Create
- Update
- Publish
- Archive

---

## Editor

Mengedit konten.

Tidak dapat:

- Publish
- Delete Permanen
- Mengelola User

---

## Reviewer

Melakukan review sebelum publish.

Dapat:

- Approve
- Reject
- Request Revision

---

## Moderator

Mengelola:

- Review
- Laporan
- Konten bermasalah

---

## Contributor

Membuat draft konten.

Tidak dapat publish.

---

## Viewer

Hanya dapat melihat dashboard sesuai izin.

---

# 6. Permission Naming

Gunakan format:

```text
resource.action
```

Contoh:

```text
place.view
place.create
place.update
place.delete
place.publish
```

---

# 7. Module Permission

## Place

```text
place.view
place.create
place.update
place.delete
place.restore
place.publish
place.archive
```

---

## Category

```text
category.view
category.create
category.update
category.delete
```

---

## Gallery

```text
gallery.view
gallery.upload
gallery.update
gallery.delete
```

---

## Review

```text
review.view
review.reply
review.delete
review.moderate
```

---

## Event

```text
event.view
event.create
event.update
event.delete
event.publish
```

---

## Job

```text
job.view
job.create
job.update
job.delete
job.publish
```

---

## Article

```text
article.view
article.create
article.update
article.delete
article.publish
```

---

## User

```text
user.view
user.create
user.update
user.delete
```

---

## Role

```text
role.view
role.create
role.update
role.delete
```

---

## Permission

```text
permission.view
permission.create
permission.update
permission.delete
```

---

# 8. Ownership Policy

Beberapa data memiliki pemilik.

Contoh:

Article

created_by = User

Policy:

Contributor

boleh mengubah artikel miliknya sendiri.

Tidak boleh mengubah artikel milik orang lain.

---

# 9. Publish Workflow

Draft

↓

Review

↓

Approved

↓

Published

↓

Archived

Role:

Contributor

↓

Reviewer

↓

Content Manager

---

# 10. Middleware

Semua route admin menggunakan middleware.

Contoh

```php
auth
verified
permission:place.view
permission:place.create
permission:place.update
```

---

# 11. Laravel Policy

Setiap entity wajib memiliki Policy.

Contoh

```text
PlacePolicy

ArticlePolicy

EventPolicy

JobPolicy
```

Policy menentukan:

- view
- create
- update
- delete
- restore
- forceDelete
- publish

---

# 12. UI Permission

Frontend wajib menyembunyikan menu yang tidak memiliki izin.

Contoh

Tanpa:

```text
place.create
```

Tombol:

Tambah Place

tidak boleh muncul.

Catatan:

Validasi tetap dilakukan di backend.

---

# 13. API Permission

Endpoint admin wajib melakukan:

- Authentication
- Authorization
- Policy

Contoh:

```text
POST /api/v1/admin/places
```

Harus memiliki:

```text
place.create
```

---

# 14. Audit Trail

Seluruh aktivitas penting dicatat.

Contoh:

- Login
- Logout
- Create
- Update
- Delete
- Restore
- Publish
- Approve

Data yang dicatat:

- User
- Waktu
- IP
- User Agent
- Action
- Entity
- Entity ID

---

# 15. Future Multi-Tenant

Model permission dirancang agar mendukung:

- Admin Nasional
- Admin Provinsi
- Admin Kota
- Admin Kecamatan

Contoh:

Admin Kota Cirebon

hanya dapat mengelola Place di Kota Cirebon.

Permission tetap sama.

Scope data berbeda.

---

# 16. Database Structure

```text
users

roles

permissions

model_has_roles

model_has_permissions

role_has_permissions
```

Mengikuti struktur standar RBAC.

---

# 17. Best Practices

- Jangan melakukan pengecekan role langsung di Controller.
- Selalu gunakan Permission atau Policy.
- Gunakan Gate hanya untuk kebutuhan khusus.
- Semua endpoint admin harus melalui middleware authorization.
- Semua aksi penting harus tercatat pada Audit Trail.

---

# 18. Future Permissions

Disiapkan untuk:

- AI Management
- Marketplace
- Booking
- Payment
- Analytics
- Advertisement
- API Key Management
- Public API Access
- Webhook Management
- System Monitoring
