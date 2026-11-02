<?php
// Query database untuk tabel barang_keluar
require_once __DIR__ . '/stok.php';

function ambil_semua_keluar(PDO $pdo, string $cari = '', string $dari = '', string $sampai = ''): array
{
    $sql = "SELECT k.*, b.kode_barang, b.nama_barang, b.satuan
            FROM barang_keluar k
            JOIN barang b ON k.id_barang = b.id_barang
            WHERE 1=1";
    $params = [];
    if ($cari !== '') {
        $sql .= " AND (b.nama_barang LIKE :c1 OR b.kode_barang LIKE :c2 OR k.keterangan LIKE :c3)";
        $params[':c1'] = $params[':c2'] = $params[':c3'] = "%$cari%";
    }
    if ($dari !== '')   { $sql .= " AND k.tanggal >= :dari";   $params[':dari']   = $dari; }
    if ($sampai !== '') { $sql .= " AND k.tanggal <= :sampai"; $params[':sampai'] = $sampai; }
    $sql .= " ORDER BY k.tanggal DESC, k.id_keluar DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function ambil_satu_keluar(PDO $pdo, int $id, bool $kunci = false): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM barang_keluar WHERE id_keluar = :id" . ($kunci ? " FOR UPDATE" : ""));
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// Simpan transaksi keluar + kurangi stok (ditolak kalau stok tidak cukup)
function tambah_keluar(PDO $pdo, array $d): void
{
    $pdo->beginTransaction();
    try {
        ubah_stok($pdo, $d['id_barang'], -(int)$d['jumlah']);
        $stmt = $pdo->prepare(
            "INSERT INTO barang_keluar (tanggal, jumlah, keterangan, id_barang)
             VALUES (:tgl, :jml, :ket, :brg)"
        );
        $stmt->execute([
            ':tgl' => $d['tanggal'], ':jml' => $d['jumlah'],
            ':ket' => $d['keterangan'] ?: null, ':brg' => $d['id_barang'],
        ]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Ubah transaksi: kembalikan stok lama dulu, baru kurangi sesuai data baru
function ubah_keluar(PDO $pdo, int $id, array $d): void
{
    $pdo->beginTransaction();
    try {
        $lama = ambil_satu_keluar($pdo, $id, true);
        if (!$lama) throw new StokTidakCukup('Data barang keluar tidak ditemukan.');

        ubah_stok($pdo, (int)$lama['id_barang'], (int)$lama['jumlah']);
        ubah_stok($pdo, $d['id_barang'], -(int)$d['jumlah']);

        $stmt = $pdo->prepare(
            "UPDATE barang_keluar
             SET tanggal = :tgl, jumlah = :jml, keterangan = :ket, id_barang = :brg
             WHERE id_keluar = :id"
        );
        $stmt->execute([
            ':tgl' => $d['tanggal'], ':jml' => $d['jumlah'],
            ':ket' => $d['keterangan'] ?: null, ':brg' => $d['id_barang'], ':id' => $id,
        ]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Hapus transaksi keluar + kembalikan stoknya
function hapus_keluar(PDO $pdo, int $id): void
{
    $pdo->beginTransaction();
    try {
        $lama = ambil_satu_keluar($pdo, $id, true);
        if (!$lama) throw new StokTidakCukup('Data barang keluar tidak ditemukan.');

        ubah_stok($pdo, (int)$lama['id_barang'], (int)$lama['jumlah']);
        $pdo->prepare("DELETE FROM barang_keluar WHERE id_keluar = :id")->execute([':id' => $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
