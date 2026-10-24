# Letterbox — Laravel REST API + Vue 3 + Docker

**Nama:** Mutia Umniati Z
**NIM:** 24/535316/SV/24196
**Kelas:** PL5AA
**Mata kuliah:** Konstruksi dan Evolusi Perangkat Lunak (UTS)

**Repository:** <https://github.com/ttiauzm/uts-pi-24-535316-sv-24196>
**Laporan:** [`docs/laporan.md`](docs/laporan.md)

Letterbox adalah aplikasi buku catatan tontonan: menyimpan film yang **ingin ditonton**,
**sedang ditonton**, atau **sudah ditonton**, lengkap dengan sutradara, tahun, rating, dan
ulasan singkat. Tema ini berbeda dari tugas sebelumnya (CRUD catatan/notes).

- Backend: Laravel 12 + Laravel Sanctum (REST API, token Bearer)
- Frontend: Vue 3 + Vue Router + Axios
- Basis data: MySQL 8.4
- Deployment: Docker Compose + FrankenPHP, image di-push ke GHCR oleh GitHub Actions

## Struktur repository

```
.
├── backend/                  # Laravel 12 (REST API)
│   ├── app/Http/Controllers/Api/{AuthController,FilmController}.php
│   ├── app/Models/Film.php
│   ├── database/migrations/2026_01_01_000000_create_films_table.php
│   ├── database/seeders/{DatabaseSeeder,FilmSeeder}.php
│   ├── tests/Feature/Api/{AuthApiTest,FilmApiTest}.php
│   ├── docker/{Caddyfile,entrypoint.sh}
│   ├── Dockerfile
│   └── .dockerignore
├── frontend/                 # Vue 3 + Vite
│   ├── src/api/http.js       # instance Axios + token interceptor
│   ├── src/views/{HomeView,LoginView,FilmsView}.vue
│   ├── nginx.conf            # proxy /api -> backend:8000
│   ├── Dockerfile            # multi-stage: Node build -> Nginx
│   └── .dockerignore
├── postman/letterbox-api.postman_collection.json
├── .github/workflows/ci.yml  # test + build + deploy (GHCR)
├── docker-compose.yml
├── .env.example
└── README.md
```

## Alur data

```
Browser (Vue di :8080)
   │  POST /api/login (email + password)
   ▼
Nginx (container frontend)  ──proxy /api──►  FrankenPHP + Laravel (container backend :8000)
                                                   │
                                                   ▼
                                            MySQL 8.4 (container db, volume db_data)
```

1. Pengguna membuka `http://localhost:8080`, mengisi form login.
2. Axios mengirim `POST /api/login`. Karena `VITE_API_URL=/api`, browser memanggil origin yang
   sama; Nginx meneruskan permintaan ke **`http://backend:8000`** (nama service Docker, bukan IP).
3. Laravel memverifikasi password, membuat token Sanctum, dan mengembalikan JSON berisi token.
4. Frontend menyimpan token di `localStorage`; request interceptor menambahkan header
   `Authorization: Bearer <token>` pada setiap permintaan berikutnya.
5. Operasi CRUD film dikirim ke `/api/films...`; Laravel memvalidasi, menulis/membaca tabel
   `films` di MySQL, lalu membalas JSON.
6. Respons 401 (token hilang/kedaluwarsa) membuat frontend menghapus token dan kembali ke login.

## REST API

Base URL: `http://localhost:8000/api` (langsung ke Laravel) atau `http://localhost:8080/api`
(lewat Nginx). Semua respons berformat JSON.

| Method | Endpoint | Auth | Fungsi | Status |
|---|---|---|---|---|
| POST | `/login` | publik | Login, mengembalikan token | 200 / 401 / 422 |
| GET | `/me` | Bearer | Data pengguna yang login | 200 / 401 |
| POST | `/logout` | Bearer | Menghapus token aktif | 200 / 401 |
| POST | `/register` | Bearer | Membuat akun baru | 201 / 401 / 422 |
| GET | `/films` | Bearer | Tampil semua film | 200 / 401 |
| POST | `/films` | Bearer | Tambah film | 201 / 401 / 422 |
| GET | `/films/{id}` | Bearer | Tampil satu film | 200 / 401 / 404 |
| PUT/PATCH | `/films/{id}` | Bearer | Ubah film | 200 / 401 / 404 / 422 |
| DELETE | `/films/{id}` | Bearer | Hapus film | 200 / 401 / 404 |

Catatan: satu-satunya endpoint publik adalah **login**. Endpoint `/register` ikut dilindungi
token agar memenuhi ketentuan "seluruh endpoint selain login hanya dapat diakses dengan token".

Contoh:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

curl http://localhost:8000/api/films \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

Akun demo hasil seeder: **test@example.com** / **password**.

Koleksi Postman: [`postman/letterbox-api.postman_collection.json`](postman/letterbox-api.postman_collection.json)
(12 request, termasuk kasus 401/422 dan tes otomatis yang menyimpan token serta `filmId`).

## Menjalankan seluruh sistem (Docker Compose)

Prasyarat: Docker Desktop / Docker Engine dengan plugin Compose.

```bash
# 1. Siapkan kredensial (berkas .env TIDAK di-commit)
cp .env.example .env
# isi APP_KEY, MYSQL_PASSWORD, MYSQL_ROOT_PASSWORD
# APP_KEY bisa dibuat dengan:
#   docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32));"

# 2. Jalankan semua service
docker compose up -d --build
```

- Frontend: <http://localhost:8080>
- Backend: <http://localhost:8000/api> (health: <http://localhost:8000/up>)
- Urutan start: `db` sehat → `backend` (migrasi + seed) → `frontend`.
- Tiga service: `db` (MySQL 8.4), `backend` (FrankenPHP + Laravel), `frontend` (Nginx + Vue).
- Data MySQL disimpan pada named volume `db_data`.

