<?php
// Query database untuk tabel lokasi
require_once __DIR__ . '/fungsi.php';

function ambil_semua_lokasi(PDO $pdo, string $cari = ''): array
{
    $sql = "SELECT l.*, COUNT(b.id_barang) AS jumlah_barang
            FROM lokasi l
            LEFT JOIN barang b ON b.id_lokasi = l.id_lokasi";
    $params = [];
    if ($cari !== '') {
        $sql .= " WHERE l.nama_lokasi LIKE :c1 OR l.keterangan LIKE :c2";
        $params[':c1'] = "%$cari%";
        $params[':c2'] = "%$cari%";
    }
    $sql .= " GROUP BY l.id_lokasi ORDER BY l.nama_lokasi";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function ambil_satu_lokasi(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM lokasi WHERE id_lokasi = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function tambah_lokasi(PDO $pdo, array $d): void
{
    $stmt = $pdo->prepare("INSERT INTO lokasi (nama_lokasi, keterangan) VALUES (:nama, :ket)");
    $stmt->execute([':nama' => $d['nama_lokasi'], ':ket' => $d['keterangan'] ?: null]);
}

function ubah_lokasi(PDO $pdo, int $id, array $d): void
{
    $stmt = $pdo->prepare("UPDATE lokasi SET nama_lokasi = :nama, keterangan = :ket WHERE id_lokasi = :id");
    $stmt->execute([':nama' => $d['nama_lokasi'], ':ket' => $d['keterangan'] ?: null, ':id' => $id]);
}

function hapus_lokasi(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare("DELETE FROM lokasi WHERE id_lokasi = :id");
    $stmt->execute([':id' => $id]);
}
