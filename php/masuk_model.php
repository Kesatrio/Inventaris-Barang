<?php
// Query database untuk tabel barang_masuk
require_once __DIR__ . '/stok.php';

function ambil_semua_masuk(PDO $pdo, string $cari = '', string $dari = '', string $sampai = ''): array
{
    $sql = "SELECT m.*, b.kode_barang, b.nama_barang, b.satuan, s.nama_supplier
            FROM barang_masuk m
            JOIN barang b   ON m.id_barang   = b.id_barang
            JOIN supplier s ON m.id_supplier = s.id_supplier
            WHERE 1=1";
    $params = [];
    if ($cari !== '') {
        $sql .= " AND (b.nama_barang LIKE :c1 OR b.kode_barang LIKE :c2 OR s.nama_supplier LIKE :c3)";
        $params[':c1'] = $params[':c2'] = $params[':c3'] = "%$cari%";
    }
    if ($dari !== '')   { $sql .= " AND m.tanggal >= :dari";   $params[':dari']   = $dari; }
    if ($sampai !== '') { $sql .= " AND m.tanggal <= :sampai"; $params[':sampai'] = $sampai; }
    $sql .= " ORDER BY m.tanggal DESC, m.id_masuk DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function ambil_satu_masuk(PDO $pdo, int $id, bool $kunci = false): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM barang_masuk WHERE id_masuk = :id" . ($kunci ? " FOR UPDATE" : ""));
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// Simpan transaksi masuk + tambah stok barang
function tambah_masuk(PDO $pdo, array $d): void
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO barang_masuk (tanggal, jumlah, keterangan, id_barang, id_supplier)
             VALUES (:tgl, :jml, :ket, :brg, :sup)"
        );
        $stmt->execute([
            ':tgl' => $d['tanggal'], ':jml' => $d['jumlah'], ':ket' => $d['keterangan'] ?: null,
            ':brg' => $d['id_barang'], ':sup' => $d['id_supplier'],
        ]);
        ubah_stok($pdo, $d['id_barang'], (int)$d['jumlah']);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Ubah transaksi: batalkan efek stok lama, lalu terapkan efek stok baru
function ubah_masuk(PDO $pdo, int $id, array $d): void
{
    $pdo->beginTransaction();
    try {
        $lama = ambil_satu_masuk($pdo, $id, true);
        if (!$lama) throw new StokTidakCukup('Data barang masuk tidak ditemukan.');

        ubah_stok($pdo, (int)$lama['id_barang'], -(int)$lama['jumlah']);
        ubah_stok($pdo, $d['id_barang'], (int)$d['jumlah']);

        $stmt = $pdo->prepare(
            "UPDATE barang_masuk
             SET tanggal = :tgl, jumlah = :jml, keterangan = :ket, id_barang = :brg, id_supplier = :sup
             WHERE id_masuk = :id"
        );
        $stmt->execute([
            ':tgl' => $d['tanggal'], ':jml' => $d['jumlah'], ':ket' => $d['keterangan'] ?: null,
            ':brg' => $d['id_barang'], ':sup' => $d['id_supplier'], ':id' => $id,
        ]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Hapus transaksi masuk + kurangi kembali stoknya
function hapus_masuk(PDO $pdo, int $id): void
{
    $pdo->beginTransaction();
    try {
        $lama = ambil_satu_masuk($pdo, $id, true);
        if (!$lama) throw new StokTidakCukup('Data barang masuk tidak ditemukan.');

        ubah_stok($pdo, (int)$lama['id_barang'], -(int)$lama['jumlah']);
        $pdo->prepare("DELETE FROM barang_masuk WHERE id_masuk = :id")->execute([':id' => $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
