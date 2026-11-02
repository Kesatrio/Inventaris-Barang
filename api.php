<?php
$host = 'localhost';
$db   = 'db_inventaris_tubes';
$user = 'root';
$pass = '';
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function kirim(array $d, int $kode = 200): void
{
    http_response_code($kode);
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}
function sukses($data = null, string $pesan = ''): void { kirim(['ok' => true, 'data' => $data, 'pesan' => $pesan]); }
function gagal(string $pesan, int $kode = 422): void   { kirim(['ok' => false, 'pesan' => $pesan], $kode); }

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    gagal('Koneksi database gagal. Pastikan MySQL menyala dan database sudah diimport (cek bagian atas api.php).', 500);
}
set_exception_handler(fn($e) => gagal('Terjadi kesalahan pada server.', 500));

function pesan_db(PDOException $e, string $judul, string $kembar = ''): string
{
    $k = $e->errorInfo[1] ?? 0;
    if ($k == 1062) return $kembar ?: "$judul dengan nama tersebut sudah ada.";
    if ($k == 1451) return "$judul tidak bisa dihapus karena masih dipakai oleh data lain.";
    if ($k == 1452) return 'Pilihan yang dipilih tidak valid.';
    return 'Terjadi kesalahan pada database.';
}

$MASTER = [
    'kategori' => ['Kategori', 'id_kategori', 'nama_kategori', [
        'nama_kategori' => ['Nama Kategori', true, 100],
    ]],
    'lokasi' => ['Lokasi', 'id_lokasi', 'nama_lokasi', [
        'nama_lokasi' => ['Nama Lokasi', true, 100],
        'keterangan'  => ['Keterangan', false, 255],
    ]],
    'supplier' => ['Supplier', 'id_supplier', 'nama_supplier', [
        'nama_supplier' => ['Nama Supplier', true, 100],
        'telepon'       => ['Telepon', false, 20],
        'alamat'        => ['Alamat', false, 255],
    ]],
];

$t     = $_GET['t'] ?? '';
$post  = $_SERVER['REQUEST_METHOD'] === 'POST';
$in    = $post ? (json_decode(file_get_contents('php://input'), true) ?: []) : [];
$aksi  = $in['aksi'] ?? '';
$id    = (int)($in['id'] ?? 0);

if ($t === 'opsi') {
    sukses([
        'kategori' => $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll(),
        'lokasi'   => $pdo->query("SELECT * FROM lokasi ORDER BY nama_lokasi")->fetchAll(),
        'supplier' => $pdo->query("SELECT * FROM supplier ORDER BY nama_supplier")->fetchAll(),
        'barang'   => $pdo->query("SELECT id_barang, kode_barang, nama_barang, satuan, stok FROM barang ORDER BY nama_barang")->fetchAll(),
    ]);
}

if (isset($MASTER[$t])) {
    [$judul, $pk, $urut, $kolom] = $MASTER[$t];

    if (!$post) sukses($pdo->query("SELECT * FROM $t ORDER BY $urut")->fetchAll());

    if ($aksi === 'hapus') {
        try { $pdo->prepare("DELETE FROM $t WHERE $pk = ?")->execute([$id]); }
        catch (PDOException $e) { gagal(pesan_db($e, $judul)); }
        sukses(null, "$judul berhasil dihapus.");
    }

    if ($aksi === 'tambah' || $aksi === 'ubah') {
        $d = []; $err = [];
        foreach ($kolom as $k => [$label, $wajib, $maks]) {
            $v = trim((string)($in[$k] ?? ''));
            if ($wajib && $v === '')      $err[] = "$label wajib diisi.";
            if (mb_strlen($v) > $maks)    $err[] = "$label maksimal $maks karakter.";
            $d[$k] = $v === '' ? null : $v;
        }
        if ($aksi === 'ubah' && $id <= 0) $err[] = 'Data tidak valid.';
        if ($err) gagal(implode(' ', $err));

        $nama = array_keys($d);
        try {
            if ($aksi === 'tambah') {
                $sql = "INSERT INTO $t (" . implode(',', $nama) . ") VALUES (" . implode(',', array_fill(0, count($nama), '?')) . ")";
                $pdo->prepare($sql)->execute(array_values($d));
            } else {
                $sql = "UPDATE $t SET " . implode(',', array_map(fn($k) => "$k = ?", $nama)) . " WHERE $pk = ?";
                $pdo->prepare($sql)->execute([...array_values($d), $id]);
            }
        } catch (PDOException $e) { gagal(pesan_db($e, $judul)); }
        sukses(null, "$judul berhasil " . ($aksi === 'tambah' ? 'ditambahkan.' : 'diperbarui.'));
    }
    gagal('Aksi tidak dikenal.', 400);
}

