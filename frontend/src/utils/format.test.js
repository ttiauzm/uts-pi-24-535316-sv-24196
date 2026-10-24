import { describe, it, expect } from 'vitest'
import { labelStatus, ringkasReview, formatRating, formatTanggal } from './format'

describe('labelStatus', () => {
  it('mengubah nilai enum backend menjadi label yang mudah dibaca', () => {
    expect(labelStatus('ingin_ditonton')).toBe('Ingin ditonton')
    expect(labelStatus('sudah_ditonton')).toBe('Sudah ditonton')
  })

  it('mengembalikan tanda hubung untuk nilai kosong', () => {
    expect(labelStatus(null)).toBe('-')
  })
})

describe('ringkasReview', () => {
  it('mengembalikan tanda hubung jika ulasan kosong', () => {
    expect(ringkasReview('')).toBe('-')
  })

  it('memotong ulasan yang melebihi batas', () => {
    expect(ringkasReview('a'.repeat(80), 10)).toBe('aaaaaaaaaa...')
  })
})

describe('formatRating', () => {
  it('menampilkan rating dalam bentuk n/5', () => {
    expect(formatRating(4)).toBe('4/5')
    expect(formatRating(null)).toBe('-')
  })
})

describe('formatTanggal', () => {
  it('memformat tanggal ISO dan menolak nilai tidak valid', () => {
    expect(formatTanggal('2026-10-24T10:00:00Z')).not.toBe('-')
    expect(formatTanggal('bukan-tanggal')).toBe('-')
  })
})