Perintah verifikasi:

```bash
docker compose ps
curl -i http://localhost:8000/up
docker compose logs --tail=80 backend
```

Uji persistensi data:

```bash
docker compose down          # container dihapus, volume tetap
docker compose up -d         # data lama harus masih ada
```

Untuk menghapus data juga: `docker compose down -v`.

## Development tanpa Docker

Butuh PHP 8.3 + Composer, Node 20/22, dan SQLite (atau MySQL).

```bash
# Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve                       # http://127.0.0.1:8000

# Frontend (terminal lain)
cd frontend
cp .env.example .env                    # VITE_API_URL=/api
npm ci
npm run dev                             # http://localhost:5173
```

Vite memakai proxy `/api` ke `http://127.0.0.1:8000` (dapat diubah lewat
`VITE_DEV_API_TARGET`), sehingga alamat API tidak ditulis di komponen.

## Test

```bash
cd backend && php artisan test          # 19 feature/unit test
cd frontend && npm run lint && npm test && npm run build
```

Feature test backend mencakup login (200/401/422), akses tanpa token (401), register
terlindungi, CRUD film lengkap (201/200/404/422), dan penghapusan token saat logout.

## GitHub Actions (CI/CD)

Workflow: [`.github/workflows/ci.yml`](.github/workflows/ci.yml)

| Job | Kapan | Isi |
|---|---|---|
| `Backend (Laravel test)` | semua push/PR | `php artisan test` |
| `Frontend (npm ci + build)` | semua push/PR | `npm ci`, lint, unit test, `npm run build` |
| `Deploy (build & push GHCR)` | **hanya push ke `main`** | build image backend & frontend, push ke GHCR |

Job deploy memakai `if: github.event_name == 'push' && github.ref == 'refs/heads/main'`,
sehingga Pull Request dan branch `dev`/`feature/*` berhenti setelah job pengujian dan build.
Tag image memakai **commit SHA** (`ghcr.io/<owner>/<repo>-backend:<sha>` dan
`...-frontend:<sha>`), bukan `latest`.

### Secrets yang diperlukan

Settings → Secrets and variables → Actions → **New repository secret**:

| Nama | Kegunaan |
|---|---|
| `APP_KEY` | kunci Laravel (dipakai job backend & validasi compose) |
| `DB_PASSWORD` | password MySQL untuk user aplikasi |
| `DB_ROOT_PASSWORD` | password root MySQL |
| `VITE_API_URL` | opsional; default `/api` |

Tidak ada nilai rahasia yang ditulis di repository, Dockerfile, atau docker-compose.yml.

### Branch dan branch protection

Branch: `main`, `dev`, dan minimal satu `feature/*` (contoh: `feature/letterbox`).
Semua perubahan masuk lewat Pull Request. Pasang ruleset/branch protection pada `main` dan
`dev` dengan:

- Require a pull request before merging
- Require status checks to pass: `Backend (Laravel test)` dan `Frontend (npm ci + build)`
- Block force pushes
- Restrict deletions

Langkah rinci (termasuk contoh payload API ruleset) ada di
[`docs/github-setup.md`](docs/github-setup.md).

## FrankenPHP

Backend memakai `dunglas/frankenphp:1.13.3-php8.3-alpine` (versi eksplisit, bukan `latest`),
menggantikan `php artisan serve` dan PHP-FPM/Nginx. Konfigurasi ada di
`backend/docker/Caddyfile`; log akses dikirim ke stderr sehingga bukti permintaan yang
dilayani FrankenPHP terlihat di `docker compose logs backend`.

Bukti cepat:

```bash
curl -i http://localhost:8000/up
# Server: FrankenPHP / Caddy, X-Powered-By: PHP/8.3.x
docker compose logs --tail=50 backend
# baris "handled request" dengan logger http.log.access
```

## Dockerfile

- `backend/Dockerfile`: multi-stage (`composer:2.9.8` → `dunglas/frankenphp:1.13.3-php8.3-alpine`).
  `composer.json` + `composer.lock` disalin dan dipasang **sebelum** kode aplikasi, sehingga
  layer dependensi tetap ter-cache saat kode berubah. Berjalan sebagai `www-data`.
- `frontend/Dockerfile`: multi-stage (`node:22.20.0-alpine3.22` → `nginxinc/nginx-unprivileged:1.27.5-alpine3.21`).
  Image akhir hanya berisi `dist/` + konfigurasi Nginx; `node_modules` tidak ikut.
- `backend/.dockerignore` dan `frontend/.dockerignore` mengecualikan `.git`, `.env*`, `vendor`,
  `node_modules`, cache, dan berkas yang tidak dibutuhkan saat runtime.

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| `docker compose up` gagal: `MYSQL_PASSWORD` belum diisi | Salin `.env.example` → `.env`, isi kredensial. |
| Frontend 502 saat memanggil API | Backend belum sehat. Cek `docker compose ps`, `docker compose logs backend`; pastikan `backend` healthy. |
| 401 terus setelah login | Token terhapus/kedaluwarsa. Login ulang; cek header `Authorization` di DevTools. |
| CORS saat `npm run dev` | Jalankan lewat proxy Vite (`/api`), atau tambahkan origin `http://localhost:5173` ke `CORS_ALLOWED_ORIGINS`. |
| Data hilang setelah restart | Jangan pakai `docker compose down -v`; volume `db_data` harus dipertahankan. |
| Migrasi gagal karena APP_KEY | Isi `APP_KEY` di `.env` (lihat `.env.example`). |
