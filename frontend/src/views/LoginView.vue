<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login, pesanError } from '../api/http'

const router = useRouter()
const email = ref('test@example.com')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await login(email.value, password.value)
    router.push('/films')
  } catch (err) {
    error.value = pesanError(err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="login-shell">
    <p class="eyebrow">Masuk</p>
    <h1>Login</h1>
    <p class="muted small">
      Token dari Laravel Sanctum akan disimpan di browser dan dipakai untuk seluruh permintaan
      berikutnya.
    </p>

    <form class="panel stack" @submit.prevent="submit">
      <div>
        <label for="email">Email</label>
        <input id="email" v-model="email" type="email" autocomplete="username" required />
      </div>
      <div>
        <label for="password">Password</label>
        <input
          id="password"
          v-model="password"
          type="password"
          autocomplete="current-password"
          required
        />
      </div>
      <p v-if="error" class="alert" role="alert">{{ error }}</p>
      <div class="button-row">
        <button type="submit" :disabled="loading">{{ loading ? 'Memproses...' : 'Masuk' }}</button>
      </div>
    </form>

    <p class="credentials small muted">
      Akun demo hasil seeder: <strong>test@example.com</strong> / <strong>password</strong>
    </p>
  </section>
</template>
