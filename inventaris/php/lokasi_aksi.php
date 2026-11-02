<?php
require_once __DIR__ . '/lokasi_model.php';
wajib_login();

$list = '../html/lokasi_list.php';
$form = '../html/lokasi_form.php';
wajib_post($list);

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_lokasi'] ?? 0);
$data = [
    'nama_lokasi' => trim($_POST['nama_lokasi'] ?? ''),
    'keterangan'  => trim($_POST['keterangan'] ?? ''),
];

$pesanDb = fn(PDOException $e) => pesan_db_umum(
    $e,
    'Nama lokasi sudah ada.',
    'Lokasi tidak bisa dihapus karena masih dipakai oleh data barang.'
);

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $balik = $aksi === 'ubah' ? "$form?id=$id" : $form;
    if ($data['nama_lokasi'] === '') {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', 'Nama lokasi wajib diisi.');
    }
    try {
        $aksi === 'tambah' ? tambah_lokasi($pdo, $data) : ubah_lokasi($pdo, $id, $data);
    } catch (PDOException $e) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', $pesanDb($e));
    }
    kembali($list, 'sukses', $aksi === 'tambah' ? 'Lokasi berhasil ditambahkan.' : 'Lokasi berhasil diperbarui.');
}

if ($aksi === 'hapus') {
    try {
        hapus_lokasi($pdo, $id);
    } catch (PDOException $e) {
        kembali($list, 'error', $pesanDb($e));
    }
    kembali($list, 'sukses', 'Lokasi berhasil dihapus.');
}

kembali($list, 'error', 'Aksi tidak dikenal.');
