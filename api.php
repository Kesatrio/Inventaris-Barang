<?php
require __DIR__ . '/koneksi.php';
header('Content-Type: application/json; charset=utf-8');

function send($d, $c = 200) { http_response_code($c); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

$pk = ['kategori' => 'id_kategori', 'lokasi' => 'id_lokasi', 'supplier' => 'id_supplier',
       'barang' => 'id_barang', 'barang_masuk' => 'id_masuk', 'barang_keluar' => 'id_keluar'];
$cols = [
  'kategori'      => ['nama_kategori'],
  'lokasi'        => ['nama_lokasi', 'keterangan'],
  'supplier'      => ['nama_supplier', 'telepon', 'alamat'],
  'barang'        => ['kode_barang', 'nama_barang', 'id_kategori', 'id_lokasi', 'satuan', 'stok', 'harga'],
  'barang_masuk'  => ['tanggal', 'id_barang', 'id_supplier', 'jumlah'],
  'barang_keluar' => ['tanggal', 'id_barang', 'jumlah', 'keterangan'],
];

function pesan_db(PDOException $e): string {
    $k = $e->errorInfo[1] ?? 0;
    if ($k == 1062) return 'Gagal: data kembar, nilai tersebut sudah ada.';
    if ($k == 1451) return 'Gagal: data masih dipakai tabel lain.';
    if ($k == 1452) return 'Gagal: pilihan yang dipilih tidak valid.';
    if ($k == 1048) return 'Gagal: ada isian wajib yang kosong.';
    if ($k == 3819 || $k == 4025) return 'Gagal: nilai tidak valid (stok/harga/jumlah tidak boleh negatif atau nol).';
    return 'Terjadi kesalahan pada database.';
}

try {
  if (isset($_GET['laporan'])) {
    $q = [
      'Daftar Barang Lengkap (JOIN 3 tabel)' =>
        "SELECT b.kode_barang AS Kode, b.nama_barang AS Barang, k.nama_kategori AS Kategori, l.nama_lokasi AS Lokasi,
                CONCAT(b.stok, ' ', b.satuan) AS Stok, b.harga AS `Harga (Rp)`
         FROM barang b JOIN kategori k ON b.id_kategori = k.id_kategori
         JOIN lokasi l ON b.id_lokasi = l.id_lokasi
         ORDER BY k.nama_kategori, b.nama_barang",
      'Jumlah Barang dan Total Stok per Kategori (COUNT + SUM + GROUP BY)' =>
        "SELECT k.nama_kategori AS Kategori, COUNT(b.id_barang) AS `Jumlah Barang`, COALESCE(SUM(b.stok), 0) AS `Total Stok`
         FROM kategori k LEFT JOIN barang b ON b.id_kategori = k.id_kategori
         GROUP BY k.id_kategori, k.nama_kategori ORDER BY k.nama_kategori",
      'Nilai Persediaan per Kategori (SUM)' =>
        "SELECT k.nama_kategori AS Kategori, COALESCE(SUM(b.stok * b.harga), 0) AS `Nilai Persediaan (Rp)`
         FROM kategori k LEFT JOIN barang b ON b.id_kategori = k.id_kategori
         GROUP BY k.id_kategori, k.nama_kategori ORDER BY 2 DESC",
      'Total Barang Masuk per Barang (JOIN + SUM)' =>
        "SELECT b.nama_barang AS Barang, COUNT(m.id_masuk) AS `Jumlah Transaksi`, SUM(m.jumlah) AS `Total Masuk`
         FROM barang_masuk m JOIN barang b ON m.id_barang = b.id_barang
         GROUP BY b.id_barang, b.nama_barang ORDER BY 3 DESC",
      'Total Barang Keluar per Barang (JOIN + SUM)' =>
        "SELECT b.nama_barang AS Barang, COUNT(k.id_keluar) AS `Jumlah Transaksi`, SUM(k.jumlah) AS `Total Keluar`
         FROM barang_keluar k JOIN barang b ON k.id_barang = b.id_barang
         GROUP BY b.id_barang, b.nama_barang ORDER BY 3 DESC",
      'Total Pasokan per Supplier (JOIN + SUM)' =>
        "SELECT s.nama_supplier AS Supplier, COUNT(m.id_masuk) AS `Jumlah Transaksi`, SUM(m.jumlah) AS `Total Dipasok`
         FROM supplier s JOIN barang_masuk m ON m.id_supplier = s.id_supplier
         GROUP BY s.id_supplier, s.nama_supplier ORDER BY 3 DESC",
      'Barang Stok Menipis (stok 10 atau kurang)' =>
        "SELECT kode_barang AS Kode, nama_barang AS Barang, CONCAT(stok, ' ', satuan) AS Stok
         FROM barang WHERE stok <= 10 ORDER BY stok",
    ];
    $out = [];
    foreach ($q as $title => $sql) $out[] = ['title' => $title, 'rows' => $pdo->query($sql)->fetchAll()];
    send($out);
  }

  $t = $_GET['t'] ?? '';
  if (!isset($pk[$t])) send(['error' => 'Tabel tidak valid'], 400);
  $key = $pk[$t]; $c = $cols[$t]; $m = $_SERVER['REQUEST_METHOD'];
  $id  = (int)($_GET['id'] ?? 0);
  $b   = json_decode(file_get_contents('php://input'), true) ?? [];
  $transaksi = ($t === 'barang_masuk' || $t === 'barang_keluar');

  if ($m === 'GET') {
    $urut = $transaksi ? "tanggal DESC, $key DESC" : $key;
    send($pdo->query("SELECT * FROM $t ORDER BY $urut")->fetchAll());
  }

  if ($transaksi) {
    $masuk = $t === 'barang_masuk';
    if ($m === 'PUT') send(['error' => 'Catatan transaksi tidak bisa diubah. Hapus lalu catat ulang.'], 405);

    $stokKunci = function (int $idB) use ($pdo): int {
        $s = $pdo->prepare("SELECT stok FROM barang WHERE id_barang = ? FOR UPDATE");
        $s->execute([$idB]);
        $r = $s->fetch();
        if (!$r) throw new RuntimeException('Barang tidak ditemukan.');
        return (int)$r['stok'];
    };
    $ubahStok = fn(int $idB, int $selisih) =>
        $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?")->execute([$selisih, $idB]);

    try {
      $pdo->beginTransaction();

      if ($m === 'POST') {
        $tanggal = trim((string)($b['tanggal'] ?? ''));
        $idB     = (int)($b['id_barang'] ?? 0);
        $idS     = (int)($b['id_supplier'] ?? 0);
        $jumlah  = trim((string)($b['jumlah'] ?? ''));
        $ket     = trim((string)($b['keterangan'] ?? ''));
        $tg = DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$tg || $tg->format('Y-m-d') !== $tanggal)  throw new RuntimeException('Tanggal tidak valid.');
        if ($idB <= 0)                                  throw new RuntimeException('Barang wajib dipilih.');
        if ($masuk && $idS <= 0)                        throw new RuntimeException('Supplier wajib dipilih.');
        if (!ctype_digit($jumlah) || (int)$jumlah <= 0) throw new RuntimeException('Jumlah harus berupa angka bulat lebih dari 0.');
        $jumlah = (int)$jumlah;

        $stok = $stokKunci($idB);
        if ($masuk) {
            $pdo->prepare("INSERT INTO barang_masuk (tanggal, id_barang, id_supplier, jumlah) VALUES (?, ?, ?, ?)")
                ->execute([$tanggal, $idB, $idS, $jumlah]);
            $ubahStok($idB, $jumlah);
        } else {
            if ($stok < $jumlah) throw new RuntimeException("Stok tidak cukup. Stok tersedia: $stok.");
            $pdo->prepare("INSERT INTO barang_keluar (tanggal, id_barang, jumlah, keterangan) VALUES (?, ?, ?, ?)")
                ->execute([$tanggal, $idB, $jumlah, $ket !== '' ? $ket : null]);
            $ubahStok($idB, -$jumlah);
        }
        $pdo->commit();
        send(['ok' => 1], 201);
      }

      if ($m === 'DELETE') {
        $s = $pdo->prepare("SELECT * FROM $t WHERE $key = ? FOR UPDATE");
        $s->execute([$id]);
        $r = $s->fetch();
        if (!$r) throw new RuntimeException('Data tidak ditemukan.');
        $stok = $stokKunci((int)$r['id_barang']);
        if ($masuk && $stok < (int)$r['jumlah']) {
            throw new RuntimeException("Tidak bisa dihapus: stok saat ini ($stok) lebih kecil dari jumlah masuk ({$r['jumlah']}), artinya sebagian barang sudah keluar.");
        }
        $pdo->prepare("DELETE FROM $t WHERE $key = ?")->execute([$id]);
        $ubahStok((int)$r['id_barang'], $masuk ? -(int)$r['jumlah'] : (int)$r['jumlah']);
        $pdo->commit();
        send(['ok' => 1]);
      }

      $pdo->rollBack();
      send(['error' => 'Method tidak didukung'], 405);
    } catch (RuntimeException $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      send(['error' => $e->getMessage()], 409);
    }
  }

  if ($m === 'DELETE') { $pdo->prepare("DELETE FROM $t WHERE $key = ?")->execute([$id]); send(['ok' => 1]); }

  if ($m === 'PUT' && $t === 'barang') $c = array_values(array_diff($c, ['stok']));
  $v = array_map(fn($k) => (($b[$k] ?? '') === '') ? null : $b[$k], $c);

  if ($m === 'POST') {
    $ph = implode(',', array_fill(0, count($c), '?'));
    $pdo->prepare("INSERT INTO $t (" . implode(',', $c) . ") VALUES ($ph)")->execute($v);
    send(['ok' => 1, 'id' => $pdo->lastInsertId()], 201);
  }
  if ($m === 'PUT') {
    $set = implode(',', array_map(fn($k) => "$k = ?", $c));
    $v[] = $id;
    $pdo->prepare("UPDATE $t SET $set WHERE $key = ?")->execute($v);
    send(['ok' => 1]);
  }
  send(['error' => 'Method tidak didukung'], 405);

} catch (PDOException $e) {
  if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
  send(['error' => pesan_db($e)], 409);
}
