# syntax=docker/dockerfile:1

# =====================================================================
# Stage 1 (build): install dependensi + build Vue (Vite) jadi file statis
# =====================================================================
FROM node:22.20.0-alpine3.22 AS build
WORKDIR /app

# Layer dependensi: hanya berubah kalau package*.json berubah
COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Alamat API dipasang saat build (dibaca Vite sebagai VITE_API_URL).
# Default "/api" = origin yang sama, diteruskan Nginx ke service "backend".
ARG VITE_API_URL=/api
ENV VITE_API_URL=$VITE_API_URL
RUN npm run build

# =====================================================================
# Stage 2 (runtime): Nginx non-root, hanya berisi hasil build statis
# =====================================================================
FROM nginxinc/nginx-unprivileged:1.27.5-alpine3.21 AS runtime

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 8080

HEALTHCHECK --interval=15s --timeout=5s --start-period=10s --retries=5 \
    CMD wget -qO- http://127.0.0.1:8080/ > /dev/null || exit 1
