<?php
require_once __DIR__ . '/../php/barang_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit = $id > 0;

$barang = $edit ? ambil_barang($pdo, $id) : null;
if ($edit && !$barang) {
    kembali('barang_list.php', 'error', 'Barang tidak ditemukan.');
}

$d = ambil_old() ?? $barang ?? [
    'kode_barang' => '', 'nama_barang' => '', 'satuan' => 'pcs',
    'stok' => 0, 'stok_minimum' => 5, 'harga' => 0, 'id_kategori' => '', 'id_lokasi' => '',
];

$daftarKategori = ambil_kategori($pdo);
$daftarLokasi   = ambil_lokasi($pdo);

tampil_header(($edit ? 'Ubah' : 'Tambah') . ' Barang', 'barang', true);
?>
<form method="post" action="../php/barang_aksi.php" class="form">
    <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
    <?php if ($edit): ?>
        <input type="hidden" name="id_barang" value="<?= $id ?>">
    <?php endif; ?>

    <div class="baris">
        <label>Kode Barang
            <input type="text" name="kode_barang" maxlength="30" required placeholder="BRG-007" value="<?= e($d['kode_barang']) ?>">
        </label>
        <label>Satuan
            <input type="text" name="satuan" maxlength="20" required placeholder="pcs / unit / rim" value="<?= e($d['satuan']) ?>">
        </label>
    </div>

    <label>Nama Barang
        <input type="text" name="nama_barang" maxlength="150" required value="<?= e($d['nama_barang']) ?>">
    </label>

    <div class="baris">
        <label>Kategori
            <select name="id_kategori" required>
                <option value="">-- Pilih kategori --</option>
                <?php foreach ($daftarKategori as $k): ?>
                    <option value="<?= (int)$k['id_kategori'] ?>"
                        <?= (int)$d['id_kategori'] === (int)$k['id_kategori'] ? 'selected' : '' ?>>
                        <?= e($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Lokasi
            <select name="id_lokasi" required>
                <option value="">-- Pilih lokasi --</option>
                <?php foreach ($daftarLokasi as $l): ?>
                    <option value="<?= (int)$l['id_lokasi'] ?>"
                        <?= (int)$d['id_lokasi'] === (int)$l['id_lokasi'] ? 'selected' : '' ?>>
                        <?= e($l['nama_lokasi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <div class="baris">
        <label><?= $edit ? 'Stok Saat Ini' : 'Stok Awal' ?>
            <?php if ($edit): ?>
                <input type="number" value="<?= e($barang['stok']) ?>" disabled>
            <?php else: ?>
                <input type="number" name="stok" min="0" step="1" required value="<?= e($d['stok']) ?>">
            <?php endif; ?>
        </label>
        <label>Stok Minimum
            <input type="number" name="stok_minimum" min="0" step="1" required value="<?= e($d['stok_minimum']) ?>">
        </label>
        <label>Harga (Rp)
            <input type="number" name="harga" min="0" step="1" required value="<?= e((int)$d['harga']) ?>">
        </label>
    </div>
    <?php if ($edit): ?>
        <p class="catatan">Stok tidak diubah di sini. Gunakan menu <b>Barang Masuk</b> atau <b>Barang Keluar</b> agar riwayatnya tercatat.</p>
    <?php endif; ?>

    <div class="tombol">
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
        <a class="btn" href="barang_list.php">Batal</a>
    </div>
</form>
<?php tampil_footer(); ?>
