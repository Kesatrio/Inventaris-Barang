<?php
require_once __DIR__ . '/../php/masuk_model.php';
require_once __DIR__ . '/../php/barang_model.php';
require_once __DIR__ . '/../php/supplier_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$id   = (int)($_GET['id'] ?? 0);
$edit = $id > 0;
$row  = $edit ? ambil_satu_masuk($pdo, $id) : null;
if ($edit && !$row) {
    kembali('masuk_list.php', 'error', 'Data barang masuk tidak ditemukan.');
}
$d = ambil_old() ?? $row ?? [
    'tanggal' => date('Y-m-d'), 'id_barang' => (int)($_GET['barang'] ?? 0),
    'id_supplier' => 0, 'jumlah' => '', 'keterangan' => '',
];

$daftarBarang   = ambil_pilihan_barang($pdo);
$daftarSupplier = ambil_pilihan_supplier($pdo);

tampil_header(($edit ? 'Ubah' : 'Catat') . ' Barang Masuk', 'masuk', true);
?>
<form method="post" action="../php/masuk_aksi.php" class="form">
    <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
    <?php if ($edit): ?><input type="hidden" name="id_masuk" value="<?= $id ?>"><?php endif; ?>

    <label>Tanggal
        <input type="date" name="tanggal" required max="<?= date('Y-m-d') ?>" value="<?= e($d['tanggal']) ?>">
    </label>

    <label>Barang
        <select name="id_barang" required>
            <option value="">-- Pilih barang --</option>
            <?php foreach ($daftarBarang as $b): ?>
                <option value="<?= (int)$b['id_barang'] ?>" <?= (int)$d['id_barang'] === (int)$b['id_barang'] ? 'selected' : '' ?>>
                    <?= e($b['kode_barang']) ?> &ndash; <?= e($b['nama_barang']) ?> (stok: <?= (int)$b['stok'] ?> <?= e($b['satuan']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Supplier
        <select name="id_supplier" required>
            <option value="">-- Pilih supplier --</option>
            <?php foreach ($daftarSupplier as $s): ?>
                <option value="<?= (int)$s['id_supplier'] ?>" <?= (int)$d['id_supplier'] === (int)$s['id_supplier'] ? 'selected' : '' ?>>
                    <?= e($s['nama_supplier']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Jumlah
        <input type="number" name="jumlah" min="1" step="1" required value="<?= e($d['jumlah']) ?>">
    </label>

    <label>Keterangan <span class="opsional">(opsional)</span>
        <input type="text" name="keterangan" maxlength="255" value="<?= e($d['keterangan'] ?? '') ?>">
    </label>

    <p class="catatan">Stok barang akan bertambah otomatis sesuai jumlah yang dicatat.</p>

    <div class="tombol">
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
        <a class="btn" href="masuk_list.php">Batal</a>
    </div>
</form>
<?php tampil_footer(); ?>
