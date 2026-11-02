# Sistem Informasi Manajemen Inventaris Barang Kantor

Tugas Besar Basis Data, Kelas A2. Dibuat dengan PHP (PDO) dan MySQL/MariaDB, tanpa framework.

## Fitur

- **Login admin.** Password disimpan sebagai hash (`password_hash`), dan semua halaman wajib login.
- **Dashboard.** Menampilkan jumlah barang, nilai inventaris, barang masuk/keluar bulan ini, stok menipis, transaksi terakhir, dan nilai per kategori.
- **CRUD lengkap untuk 6 tabel:** Barang, Kategori, Lokasi, Supplier, Barang Masuk, Barang Keluar.
- **Stok otomatis:**
  - Barang masuk menambah stok, barang keluar mengurangi stok.
  - Mengubah atau menghapus transaksi akan menyesuaikan stok kembali.
  - Barang keluar ditolak kalau stok tidak cukup, jadi stok tidak pernah minus.
  - Semuanya berjalan dalam *database transaction* (`beginTransaction`, `commit`, `rollBack`) dengan `SELECT ... FOR UPDATE`.
- **Integritas data:**
  - Data yang masih dipakai tidak bisa dihapus. Contohnya, kategori yang masih punya barang (foreign key `ON DELETE RESTRICT`).
  - Kode barang dan nama kategori tidak boleh duplikat (`UNIQUE`).
- **Pencarian dan filter** di setiap halaman daftar, termasuk filter tanggal dan filter "stok menipis".
- **Laporan Stok dan Laporan Transaksi** per periode yang bisa dicetak. Laporan transaksi memakai VIEW `v_riwayat_transaksi`.
- **Keamanan dasar:** prepared statement (anti SQL injection), `htmlspecialchars` (anti XSS), dan aksi ubah/hapus hanya lewat POST.

**Akun default:** username `admin`, password `admin123`.

## Menjalankan di Laragon (lokal)

1. Salin folder `inventaris` ke `C:\laragon\www\`.
2. Klik **Start All** di Laragon.
3. Buka HeidiSQL atau phpMyAdmin, lalu jalankan file `database/db_inventaris.sql`. Database `db_inventaris` akan dibuat otomatis beserta data contohnya.
4. Buka `http://localhost/inventaris` dan login.

## Deploy online (contoh: InfinityFree, gratis)

1. Daftar di infinityfree.com, lalu buat akun hosting dan subdomain.
2. Di **Control Panel → MySQL Databases**, buat database baru. Catat *host*, *nama database*, *username*, dan *password*-nya.
3. Buka **phpMyAdmin** dari control panel dan pilih database tadi. Sebelum mengimport `db_inventaris.sql`, **hapus dulu 2 baris paling atas** (`CREATE DATABASE ...` dan `USE db_inventaris;`), karena hosting gratis tidak mengizinkan membuat database lewat SQL. Setelah itu baru import.
4. Edit `config/koneksi.php` dan isi `$host`, `$db`, `$user`, `$pass` sesuai data dari langkah 2.
5. Upload **isi** folder `inventaris` (bukan foldernya) ke dalam `htdocs` lewat File Manager atau FTP (FileZilla).
6. Buka subdomain kamu, login, lalu **ganti password admin**. Caranya: buat hash baru dengan `php -r "echo password_hash('passwordbaru', PASSWORD_DEFAULT);"`, lalu update kolom `password` di tabel `pengguna`.

Alternatif hosting PHP+MySQL gratis lain: AwardSpace, 000webhost (jika masih tersedia), atau Railway/Render (perlu kartu/kuota).

## Struktur folder

```
inventaris/
├── index.php                 → redirect ke dashboard
├── config/koneksi.php        → koneksi PDO ke database
├── database/
│   ├── db_inventaris.sql     → skema + data contoh (import ini)
│   ├── ERD_Inventaris.png    → gambar ERD untuk dikumpulkan
│   └── erd.html              → sumber gambar ERD (buka di browser untuk mengedit)
├── assets/css/style.css
├── php/                      → logika (model = query, aksi = proses form)
│   ├── fungsi.php            → helper umum (e(), flash, wajib_login, rupiah, ...)
│   ├── layout.php            → header, navigasi, footer
│   ├── stok.php              → ubah_stok() + validasi transaksi
│   ├── auth_aksi.php         → login / logout
│   ├── barang_model.php      barang_aksi.php
│   ├── kategori_model.php    kategori_aksi.php
│   ├── lokasi_model.php      lokasi_aksi.php
│   ├── supplier_model.php    supplier_aksi.php
│   ├── masuk_model.php       masuk_aksi.php
│   ├── keluar_model.php      keluar_aksi.php
│   └── laporan_model.php     → query dashboard & laporan
└── html/                     → halaman tampilan
    ├── login.php  dashboard.php  laporan.php
    ├── barang_list.php    barang_form.php
    ├── kategori_list.php  kategori_form.php
    ├── lokasi_list.php    lokasi_form.php
    ├── supplier_list.php  supplier_form.php
    ├── masuk_list.php     masuk_form.php
    └── keluar_list.php    keluar_form.php
```

Polanya sama untuk setiap modul:

- `html/X_list.php` menampilkan tabel data.
- `html/X_form.php` adalah form tambah/ubah (mode ubah kalau ada `?id=`).
- `php/X_aksi.php` menerima POST: validasi, lalu memanggil fungsi di model, lalu redirect dengan pesan.
- `php/X_model.php` berisi fungsi query SQL.

## Rancangan database

| Tabel | Primary Key | Foreign Key | Keterangan |
|---|---|---|---|
| pengguna | id_pengguna | – | Admin yang bisa login |
| kategori | id_kategori | – | Jenis barang |
| lokasi | id_lokasi | – | Tempat penyimpanan |
| supplier | id_supplier | – | Pemasok barang |
| barang | id_barang | id_kategori → kategori, id_lokasi → lokasi | Data barang + stok |
| barang_masuk | id_masuk | id_barang → barang, id_supplier → supplier | Transaksi masuk |
| barang_keluar | id_keluar | id_barang → barang | Transaksi keluar |

Relasi (semuanya one-to-many):

- kategori 1 : N barang
- lokasi 1 : N barang
- barang 1 : N barang_masuk
- barang 1 : N barang_keluar
- supplier 1 : N barang_masuk

## Usulan pembagian tugas

| Anggota | Bagian | File utama |
|---|---|---|
| 1 | Database, ERD, login, dashboard | `database/`, `auth_aksi.php`, `login.php`, `dashboard.php`, `laporan_model.php` |
| 2 | Data Barang | `barang_*.php` |
| 3 | Kategori & Lokasi | `kategori_*.php`, `lokasi_*.php` |
| 4 | Supplier & Barang Masuk | `supplier_*.php`, `masuk_*.php`, `stok.php` |
| 5 | Barang Keluar, Laporan, Deploy | `keluar_*.php`, `laporan.php`, hosting |
