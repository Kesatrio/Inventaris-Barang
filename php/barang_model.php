<?php
// Query database untuk tabel barang
require_once __DIR__ . '/fungsi.php';

function ambil_semua_barang(PDO $pdo, string $cari = '', int $idKategori = 0, bool $hanyaMenipis = false): array
{
    $sql = "SELECT b.*, k.nama_kategori, l.nama_lokasi
            FROM barang b
            JOIN kategori k ON b.id_kategori = k.id_kategori
            JOIN lokasi l   ON b.id_lokasi   = l.id_lokasi
            WHERE 1=1";
    $params = [];
    if ($cari !== '') {
        $sql .= " AND (b.nama_barang LIKE :c1 OR b.kode_barang LIKE :c2)";
        $params[':c1'] = "%$cari%";
        $params[':c2'] = "%$cari%";
    }
    if ($idKategori > 0) {
        $sql .= " AND b.id_kategori = :kat";
        $params[':kat'] = $idKategori;
    }
    if ($hanyaMenipis) {
        $sql .= " AND b.stok <= b.stok_minimum";
    }
    $sql .= " ORDER BY b.kode_barang";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Daftar ringkas untuk pilihan dropdown di form transaksi
function ambil_pilihan_barang(PDO $pdo): array
{
    return $pdo->query("SELECT id_barang, kode_barang, nama_barang, satuan, stok
                        FROM barang ORDER BY kode_barang")->fetchAll();
}

function ambil_barang(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE id_barang = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function ambil_kategori(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
}

function ambil_lokasi(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll();
}

function tambah_barang(PDO $pdo, array $d): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO barang (kode_barang, nama_barang, satuan, stok, stok_minimum, harga, id_kategori, id_lokasi)
         VALUES (:kode, :nama, :satuan, :stok, :min, :harga, :kat, :lok)"
    );
    $stmt->execute([
        ':kode'   => $d['kode_barang'],
        ':nama'   => $d['nama_barang'],
        ':satuan' => $d['satuan'],
        ':stok'   => $d['stok'],
        ':min'    => $d['stok_minimum'],
        ':harga'  => $d['harga'],
        ':kat'    => $d['id_kategori'],
        ':lok'    => $d['id_lokasi'],
    ]);
}

// Stok TIDAK ikut diubah di sini: stok hanya berubah lewat barang masuk / keluar
function ubah_barang(PDO $pdo, int $id, array $d): void
{
    $stmt = $pdo->prepare(
        "UPDATE barang
         SET kode_barang = :kode, nama_barang = :nama, satuan = :satuan,
             stok_minimum = :min, harga = :harga, id_kategori = :kat, id_lokasi = :lok
         WHERE id_barang = :id"
    );
    $stmt->execute([
        ':kode'   => $d['kode_barang'],
        ':nama'   => $d['nama_barang'],
        ':satuan' => $d['satuan'],
        ':min'    => $d['stok_minimum'],
        ':harga'  => $d['harga'],
        ':kat'    => $d['id_kategori'],
        ':lok'    => $d['id_lokasi'],
        ':id'     => $id,
    ]);
}

function hapus_barang(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare("DELETE FROM barang WHERE id_barang = :id");
    $stmt->execute([':id' => $id]);
}
