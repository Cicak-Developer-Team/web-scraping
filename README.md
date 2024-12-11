# Dokumentasi Alur Kode Web Scraping

## Gambaran Umum
Website dibuat untuk melakukan web scraping. Fungsi ini mengambil data dari beberapa halaman web berdasarkan input pengguna, seperti URL, elemen HTML yang ingin diambil, dan jumlah halaman yang akan diakses.

---

## Alur Kerja

### 1. **Input Data**
- **URL**: Satu atau lebih alamat website yang akan diambil datanya.
- **Class Container**: Nama class HTML dari elemen yang ingin diambil informasinya.
- **Jumlah Halaman**: Berapa banyak halaman yang akan diakses (paginasi).

### 2. **Persiapan Data**
- Jika pengguna memberikan lebih dari satu URL, kode akan memisahkan daftar URL berdasarkan koma (`,`), lalu menghilangkan spasi tambahan.

### 3. **Proses Pengambilan Data**
- Untuk setiap URL:
  1. Dilakukan perulangan berdasarkan jumlah halaman yang diminta.
  2. Menambahkan nomor halaman ke URL untuk mengakses setiap halaman dalam fitur paginasi.
  3. Mengirim permintaan HTTP ke URL tersebut untuk mendapatkan konten HTML.

### 4. **Ekstraksi Data**
- Jika halaman berhasil diakses:
  - Kode mencari elemen HTML dengan class yang diberikan (`containerClass`).
  - Dari elemen tersebut, kode mengambil:
    - **Teks utama**: Isi teks dari elemen tersebut.
    - **Link**: Nilai atribut `href` jika terdapat elemen `<a>`.
    - **Gambar**: Nilai atribut `src` jika terdapat elemen `<img>`.

### 5. **Penanganan Kesalahan**
- Jika permintaan HTTP gagal, kode mencatat pesan error, misalnya "Failed to retrieve content".
- Jika terjadi kesalahan lain saat memproses konten, pesan error juga akan dicatat.

### 6. **Hasil Akhir**
- Semua data yang berhasil diambil disusun dalam format JSON:
  - Data dari elemen yang ditemukan.
  - Informasi tentang halaman yang gagal diproses (jika ada).

---

## Contoh Format JSON Hasil
Berikut adalah contoh hasil dari proses scraping:

```json
[
    {
        "page": 1,
        "data": [
            {
                "title": "Judul Artikel",
                "link": "https://contoh.com/artikel",
                "image": "https://contoh.com/image.jpg"
            },
            {
                "title": "Artikel Lain",
                "link": "https://contoh.com/artikel-lain",
                "image": null
            }
        ]
    },
    {
        "page": 2,
        "error": "Failed to retrieve content from https://contoh.com?page=2"
    }
]
