<?php
require_once __DIR__ . '/barang_model.php';
wajib_login();

const HALAMAN_LIST = '../html/barang_list.php';
const HALAMAN_FORM = '../html/barang_form.php';

wajib_post(HALAMAN_LIST);

function validasi_barang(array $d, bool $baru): array
{
    $err = [];
    if ($d['kode_barang'] === '') $err[] = 'Kode barang wajib diisi.';
    if ($d['nama_barang'] === '') $err[] = 'Nama barang wajib diisi.';
    if ($d['satuan'] === '')      $err[] = 'Satuan wajib diisi.';
    if ($baru && !ctype_digit((string)$d['stok'])) $err[] = 'Stok awal harus berupa angka bulat 0 atau lebih.';
    if (!ctype_digit((string)$d['stok_minimum']))  $err[] = 'Stok minimum harus berupa angka bulat 0 atau lebih.';
    if (!is_numeric($d['harga']) || $d['harga'] < 0) $err[] = 'Harga harus berupa angka 0 atau lebih.';
    if ((int)$d['id_kategori'] <= 0) $err[] = 'Kategori wajib dipilih.';
    if ((int)$d['id_lokasi'] <= 0)   $err[] = 'Lokasi wajib dipilih.';
    return $err;
}

function pesan_db(PDOException $e): string
{
    return pesan_db_umum(
        $e,
        'Kode barang sudah dipakai barang lain.',
        'Barang tidak bisa dihapus karena sudah punya riwayat transaksi.'
    );
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_barang'] ?? 0);

$data = [
    'kode_barang'  => strtoupper(trim($_POST['kode_barang'] ?? '')),
    'nama_barang'  => trim($_POST['nama_barang'] ?? ''),
    'satuan'       => trim($_POST['satuan'] ?? ''),
    'stok'         => trim($_POST['stok'] ?? '0'),
    'stok_minimum' => trim($_POST['stok_minimum'] ?? '0'),
    'harga'        => trim($_POST['harga'] ?? '0'),
    'id_kategori'  => (int)($_POST['id_kategori'] ?? 0),
    'id_lokasi'    => (int)($_POST['id_lokasi'] ?? 0),
];

switch ($aksi) {
    case 'tambah':
        $err = validasi_barang($data, true);
        if ($err) {
            $_SESSION['old'] = $data;
            kembali(HALAMAN_FORM, 'error', implode(' ', $err));
        }
        try {
            tambah_barang($pdo, $data);
        } catch (PDOException $e) {
            $_SESSION['old'] = $data;
            kembali(HALAMAN_FORM, 'error', pesan_db($e));
        }
        kembali(HALAMAN_LIST, 'sukses', 'Barang berhasil ditambahkan.');
        break;

    case 'ubah':
        $balik = HALAMAN_FORM . '?id=' . $id;
        $err = validasi_barang($data, false);
        if ($id <= 0 || $err) {
            $_SESSION['old'] = $data;
            kembali($balik, 'error', $err ? implode(' ', $err) : 'Data tidak valid.');
        }
        try {
            ubah_barang($pdo, $id, $data);
        } catch (PDOException $e) {
            $_SESSION['old'] = $data;
            kembali($balik, 'error', pesan_db($e));
        }
        kembali(HALAMAN_LIST, 'sukses', 'Barang berhasil diperbarui.');
        break;

    case 'hapus':
        try {
            hapus_barang($pdo, $id);
        } catch (PDOException $e) {
            kembali(HALAMAN_LIST, 'error', pesan_db($e));
        }
        kembali(HALAMAN_LIST, 'sukses', 'Barang berhasil dihapus.');
        break;

    default:
        kembali(HALAMAN_LIST, 'error', 'Aksi tidak dikenal.');
}
