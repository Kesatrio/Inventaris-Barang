<?php
// Fungsi-fungsi umum yang dipakai semua halaman
require_once __DIR__ . '/../config/koneksi.php';

// Amankan teks sebelum ditampilkan di HTML (mencegah XSS)
function e($teks): string
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// Ambil pesan flash (notifikasi sekali tampil) lalu hapus dari session
function ambil_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// Simpan pesan flash lalu pindah ke halaman lain
function kembali(string $url, string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
    header("Location: $url");
    exit;
}

// Ambil data form lama (setelah gagal validasi) supaya isian tidak hilang
function ambil_old(): ?array
{
    $old = $_SESSION['old'] ?? null;
    unset($_SESSION['old']);
    return $old;
}

// Halaman yang memanggil fungsi ini hanya bisa dibuka setelah login
function wajib_login(): void
{
    if (empty($_SESSION['pengguna'])) {
        $_SESSION['flash'] = ['tipe' => 'error', 'pesan' => 'Silakan login terlebih dahulu.'];
        // Path relatif dari folder html/ maupun php/ sama-sama ke ../html/login.php
        header('Location: ../html/login.php');
        exit;
    }
}

// Tolak request yang bukan POST (untuk file aksi)
function wajib_post(string $urlBalik): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: $urlBalik");
        exit;
    }
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function tanggal_id(?string $tgl): string
{
    if (!$tgl) return '-';
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    [$y, $m, $d] = explode('-', $tgl);
    return (int)$d . ' ' . $bulan[(int)$m] . ' ' . $y;
}

// Cek format tanggal YYYY-MM-DD yang valid
function tanggal_valid(string $tgl): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $tgl);
    return $d && $d->format('Y-m-d') === $tgl;
}

// Terjemahkan kode error MySQL menjadi pesan yang mudah dipahami
function pesan_db_umum(PDOException $e, string $duplikat = 'Data sudah ada.', string $dipakai = 'Data tidak bisa dihapus karena masih dipakai data lain.'): string
{
    $kode = $e->errorInfo[1] ?? 0;
    if ($kode == 1062) return $duplikat;
    if ($kode == 1451) return $dipakai;
    if ($kode == 1452) return 'Data relasi yang dipilih tidak valid.';
    return 'Terjadi kesalahan pada database.';
}
