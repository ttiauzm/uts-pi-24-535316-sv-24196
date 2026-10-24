# Panduan GitHub: repository, branch, PR, ruleset, secrets

Dokumen ini melengkapi README. Ganti `OWNER` dengan username GitHub (contoh: `ttiauzm`).

## 1. Repository publik `uts-pi-NIM`

Nama yang dipakai: **`uts-pi-24-535316-sv-24196`**.

Cara paling cepat: buka repository lama → **Settings → General → Repository name** → ganti
menjadi `uts-pi-24-535316-sv-24196` → **Rename**. GitHub otomatis membuat redirect dari URL
lama, tetapi perbarui remote lokal:

```bash
git remote set-url origin https://github.com/OWNER/uts-pi-24-535316-sv-24196.git
```

Alternatif: buat repository baru (public, **tanpa** README/gitignore) lalu push dari lokal.

Pastikan **Settings → Actions → General → Actions permissions** = *Allow all actions and
reusable workflows* agar GitHub Actions aktif.

## 2. Struktur branch dan alur PR

Branch yang harus ada:

- `main` — stabil, hasil deploy.
- `dev` — integrasi.
- `feature/letterbox` — pengembangan fitur.

Alur: `feature/*` → PR ke `dev` → PR `dev` ke `main`.

### Push awal (sebelum branch protection dipasang)

```bash
git checkout main
git push -u origin main

git checkout -b dev
git push -u origin dev

git checkout -b feature/letterbox
git push -u origin feature/letterbox
```

### Pull Request

1. Buka **Pull requests → New pull request**.
2. Base `dev`, compare `feature/letterbox` → **Create pull request**.
3. Tunggu dua check hijau: **Backend (Laravel test)** dan **Frontend (npm ci + build)**.
4. **Merge pull request**.
5. Ulangi untuk base `main`, compare `dev` → merge. Push/merge ke `main` memicu job
   **Deploy (build & push GHCR)**.

Jika branch protection sudah aktif, tombol merge terkunci sampai PR disetujui/di-approve
sesuai aturan dan status check lulus.

## 3. Branch protection (Rulesets)

Buka **Settings → Rules → Rulesets → New ruleset → New branch ruleset**, atau gunakan API.
Berkas siap pakai ada di [`docs/rulesets/protect-main.json`](rulesets/protect-main.json) dan
[`docs/rulesets/protect-dev.json`](rulesets/protect-dev.json).

Dengan GitHub CLI:

```bash
gh api --method POST repos/OWNER/uts-pi-24-535316-sv-24196/rulesets \
  --input docs/rulesets/protect-main.json

gh api --method POST repos/OWNER/uts-pi-24-535316-sv-24196/rulesets \
  --input docs/rulesets/protect-dev.json
```

Aturan yang aktif:

- **Restrict deletions** — branch tidak bisa dihapus.
- **Block force pushes** — riwayat tidak bisa ditimpa.
- **Require a pull request before merging** — push langsung ditolak.
- **Require status checks to pass** — `Backend (Laravel test)` dan
  `Frontend (npm ci + build)` wajib hijau.

Screenshot untuk laporan: halaman **Settings → Rules** yang menampilkan kedua ruleset
(`protect-main`, `protect-dev`) beserta daftar aturannya, dan halaman edit ruleset yang
menampilkan status check terpilih.

## 4. Secrets

Buka **Settings → Secrets and variables → Actions → New repository secret**.

| Nama | Contoh cara membuat nilai |
|---|---|
| `APP_KEY` | `php -r "echo 'base64:'.base64_encode(random_bytes(32));"` |
| `DB_PASSWORD` | password acak, mis. `openssl rand -hex 16` |
| `DB_ROOT_PASSWORD` | password acak lain |
| `VITE_API_URL` | opsional; isi `/api` |

Workflow memakai `${{ secrets.APP_KEY }}`, `${{ secrets.DB_PASSWORD }}`, dan
`${{ secrets.DB_ROOT_PASSWORD }}`. Tidak ada nilai rahasia di repository.

Screenshot untuk laporan: halaman daftar secrets (nilai tersembunyi) dan halaman pembuatan
secret.

## 5. Bukti workflow

- **Actions → CI/CD** pada `main`: semua job hijau, termasuk **Deploy (build & push GHCR)**.
- **Actions → CI/CD** pada `dev` atau sebuah Pull Request: hanya dua job test/build;
  job deploy berstatus *skipped* (tidak berjalan).
- **Packages** (profil/organisasi → Packages): dua image,
  `uts-pi-24-535316-sv-24196-backend` dan `uts-pi-24-535316-sv-24196-frontend`,
  masing-masing dengan tag commit SHA.

Cek tag image dari terminal:

```bash
docker pull ghcr.io/OWNER/uts-pi-24-535316-sv-24196-backend:<commit-sha>
docker pull ghcr.io/OWNER/uts-pi-24-535316-sv-24196-frontend:<commit-sha>
```

## 6. Jika push langsung ke main/dev ditolak

Itu tanda branch protection bekerja. Buat branch fitur, commit, push, lalu buka PR:

```bash
git checkout -b feature/perbaikan-kecil dev
# ...ubah berkas...
git add .
git commit -m "fix: perbaikan kecil"
git push -u origin feature/perbaikan-kecil
```

Screenshot pesan `remote: error: GH006: Protected branch update failed` dapat dipakai sebagai
bukti tambahan bahwa push langsung tidak diizinkan.
