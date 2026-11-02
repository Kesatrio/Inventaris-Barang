<?php
require_once __DIR__ . '/keluar_model.php';
wajib_login();

$list = '../html/keluar_list.php';
$form = '../html/keluar_form.php';
wajib_post($list);

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_keluar'] ?? 0);
$data = [
    'tanggal'    => trim($_POST['tanggal'] ?? ''),
    'id_barang'  => (int)($_POST['id_barang'] ?? 0),
    'jumlah'     => trim($_POST['jumlah'] ?? ''),
    'keterangan' => trim($_POST['keterangan'] ?? ''),
];

function pesan_gagal(Throwable $e): string
{
    if ($e instanceof StokTidakCukup) return $e->getMessage();
    if ($e instanceof PDOException)   return pesan_db_umum($e);
    return 'Terjadi kesalahan.';
}

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $balik = $aksi === 'ubah' ? "$form?id=$id" : $form;
    $err = validasi_transaksi($data, false);
    if ($err) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', implode(' ', $err));
    }
    try {
        $aksi === 'tambah' ? tambah_keluar($pdo, $data) : ubah_keluar($pdo, $id, $data);
    } catch (Throwable $e) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', pesan_gagal($e));
    }
    kembali($list, 'sukses', $aksi === 'tambah'
        ? 'Barang keluar berhasil dicatat, stok berkurang.'
        : 'Barang keluar berhasil diperbarui, stok disesuaikan.');
}

if ($aksi === 'hapus') {
    try {
        hapus_keluar($pdo, $id);
    } catch (Throwable $e) {
        kembali($list, 'error', pesan_gagal($e));
    }
    kembali($list, 'sukses', 'Data barang keluar dihapus, stok dikembalikan.');
}

kembali($list, 'error', 'Aksi tidak dikenal.');
