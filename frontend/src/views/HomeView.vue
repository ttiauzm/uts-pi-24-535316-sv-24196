<script setup>
import { apakahSudahLogin } from '../api/http'

const sudahLogin = apakahSudahLogin()
</script>

<template>
  <section>
    <p class="eyebrow">UTS Konstruksi dan Evolusi Perangkat Lunak</p>
    <h1>Letterbox</h1>
    <p class="lead">
      Buku catatan tontonan sederhana. Simpan film yang ingin ditonton, sedang ditonton, atau
      sudah selesai ditonton, lengkap dengan sutradara, tahun, rating, dan ulasan singkat.
    </p>

    <div class="panel stack">
      <div>
        <h2>Yang bisa dilakukan</h2>
        <ul>
          <li>Menampilkan seluruh film yang tersimpan di basis data.</li>
          <li>Menambah film baru dan mengubah data film yang sudah ada.</li>
          <li>Menghapus film dari daftar.</li>
          <li>Menyaring daftar berdasarkan status tontonan.</li>
        </ul>
      </div>
      <div>
        <p class="small muted">
          Frontend Vue 3 ini memanggil REST API Laravel memakai Axios. Token Sanctum disimpan
          setelah login dan dikirim pada setiap permintaan berikutnya. Alamat API diatur lewat
          variabel lingkungan <code>VITE_API_URL</code>.
        </p>
        <div class="button-row">
          <router-link v-if="!sudahLogin" class="button" to="/login">Masuk ke aplikasi</router-link>
          <router-link v-else class="button" to="/films">Buka daftar film</router-link>
        </div>
      </div>
    </div>
  </section>
</template>
