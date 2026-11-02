<?php
require_once __DIR__ . '/kategori_model.php';
wajib_login();

$list = '../html/kategori_list.php';
$form = '../html/kategori_form.php';
wajib_post($list);

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_kategori'] ?? 0);
$data = ['nama_kategori' => trim($_POST['nama_kategori'] ?? '')];

$pesanDb = fn(PDOException $e) => pesan_db_umum(
    $e,
    'Nama kategori sudah ada.',
    'Kategori tidak bisa dihapus karena masih dipakai oleh data barang.'
);

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $balik = $aksi === 'ubah' ? "$form?id=$id" : $form;
    if ($data['nama_kategori'] === '') {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', 'Nama kategori wajib diisi.');
    }
    try {
        $aksi === 'tambah' ? tambah_kategori($pdo, $data) : ubah_kategori($pdo, $id, $data);
    } catch (PDOException $e) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', $pesanDb($e));
    }
    kembali($list, 'sukses', $aksi === 'tambah' ? 'Kategori berhasil ditambahkan.' : 'Kategori berhasil diperbarui.');
}

if ($aksi === 'hapus') {
    try {
        hapus_kategori($pdo, $id);
    } catch (PDOException $e) {
        kembali($list, 'error', $pesanDb($e));
    }
    kembali($list, 'sukses', 'Kategori berhasil dihapus.');
}

kembali($list, 'error', 'Aksi tidak dikenal.');
