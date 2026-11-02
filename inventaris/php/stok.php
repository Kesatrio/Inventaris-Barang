<?php
// Logika perubahan stok yang dipakai oleh barang masuk dan barang keluar
require_once __DIR__ . '/fungsi.php';

// Dilempar kalau stok tidak mencukupi, supaya transaksi dibatalkan (rollback)
class StokTidakCukup extends RuntimeException {}

/**
 * Menambah ($selisih positif) atau mengurangi ($selisih negatif) stok barang.
 * Wajib dipanggil di dalam transaksi database ($pdo->beginTransaction()).
 * SELECT ... FOR UPDATE mengunci baris barang agar tidak bentrok dengan
 * transaksi lain yang berjalan bersamaan.
 */
function ubah_stok(PDO $pdo, int $idBarang, int $selisih): void
{
    $stmt = $pdo->prepare("SELECT nama_barang, stok, satuan FROM barang WHERE id_barang = :id FOR UPDATE");
    $stmt->execute([':id' => $idBarang]);
    $b = $stmt->fetch();

    if (!$b) {
        throw new StokTidakCukup('Barang tidak ditemukan.');
    }
    if ($b['stok'] + $selisih < 0) {
        throw new StokTidakCukup(sprintf(
            'Stok %s tidak mencukupi (tersedia %d %s).',
            $b['nama_barang'], $b['stok'], $b['satuan']
        ));
    }

    $stmt = $pdo->prepare("UPDATE barang SET stok = stok + :s WHERE id_barang = :id");
    $stmt->execute([':s' => $selisih, ':id' => $idBarang]);
}

// Validasi isian form transaksi (dipakai masuk & keluar)
function validasi_transaksi(array $d, bool $perluSupplier): array
{
    $err = [];
    if (!tanggal_valid($d['tanggal'])) $err[] = 'Tanggal tidak valid.';
    elseif ($d['tanggal'] > date('Y-m-d')) $err[] = 'Tanggal tidak boleh melebihi hari ini.';
    if ($d['id_barang'] <= 0) $err[] = 'Barang wajib dipilih.';
    if (!ctype_digit((string)$d['jumlah']) || (int)$d['jumlah'] <= 0) $err[] = 'Jumlah harus angka bulat lebih dari 0.';
    if ($perluSupplier && $d['id_supplier'] <= 0) $err[] = 'Supplier wajib dipilih.';
    return $err;
}
