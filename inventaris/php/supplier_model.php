<?php
// Query database untuk tabel supplier
require_once __DIR__ . '/fungsi.php';

// Ikut menghitung berapa kali supplier mengirim barang
function ambil_semua_supplier(PDO $pdo, string $cari = ''): array
{
    $sql = "SELECT s.*, COUNT(m.id_masuk) AS jumlah_transaksi
            FROM supplier s
            LEFT JOIN barang_masuk m ON m.id_supplier = s.id_supplier";
    $params = [];
    if ($cari !== '') {
        $sql .= " WHERE s.nama_supplier LIKE :c1 OR s.alamat LIKE :c2";
        $params[':c1'] = "%$cari%";
        $params[':c2'] = "%$cari%";
    }
    $sql .= " GROUP BY s.id_supplier ORDER BY s.nama_supplier";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function ambil_pilihan_supplier(PDO $pdo): array
{
    return $pdo->query("SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier")->fetchAll();
}

function ambil_satu_supplier(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM supplier WHERE id_supplier = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function tambah_supplier(PDO $pdo, array $d): void
{
    $stmt = $pdo->prepare("INSERT INTO supplier (nama_supplier, telepon, alamat) VALUES (:nama, :telp, :alamat)");
    $stmt->execute([
        ':nama'   => $d['nama_supplier'],
        ':telp'   => $d['telepon'] ?: null,
        ':alamat' => $d['alamat'] ?: null,
    ]);
}

function ubah_supplier(PDO $pdo, int $id, array $d): void
{
    $stmt = $pdo->prepare("UPDATE supplier SET nama_supplier = :nama, telepon = :telp, alamat = :alamat
                           WHERE id_supplier = :id");
    $stmt->execute([
        ':nama'   => $d['nama_supplier'],
        ':telp'   => $d['telepon'] ?: null,
        ':alamat' => $d['alamat'] ?: null,
        ':id'     => $id,
    ]);
}

function hapus_supplier(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare("DELETE FROM supplier WHERE id_supplier = :id");
    $stmt->execute([':id' => $id]);
}
