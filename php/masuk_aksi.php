<?php
require_once __DIR__ . '/masuk_model.php';
wajib_login();

$list = '../html/masuk_list.php';
$form = '../html/masuk_form.php';
wajib_post($list);

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_masuk'] ?? 0);
$data = [
    'tanggal'     => trim($_POST['tanggal'] ?? ''),
    'id_barang'   => (int)($_POST['id_barang'] ?? 0),
    'id_supplier' => (int)($_POST['id_supplier'] ?? 0),
    'jumlah'      => trim($_POST['jumlah'] ?? ''),
    'keterangan'  => trim($_POST['keterangan'] ?? ''),
];

// Ubah exception menjadi pesan untuk pengguna
function pesan_gagal(Throwable $e): string
{
    if ($e instanceof StokTidakCukup) {
        return $e->getMessage() . ' Sebagian stok dari transaksi ini mungkin sudah dipakai barang keluar.';
    }
    if ($e instanceof PDOException) {
        return pesan_db_umum($e);
    }
    return 'Terjadi kesalahan.';
}

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $balik = $aksi === 'ubah' ? "$form?id=$id" : $form;
    $err = validasi_transaksi($data, true);
    if ($err) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', implode(' ', $err));
    }
    try {
        $aksi === 'tambah' ? tambah_masuk($pdo, $data) : ubah_masuk($pdo, $id, $data);
    } catch (Throwable $e) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', pesan_gagal($e));
    }
    kembali($list, 'sukses', $aksi === 'tambah'
        ? 'Barang masuk berhasil dicatat, stok bertambah.'
        : 'Barang masuk berhasil diperbarui, stok disesuaikan.');
}

if ($aksi === 'hapus') {
    try {
        hapus_masuk($pdo, $id);
    } catch (Throwable $e) {
        kembali($list, 'error', pesan_gagal($e));
    }
    kembali($list, 'sukses', 'Data barang masuk dihapus, stok dikurangi kembali.');
}

kembali($list, 'error', 'Aksi tidak dikenal.');
