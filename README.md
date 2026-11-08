# 📦 Sistem Informasi Manajemen Inventaris Barang

Aplikasi web untuk mengelola persediaan barang: data barang, kategori, lokasi penyimpanan, supplier, serta pencatatan barang masuk dan barang keluar yang otomatis memperbarui stok.

> **Tugas Besar** — Basis Data · Program Studi Informatika, Fakultas Teknik, Universitas Bengkulu

| | |
|---|---|
| **Kelompok** | Kelompok 5 |
| **Tema** | Sistem Informasi Manajemen Inventaris Barang |
| **Demo online** | https://kesatrio.github.io/Inventaris-Barang/#barang |

## 👥 Anggota Kelompok

| No | Nama | NPM | Peran |
|---|---|---|---|
| 1 | Muhamad Kesatrio Aditya Putra | G1A025089 | Pembuat Website & Perancang Database |
| 2 | Alldy Cahya Dinitra | G1A025085 | Pembuat PPT & Peninjau Laporan |
| 3 | Annisa Aurellia | G1A025051 | Pembuat Laporan & Pemeriksa Keseluruhan Laporan |

---

## ✨ Fitur

- **CRUD lengkap** (Create, Read, Update, Delete) untuk **Barang, Kategori, Lokasi, dan Supplier**.
- **Barang Masuk** dan **Barang Keluar**: setiap pencatatan otomatis menambah atau mengurangi stok barang.
- **Validasi stok**: barang keluar ditolak jika stok tidak cukup, stok tidak pernah menjadi minus.
- **Integritas data**: data yang masih dipakai tabel lain tidak bisa dihapus (foreign key `ON DELETE RESTRICT`), dan kode barang/kategori tidak boleh kembar.
- **Dashboard**: ringkasan jumlah barang, total stok, nilai persediaan, dan daftar barang yang stoknya menipis.
- **Laporan**: 7 query yang memakai `JOIN`, `COUNT`, `SUM`, dan `GROUP BY`.
- Pencarian cepat di setiap tabel, tampilan responsif, dan mendukung mode gelap.

## 🛠️ Teknologi

| Lapisan | Teknologi |
|---|---|
| Tampilan | HTML, CSS, JavaScript (tanpa framework) |
| Backend | PHP (PDO) sebagai API JSON |
| Database | MySQL / MariaDB |
| Server lokal | Laragon |
| Deploy | GitHub Pages (Mode Demo) |
