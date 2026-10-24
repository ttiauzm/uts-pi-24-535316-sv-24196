import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  // Alamat backend untuk dev server. Di Docker, alamat ini tidak dipakai:
  // Nginx meneruskan /api ke service "backend" di jaringan Compose.
  const env = loadEnv(mode, process.cwd(), '')
  const devApiTarget = env.VITE_DEV_API_TARGET || 'http://127.0.0.1:8000'

  return {
    plugins: [vue()],
    server: {
      proxy: {
        '/api': {
          target: devApiTarget,
          changeOrigin: true,
        },
      },
    },
    test: {
      // 'node' cukup karena yang diuji hanya fungsi logika murni,
      // bukan komponen Vue -> tidak butuh Laravel atau browser.
      environment: 'node',
      globals: true,
    },
  }
})
