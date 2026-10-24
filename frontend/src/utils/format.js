/**
 * Label status tontonan yang dipakai di seluruh tampilan.
 * Nilai mentahnya sama dengan enum di backend (App\Models\Film::STATUSES).
 */
export const STATUS_LABELS = {
  ingin_ditonton: 'Ingin ditonton',
  sedang_ditonton: 'Sedang ditonton',
  sudah_ditonton: 'Sudah ditonton',
}

export const STATUS_OPTIONS = Object.entries(STATUS_LABELS).map(([value, label]) => ({
  value,
  label,
}))

/**
 * @param {string|null|undefined} status
 * @returns {string}
 */
export function labelStatus(status) {
  return STATUS_LABELS[status] || status || '-'
}

/**
 * Meringkas ulasan supaya tidak terlalu panjang di tabel.
 * @param {string|null|undefined} review
 * @param {number} maxPanjang
 * @returns {string}
 */
export function ringkasReview(review, maxPanjang = 70) {
  if (!review) return '-'
  const teks = review.trim()
  if (teks.length <= maxPanjang) return teks
  return teks.slice(0, maxPanjang).trimEnd() + '...'
}

/**
 * @param {number|null|undefined} rating
 * @returns {string}
 */
export function formatRating(rating) {
  if (!rating) return '-'
  return `${rating}/5`
}

/**
 * Format tanggal ISO dari Laravel (created_at) menjadi format id-ID.
 * @param {string|null|undefined} tanggalIso
 * @returns {string}
 */
export function formatTanggal(tanggalIso) {
  if (!tanggalIso) return '-'
  const tanggal = new Date(tanggalIso)
  if (Number.isNaN(tanggal.getTime())) return '-'
  return tanggal.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}
