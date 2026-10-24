import axios from 'axios'

export const TOKEN_KEY = 'letterbox_token'

// Satu instance Axios untuk semua request ke Laravel.
// Alamat API TIDAK ditulis di komponen: nilainya berasal dari VITE_API_URL.
// Di Docker, VITE_API_URL=/api dan Nginx meneruskan /api ke service "backend".
export const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { Accept: 'application/json' },
})

// Otomatis kirim token Sanctum: Authorization: Bearer <token>
http.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Token kedaluwarsa/tidak valid -> bersihkan dan arahkan ke halaman login
http.interceptors.response.use(
  (res) => res,
  (err) => {
    const dariLogin = err.config?.url?.includes('/login')
    if (err.response?.status === 401 && !dariLogin) {
      localStorage.removeItem(TOKEN_KEY)
      if (window.location.pathname !== '/login') window.location.assign('/login')
    }
    return Promise.reject(err)
  },
)

export function apakahSudahLogin() {
  return Boolean(localStorage.getItem(TOKEN_KEY))
}

export async function login(email, password) {
  const { data } = await http.post('/login', { email, password })
  localStorage.setItem(TOKEN_KEY, data.token)
  return data
}

export async function logout() {
  try {
    await http.post('/logout')
  } finally {
    localStorage.removeItem(TOKEN_KEY)
  }
}

export const getFilms = () => http.get('/films').then((r) => r.data)
export const createFilm = (payload) => http.post('/films', payload).then((r) => r.data)
export const updateFilm = (id, payload) => http.put(`/films/${id}`, payload).then((r) => r.data)
export const deleteFilm = (id) => http.delete(`/films/${id}`).then((r) => r.data)

// Ambil pesan error yang ramah dari respons Laravel (422/401/dll)
export function pesanError(err) {
  const data = err.response?.data
  if (data?.errors) return Object.values(data.errors).flat().join(' ')
  return data?.message || err.message
}
