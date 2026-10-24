#!/usr/bin/env bash
# Smoke test Docker Compose: build, login, CRUD, persistensi volume, log FrankenPHP.
# Dijalankan oleh .github/workflows/compose-smoke.yml (workflow_dispatch).
set -euo pipefail

BASE="http://localhost:8000/api"
FRONT="http://localhost:8080"

wait_healthy() {
  for i in $(seq 1 60); do
    running=$(docker compose ps --services --status running | wc -l)
    healthy=$(docker compose ps --format '{{.Status}}' | grep -c 'healthy' || true)
    if [ "$running" -ge 3 ] && [ "$healthy" -ge 3 ]; then
      echo "Semua service sehat setelah ${i} percobaan."
      docker compose ps
      return 0
    fi
    echo "Menunggu service sehat... ($i/60)"
    sleep 5
  done
  echo "Service tidak sehat. Status terakhir:"
  docker compose ps
  docker compose logs --tail=120
  return 1
}

login() {
  curl -fsS -X POST "$BASE/login" \
    -H 'Content-Type: application/json' \
    -d '{"email":"test@example.com","password":"password"}' \
    | python3 -c 'import json,sys; print(json.load(sys.stdin)["token"])'
}

http_code() {
  curl -s -o /dev/null -w '%{http_code}' "$@"
}

echo "== 1. Build dan jalankan seluruh sistem =="
docker compose up -d --build
wait_healthy

echo "== 2. Login lewat backend langsung =="
TOKEN=$(login)
test -n "$TOKEN"
echo "Token diperoleh (${#TOKEN} karakter)."

echo "== 3. Tanpa token harus 401 =="
CODE=$(http_code "$BASE/films")
test "$CODE" = "401"
echo "GET /films tanpa token -> $CODE"

echo "== 4. Validasi gagal harus 422 =="
CODE=$(http_code -X POST "$BASE/films" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"title":"","director":"","year":1800,"status":"x","rating":9}')
test "$CODE" = "422"
echo "POST /films tidak valid -> $CODE"

echo "== 5. CRUD lengkap =="
CREATED=$(curl -fsS -X POST "$BASE/films" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"title":"Smoke Test Film","director":"Runner","year":2026,"status":"ingin_ditonton","rating":null,"review":"Dibuat oleh smoke test."}')
FILM_ID=$(echo "$CREATED" | python3 -c 'import json,sys; print(json.load(sys.stdin)["id"])')
echo "POST /films -> 201, id=$FILM_ID"

curl -fsS "$BASE/films/$FILM_ID" -H "Authorization: Bearer $TOKEN" > /dev/null
echo "GET /films/$FILM_ID -> 200"

curl -fsS -X PUT "$BASE/films/$FILM_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"title":"Smoke Test Film (edit)","director":"Runner","year":2026,"status":"sudah_ditonton","rating":5,"review":"Sudah diubah."}' > /dev/null
echo "PUT /films/$FILM_ID -> 200"

curl -fsS -X DELETE "$BASE/films/$FILM_ID" -H "Authorization: Bearer $TOKEN" > /dev/null
echo "DELETE /films/$FILM_ID -> 200"

echo "== 6. Frontend dan proxy Nginx -> backend =="
curl -fsS "$FRONT/" | grep -q '<title>Letterbox</title>'
echo "GET http://localhost:8080/ memuat halaman Letterbox."
curl -fsS -X POST "$FRONT/api/login" \
  -H 'Content-Type: application/json' \
  -d '{"email":"test@example.com","password":"password"}' > /dev/null
echo "POST http://localhost:8080/api/login -> 200 (proxy ke service backend)."

echo "== 7. Persistensi named volume =="
curl -fsS -X POST "$BASE/films" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"title":"Persistence Marker","director":"Docker Volume","year":2026,"status":"ingin_ditonton","rating":null,"review":"Harus tetap ada setelah down/up."}' > /dev/null
echo "Film penanda dibuat."

docker compose down
echo "docker compose down selesai (volume db_data dipertahankan)."

docker compose up -d
wait_healthy

TOKEN=$(login)
curl -fsS "$BASE/films" -H "Authorization: Bearer $TOKEN" | grep -q 'Persistence Marker'
echo "Setelah down lalu up, film penanda masih ada -> volume persisten."

echo "== 7b. Data langsung dari MySQL =="
docker compose exec -T db sh -c \
  'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "SELECT id,title,status FROM films ORDER BY id;"'

echo "== 8. Bukti FrankenPHP =="
docker compose logs --tail=80 backend | grep -E 'FrankenPHP|http.log.access|handled request|Caddy' || true
curl -sI "http://localhost:8000/up" | tr -d '\r' | grep -Ei 'HTTP/|Server:|X-Powered-By:' || true

echo "== 9. Ringkasan status =="
docker compose ps
