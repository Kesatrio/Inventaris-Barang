<?php
// Query database untuk tabel kategori
require_once __DIR__ . '/fungsi.php';

// Ikut menghitung jumlah barang per kategori (LEFT JOIN + GROUP BY)
function ambil_semua_kategori(PDO $pdo, string $cari = ''): array
{
    $sql = "SELECT k.*, COUNT(b.id_barang) AS jumlah_barang
            FROM kategori k
            LEFT JOIN barang b ON b.id_kategori = k.id_kategori";
    $params = [];
    if ($cari !== '') {
        $sql .= " WHERE k.nama_kategori LIKE :c";
        $params[':c'] = "%$cari%";
    }
    $sql .= " GROUP BY k.id_kategori ORDER BY k.nama_kategori";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function ambil_satu_kategori(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE id_kategori = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function tambah_kategori(PDO $pdo, array $d): void
{
    $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (:nama)");
    $stmt->execute([':nama' => $d['nama_kategori']]);
}

function ubah_kategori(PDO $pdo, int $id, array $d): void
{
    $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = :nama WHERE id_kategori = :id");
    $stmt->execute([':nama' => $d['nama_kategori'], ':id' => $id]);
}

function hapus_kategori(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = :id");
    $stmt->execute([':id' => $id]);
}
