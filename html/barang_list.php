<?php
require_once __DIR__ . '/../php/barang_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$cari     = trim($_GET['cari'] ?? '');
$kategori = (int)($_GET['kategori'] ?? 0);
$menipis  = isset($_GET['menipis']);

$barang         = ambil_semua_barang($pdo, $cari, $kategori, $menipis);
$daftarKategori = ambil_kategori($pdo);

tampil_header('Data Barang', 'barang');
?>
<div class="toolbar">
    <form method="get" class="cari">
        <input type="text" name="cari" placeholder="Cari kode atau nama barang" value="<?= e($cari) ?>">
        <select name="kategori">
            <option value="0">Semua kategori</option>
            <?php foreach ($daftarKategori as $k): ?>
                <option value="<?= (int)$k['id_kategori'] ?>" <?= $kategori === (int)$k['id_kategori'] ? 'selected' : '' ?>>
                    <?= e($k['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label class="cek"><input type="checkbox" name="menipis" <?= $menipis ? 'checked' : '' ?>> Stok menipis</label>
        <button type="submit">Filter</button>
        <?php if ($cari !== '' || $kategori || $menipis): ?>
            <a class="btn" href="barang_list.php">Reset</a>
        <?php endif; ?>
    </form>
    <a class="btn btn-primary" href="barang_form.php">+ Tambah Barang</a>
</div>

<div class="tabel-wrap">
<table>
    <thead>
        <tr>
            <th>No</th><th>Kode</th><th>Nama Barang</th><th>Kategori</th>
            <th>Lokasi</th><th>Stok</th><th>Harga</th><th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$barang): ?>
        <tr><td colspan="8" class="kosong">Tidak ada data barang.</td></tr>
    <?php endif; ?>
    <?php foreach ($barang as $i => $b): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($b['kode_barang']) ?></td>
            <td><?= e($b['nama_barang']) ?></td>
            <td><?= e($b['nama_kategori']) ?></td>
            <td><?= e($b['nama_lokasi']) ?></td>
            <td>
                <?= e($b['stok']) ?> <?= e($b['satuan']) ?>
                <?php if ($b['stok'] <= $b['stok_minimum']): ?>
                    <span class="badge merah">Menipis</span>
                <?php endif; ?>
            </td>
            <td><?= rupiah($b['harga']) ?></td>
            <td class="aksi">
                <a class="btn btn-kecil" href="barang_form.php?id=<?= (int)$b['id_barang'] ?>">Ubah</a>
                <form method="post" action="../php/barang_aksi.php"
                      onsubmit="return confirm('Yakin hapus barang ini?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id_barang" value="<?= (int)$b['id_barang'] ?>">
                    <button type="submit" class="btn btn-kecil btn-bahaya">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<p class="catatan">Total: <?= count($barang) ?> barang. Stok ditandai <b>Menipis</b> jika sama dengan atau di bawah stok minimum.</p>
<?php tampil_footer(); ?>
