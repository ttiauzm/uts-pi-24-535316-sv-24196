import { describe, it, expect } from 'vitest'
import { pesanError } from './http'

describe('pesanError', () => {
  it('menggabungkan pesan validasi 422 dari Laravel', () => {
    const err = {
      response: {
        data: {
          errors: {
            title: ['Judul wajib diisi.'],
            year: ['Tahun tidak valid.'],
          },
        },
      },
    }
    expect(pesanError(err)).toBe('Judul wajib diisi. Tahun tidak valid.')
  })

  it('memakai message dari respons jika tidak ada errors', () => {
    expect(pesanError({ response: { data: { message: 'Email atau password salah.' } } })).toBe(
      'Email atau password salah.',
    )
  })

  it('jatuh ke err.message jika tidak ada respons (mis. jaringan mati)', () => {
    expect(pesanError({ message: 'Network Error' })).toBe('Network Error')
  })
})