if ($t === 'barang') {
    if (!$post) {
        sukses($pdo->query(
            "SELECT b.*, k.nama_kategori, l.nama_lokasi
             FROM barang b
             JOIN kategori k ON b.id_kategori = k.id_kategori
             JOIN lokasi l   ON b.id_lokasi   = l.id_lokasi
             ORDER BY b.id_barang DESC"
        )->fetchAll());
    }

    if ($aksi === 'hapus') {
        try { $pdo->prepare("DELETE FROM barang WHERE id_barang = ?")->execute([$id]); }
        catch (PDOException $e) { gagal('Barang tidak bisa dihapus karena sudah punya riwayat transaksi.'); }
        sukses(null, 'Barang berhasil dihapus.');
    }

    if ($aksi === 'tambah' || $aksi === 'ubah') {
        $d = [
            'kode_barang' => trim((string)($in['kode_barang'] ?? '')),
            'nama_barang' => trim((string)($in['nama_barang'] ?? '')),
            'satuan'      => trim((string)($in['satuan'] ?? '')),
            'stok'        => trim((string)($in['stok'] ?? '0')),
            'harga'       => trim((string)($in['harga'] ?? '0')),
            'id_kategori' => (int)($in['id_kategori'] ?? 0),
            'id_lokasi'   => (int)($in['id_lokasi'] ?? 0),
        ];
        if ($aksi === 'ubah') {
            // Stok hanya boleh berubah lewat Barang Masuk / Keluar, jadi pakai stok yang tersimpan
            $s = $pdo->prepare("SELECT stok FROM barang WHERE id_barang = ?"); $s->execute([$id]);
            $lama = $s->fetch();
            if (!$lama) gagal('Barang tidak ditemukan.', 404);
            $d['stok'] = $lama['stok'];
        }

        $err = [];
        if ($d['kode_barang'] === '') $err[] = 'Kode barang wajib diisi.';
        if ($d['nama_barang'] === '') $err[] = 'Nama barang wajib diisi.';
        if ($d['satuan'] === '')      $err[] = 'Satuan wajib diisi.';
        if (!ctype_digit((string)$d['stok'])) $err[] = 'Stok harus berupa angka bulat 0 atau lebih.';
        if (!is_numeric($d['harga']) || $d['harga'] < 0) $err[] = 'Harga harus berupa angka 0 atau lebih.';
        if ($d['id_kategori'] <= 0) $err[] = 'Kategori wajib dipilih.';
        if ($d['id_lokasi'] <= 0)   $err[] = 'Lokasi wajib dipilih.';
        if ($err) gagal(implode(' ', $err));

        try {
            if ($aksi === 'tambah') {
                $pdo->prepare("INSERT INTO barang (kode_barang, nama_barang, satuan, stok, harga, id_kategori, id_lokasi)
                               VALUES (?, ?, ?, ?, ?, ?, ?)")->execute(array_values($d));
            } else {
                $pdo->prepare("UPDATE barang SET kode_barang=?, nama_barang=?, satuan=?, stok=?, harga=?, id_kategori=?, id_lokasi=?
                               WHERE id_barang=?")->execute([...array_values($d), $id]);
            }
        } catch (PDOException $e) { gagal(pesan_db($e, 'Barang', 'Kode barang sudah dipakai barang lain.')); }
        sukses(null, 'Barang berhasil ' . ($aksi === 'tambah' ? 'ditambahkan.' : 'diperbarui.'));
    }
    gagal('Aksi tidak dikenal.', 400);
}

if ($t === 'masuk' || $t === 'keluar') {
    $masuk = $t === 'masuk';

    if (!$post) {
        $sql = $masuk
            ? "SELECT m.id_masuk AS id, m.tanggal, m.jumlah, b.kode_barang, b.nama_barang, b.satuan, s.nama_supplier
               FROM barang_masuk m JOIN barang b ON m.id_barang = b.id_barang
               JOIN supplier s ON m.id_supplier = s.id_supplier
               ORDER BY m.tanggal DESC, m.id_masuk DESC"
            : "SELECT k.id_keluar AS id, k.tanggal, k.jumlah, k.keterangan, b.kode_barang, b.nama_barang, b.satuan
               FROM barang_keluar k JOIN barang b ON k.id_barang = b.id_barang
               ORDER BY k.tanggal DESC, k.id_keluar DESC";
        sukses($pdo->query($sql)->fetchAll());
    }

    $tabel = $masuk ? 'barang_masuk' : 'barang_keluar';
    $pk    = $masuk ? 'id_masuk' : 'id_keluar';

    $kunciStok = function (int $idBarang) use ($pdo): int {
        $s = $pdo->prepare("SELECT stok FROM barang WHERE id_barang = ? FOR UPDATE");
        $s->execute([$idBarang]);
        $r = $s->fetch();
        if (!$r) throw new RuntimeException('Barang tidak ditemukan.');
        return (int)$r['stok'];
    };
    $ubahStok = fn(int $idBarang, int $selisih) =>
        $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?")->execute([$selisih, $idBarang]);

    try {
        $pdo->beginTransaction();

        if ($aksi === 'tambah') {
            $tanggal = trim((string)($in['tanggal'] ?? ''));
            $idB     = (int)($in['id_barang'] ?? 0);
            $idS     = (int)($in['id_supplier'] ?? 0);
            $jumlah  = trim((string)($in['jumlah'] ?? ''));
            $ket     = trim((string)($in['keterangan'] ?? ''));

            $tg = DateTime::createFromFormat('Y-m-d', $tanggal);
            if (!$tg || $tg->format('Y-m-d') !== $tanggal)       throw new RuntimeException('Tanggal tidak valid.');
            if ($idB <= 0)                                       throw new RuntimeException('Barang wajib dipilih.');
            if ($masuk && $idS <= 0)                             throw new RuntimeException('Supplier wajib dipilih.');
            if (!ctype_digit($jumlah) || (int)$jumlah <= 0)      throw new RuntimeException('Jumlah harus berupa angka bulat lebih dari 0.');
            $jumlah = (int)$jumlah;

            $stok = $kunciStok($idB);
            if ($masuk) {
                $pdo->prepare("INSERT INTO barang_masuk (tanggal, jumlah, id_barang, id_supplier) VALUES (?, ?, ?, ?)")
                    ->execute([$tanggal, $jumlah, $idB, $idS]);
                $ubahStok($idB, $jumlah);
            } else {
                if ($stok < $jumlah) throw new RuntimeException("Stok tidak cukup. Stok tersedia: $stok.");
                $pdo->prepare("INSERT INTO barang_keluar (tanggal, jumlah, keterangan, id_barang) VALUES (?, ?, ?, ?)")
                    ->execute([$tanggal, $jumlah, $ket !== '' ? $ket : null, $idB]);
                $ubahStok($idB, -$jumlah);
            }
            $pdo->commit();
            sukses(null, $masuk ? 'Barang masuk dicatat, stok bertambah.' : 'Barang keluar dicatat, stok berkurang.');
        }

        if ($aksi === 'hapus') {
            $s = $pdo->prepare("SELECT * FROM $tabel WHERE $pk = ? FOR UPDATE");
            $s->execute([$id]);
            $r = $s->fetch();
            if (!$r) throw new RuntimeException('Data tidak ditemukan.');

            $stok = $kunciStok((int)$r['id_barang']);
            if ($masuk && $stok < (int)$r['jumlah']) {
                throw new RuntimeException("Tidak bisa dihapus: stok saat ini ($stok) lebih kecil dari jumlah masuk ({$r['jumlah']}), artinya sebagian barang sudah keluar.");
            }
            $pdo->prepare("DELETE FROM $tabel WHERE $pk = ?")->execute([$id]);
            $ubahStok((int)$r['id_barang'], $masuk ? -(int)$r['jumlah'] : (int)$r['jumlah']);
            $pdo->commit();
            sukses(null, $masuk ? 'Catatan dihapus, stok dikurangi kembali.' : 'Catatan dihapus, stok dikembalikan.');
        }

        $pdo->rollBack();
        gagal('Aksi tidak dikenal.', 400);
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        gagal($e->getMessage());
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        gagal(pesan_db($e, 'Data'));
    }
}

gagal('Endpoint tidak dikenal.', 404);
