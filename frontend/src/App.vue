<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { logout, apakahSudahLogin } from './api/http'

const router = useRouter()
const route = useRoute()
const masuk = ref(apakahSudahLogin())

// route.fullPath dibaca supaya status login ikut diperbarui saat berpindah halaman.
watch(
  () => route.fullPath,
  () => {
    masuk.value = apakahSudahLogin()
  },
)

async function keluar() {
  await logout().catch(() => {})
  masuk.value = false
  router.push('/login')
}
</script>

<template>
  <header class="site-header">
    <div class="wrap">
      <router-link class="brand" to="/">Letterbox</router-link>
      <nav class="site-nav">
        <router-link to="/">Beranda</router-link>
        <router-link v-if="masuk" to="/films">Daftar Film</router-link>
        <a v-if="masuk" href="#" @click.prevent="keluar">Logout</a>
        <router-link v-else to="/login">Login</router-link>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <router-view />
  </main>
</template>
