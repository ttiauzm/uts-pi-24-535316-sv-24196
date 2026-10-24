<script setup>
import { computed, onMounted, ref } from 'vue'
import { getFilms, createFilm, updateFilm, deleteFilm, pesanError } from '../api/http'
import {
  STATUS_OPTIONS,
  labelStatus,
  ringkasReview,
  formatRating,
  formatTanggal,
} from '../utils/format'

const films = ref([])
const status = ref('loading') // loading | ok | error
const pesan = ref('')
const filter = ref('semua')
const saving = ref(false)
const editId = ref(null)

const tahunSekarang = new Date().getFullYear()
const formKosong = () => ({
  title: '',
  director: '',
  year: tahunSekarang,
  status: 'ingin_ditonton',
  rating: '',
  review: '',
})
const form = ref(formKosong())

const daftarTampil = computed(() => {
  if (filter.value === 'semua') return films.value
  return films.value.filter((film) => film.status === filter.value)
})

const jumlah = computed(() => ({
  semua: films.value.length,
  ingin_ditonton: films.value.filter((f) => f.status === 'ingin_ditonton').length,
  sedang_ditonton: films.value.filter((f) => f.status === 'sedang_ditonton').length,
  sudah_ditonton: films.value.filter((f) => f.status === 'sudah_ditonton').length,
}))

async function ambilData() {
  status.value = 'loading'
  pesan.value = ''
  try {
    films.value = await getFilms()
    status.value = 'ok'
  } catch (err) {
    pesan.value = pesanError(err)
    status.value = 'error'
  }
}

function payloadDariForm() {
  return {
    title: form.value.title,
    director: form.value.director,
    year: Number(form.value.year),
    status: form.value.status,
    rating: form.value.rating === '' ? null : Number(form.value.rating),
    review: form.value.review === '' ? null : form.value.review,
  }
}

async function simpan() {
  pesan.value = ''
  saving.value = true
  try {
    if (editId.value) {
      await updateFilm(editId.value, payloadDariForm())
    } else {
      await createFilm(payloadDariForm())
    }
    batal()
    await ambilData()
  } catch (err) {
    pesan.value = pesanError(err)
  } finally {
    saving.value = false
  }
}

function mulaiEdit(film) {
  editId.value = film.id
  form.value = {
    title: film.title,
    director: film.director,
    year: film.year,
    status: film.status,
    rating: film.rating ?? '',
    review: film.review ?? '',
  }
  document.getElementById('form-film')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function batal() {
  editId.value = null
  form.value = formKosong()
}

async function hapus(film) {
  if (!window.confirm(`Hapus "${film.title}" dari daftar?`)) return
  pesan.value = ''
  try {
    await deleteFilm(film.id)
    if (editId.value === film.id) batal()
    await ambilData()
  } catch (err) {
    pesan.value = pesanError(err)
  }
}

onMounted(ambilData)
</script>

<template>
  <section>
    <p class="eyebrow">Koleksi pribadi</p>
    <h1>Daftar Film</h1>
    <p class="lead muted">
      Data di halaman ini dibaca dari <code>GET /api/films</code> dan setiap perubahan dikirim ke
      Laravel memakai Axios.
    </p>

    <p v-if="pesan" class="alert" role="alert">{{ pesan }}</p>

    <form id="form-film" class="panel stack" @submit.prevent="simpan">
      <div>
        <h2>{{ editId ? 'Ubah Film' : 'Tambah Film' }}</h2>
        <p class="small muted">
          {{ editId ? 'Perubahan disimpan ke endpoint PUT /api/films/' + editId + '.' : 'Data baru dikirim ke endpoint POST /api/films.' }}
        </p>
      </div>

      <div class="form-grid">
        <div>
          <label for="title">Judul</label>
          <input id="title" v-model="form.title" maxlength="255" required />
        </div>
        <div>
          <label for="director">Sutradara</label>
          <input id="director" v-model="form.director" maxlength="255" required />
        </div>
        <div>
          <label for="year">Tahun</label>
          <input
            id="year"
            v-model="form.year"
            type="number"
            min="1888"
            :max="tahunSekarang + 5"
            required
          />
        </div>
        <div>
          <label for="status">Status</label>
          <select id="status" v-model="form.status" required>
            <option v-for="opsi in STATUS_OPTIONS" :key="opsi.value" :value="opsi.value">
              {{ opsi.label }}
            </option>
          </select>
        </div>
        <div>
          <label for="rating">Rating (1-5, opsional)</label>
          <select id="rating" v-model="form.rating">
            <option value="">Belum dinilai</option>
            <option v-for="n in 5" :key="n" :value="n">{{ n }}/5</option>
          </select>
        </div>
        <div class="full">
          <label for="review">Ulasan singkat (opsional)</label>
          <textarea id="review" v-model="form.review" maxlength="1000"></textarea>
        </div>
      </div>

      <div class="button-row">
        <button type="submit" :disabled="saving">
          {{ saving ? 'Menyimpan...' : editId ? 'Simpan Perubahan' : 'Tambah Film' }}
        </button>
        <button v-if="editId" type="button" class="secondary" @click="batal">Batal</button>
      </div>
    </form>

    <hr class="divider" />

    <div class="filters" role="group" aria-label="Saring berdasarkan status">
      <button
        type="button"
        :aria-pressed="filter === 'semua'"
        @click="filter = 'semua'"
      >
        Semua ({{ jumlah.semua }})
      </button>
      <button
        v-for="opsi in STATUS_OPTIONS"
        :key="opsi.value"
        type="button"
        :aria-pressed="filter === opsi.value"
        @click="filter = opsi.value"
      >
        {{ opsi.label }} ({{ jumlah[opsi.value] }})
      </button>
    </div>

    <p v-if="status === 'loading'" class="muted">Memuat data dari Laravel...</p>
    <p v-else-if="status === 'error'" class="alert">
      Gagal mengambil data. Pastikan backend berjalan dan token masih valid.
    </p>
    <div v-else-if="daftarTampil.length === 0" class="empty">
      Belum ada film pada filter ini.
    </div>
    <div v-else class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Film</th>
            <th>Tahun</th>
            <th>Status</th>
            <th>Rating</th>
            <th>Ulasan</th>
            <th>Ditambahkan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="film in daftarTampil" :key="film.id">
            <td>
              <strong>{{ film.title }}</strong>
              <div class="small muted">{{ film.director }}</div>
            </td>
            <td class="narrow">{{ film.year }}</td>
            <td class="narrow"><span class="badge">{{ labelStatus(film.status) }}</span></td>
            <td class="narrow">{{ formatRating(film.rating) }}</td>
            <td>{{ ringkasReview(film.review) }}</td>
            <td class="narrow small muted">{{ formatTanggal(film.created_at) }}</td>
            <td class="narrow">
              <div class="button-row">
                <button type="button" class="secondary mini" @click="mulaiEdit(film)">Ubah</button>
                <button type="button" class="secondary mini" @click="hapus(film)">Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="small muted" style="margin-top: 16px">
      Menampilkan {{ daftarTampil.length }} dari {{ films.length }} film.
    </p>
  </section>
</template>
