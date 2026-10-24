# Checklist bukti UTS

Gunakan daftar ini sebelum mengekspor laporan ke PDF.

## Lokal

- [ ] `cd backend && php artisan test` → 19 passed
- [ ] `cd frontend && npm ci && npm run build` → sukses
- [ ] `docker compose up -d --build` → tiga service sehat
- [ ] `docker compose ps` → `db`, `backend`, `frontend` Up (healthy)
- [ ] Login `test@example.com` / `password` di <http://localhost:8080>
- [ ] Tambah, ubah, hapus film dari browser
- [ ] `docker compose down` lalu `docker compose up -d` → data tetap ada
- [ ] `curl -i http://localhost:8000/up` → 200, header FrankenPHP
- [ ] `docker compose logs --tail=80 backend` → log akses FrankenPHP

## GitHub

- [ ] Repository publik `uts-pi-24-535316-sv-24196`
- [ ] Branch `main`, `dev`, `feature/letterbox`
- [ ] PR fitur → `dev` dan `dev` → `main` berstatus merged
- [ ] Ruleset `protect-main` dan `protect-dev` aktif
- [ ] Push langsung ke `main` ditolak
- [ ] Actions di `main`: tiga job hijau
- [ ] Actions di `dev`/PR: dua job hijau, deploy skipped
- [ ] Secrets: `APP_KEY`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`
- [ ] Packages: image backend dan frontend dengan tag SHA

## Postman

- [ ] Import `postman/letterbox-api.postman_collection.json`
- [ ] Login menyimpan token
- [ ] Index, store (201), show, update, delete berhasil
- [ ] Tanpa token → 401
- [ ] Data tidak valid → 422
