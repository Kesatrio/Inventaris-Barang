<?php
// Query ringkasan untuk dashboard dan laporan
require_once __DIR__ . '/fungsi.php';

function ringkasan_dashboard(PDO $pdo): array
{
    $awalBulan = date('Y-m-01');
    $r = $pdo->query(
        "SELECT COUNT(*) AS jumlah_barang,
                COALESCE(SUM(stok), 0) AS total_stok,
                COALESCE(SUM(stok * harga), 0) AS nilai_inventaris,
                COALESCE(SUM(stok <= stok_minimum), 0) AS stok_menipis
         FROM barang"
    )->fetch();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) FROM barang_masuk WHERE tanggal >= :a");
    $stmt->execute([':a' => $awalBulan]);
    $r['masuk_bulan_ini'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) FROM barang_keluar WHERE tanggal >= :a");
    $stmt->execute([':a' => $awalBulan]);
    $r['keluar_bulan_ini'] = (int)$stmt->fetchColumn();

    return $r;
}

function barang_stok_menipis(PDO $pdo): array
{
    return $pdo->query(
        "SELECT b.id_barang, b.kode_barang, b.nama_barang, b.stok, b.stok_minimum, b.satuan, l.nama_lokasi
         FROM barang b JOIN lokasi l ON b.id_lokasi = l.id_lokasi
         WHERE b.stok <= b.stok_minimum
         ORDER BY (b.stok - b.stok_minimum), b.nama_barang"
    )->fetchAll();
}

function transaksi_terakhir(PDO $pdo, int $batas = 6): array
{
    $stmt = $pdo->prepare("SELECT * FROM v_riwayat_transaksi
                           ORDER BY tanggal DESC, id_transaksi DESC LIMIT :n");
    $stmt->bindValue(':n', $batas, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function barang_per_kategori(PDO $pdo): array
{
    return $pdo->query(
        "SELECT k.nama_kategori, COUNT(b.id_barang) AS jumlah, COALESCE(SUM(b.stok * b.harga), 0) AS nilai
         FROM kategori k LEFT JOIN barang b ON b.id_kategori = k.id_kategori
         GROUP BY k.id_kategori ORDER BY nilai DESC"
    )->fetchAll();
}

// Laporan stok: posisi stok semua barang beserta nilainya
function laporan_stok(PDO $pdo, int $idKategori = 0): array
{
    $sql = "SELECT b.*, k.nama_kategori, l.nama_lokasi, (b.stok * b.harga) AS nilai
            FROM barang b
            JOIN kategori k ON b.id_kategori = k.id_kategori
            JOIN lokasi l   ON b.id_lokasi   = l.id_lokasi";
    $params = [];
    if ($idKategori > 0) {
        $sql .= " WHERE b.id_kategori = :k";
        $params[':k'] = $idKategori;
    }
    $sql .= " ORDER BY k.nama_kategori, b.kode_barang";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Laporan transaksi: mengambil dari VIEW v_riwayat_transaksi
function laporan_transaksi(PDO $pdo, string $dari, string $sampai, string $jenis = ''): array
{
    $sql = "SELECT * FROM v_riwayat_transaksi WHERE tanggal BETWEEN :dari AND :sampai";
    $params = [':dari' => $dari, ':sampai' => $sampai];
    if ($jenis === 'Masuk' || $jenis === 'Keluar') {
        $sql .= " AND jenis = :j";
        $params[':j'] = $jenis;
    }
    $sql .= " ORDER BY tanggal, jenis, id_transaksi";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
