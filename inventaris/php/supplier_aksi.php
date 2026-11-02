<?php
require_once __DIR__ . '/supplier_model.php';
wajib_login();

$list = '../html/supplier_list.php';
$form = '../html/supplier_form.php';
wajib_post($list);

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_supplier'] ?? 0);
$data = [
    'nama_supplier' => trim($_POST['nama_supplier'] ?? ''),
    'telepon'       => trim($_POST['telepon'] ?? ''),
    'alamat'        => trim($_POST['alamat'] ?? ''),
];

$pesanDb = fn(PDOException $e) => pesan_db_umum(
    $e,
    'Data supplier sudah ada.',
    'Supplier tidak bisa dihapus karena sudah punya riwayat barang masuk.'
);

if ($aksi === 'tambah' || $aksi === 'ubah') {
    $balik = $aksi === 'ubah' ? "$form?id=$id" : $form;
    $err = [];
    if ($data['nama_supplier'] === '') $err[] = 'Nama supplier wajib diisi.';
    if ($data['telepon'] !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $data['telepon'])) {
        $err[] = 'Nomor telepon hanya boleh berisi angka, spasi, + atau - (6-20 karakter).';
    }
    if ($err) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', implode(' ', $err));
    }
    try {
        $aksi === 'tambah' ? tambah_supplier($pdo, $data) : ubah_supplier($pdo, $id, $data);
    } catch (PDOException $e) {
        $_SESSION['old'] = $data;
        kembali($balik, 'error', $pesanDb($e));
    }
    kembali($list, 'sukses', $aksi === 'tambah' ? 'Supplier berhasil ditambahkan.' : 'Supplier berhasil diperbarui.');
}

if ($aksi === 'hapus') {
    try {
        hapus_supplier($pdo, $id);
    } catch (PDOException $e) {
        kembali($list, 'error', $pesanDb($e));
    }
    kembali($list, 'sukses', 'Supplier berhasil dihapus.');
}

kembali($list, 'error', 'Aksi tidak dikenal.');
