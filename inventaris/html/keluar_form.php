<?php
require_once __DIR__ . '/../php/keluar_model.php';
require_once __DIR__ . '/../php/barang_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$id   = (int)($_GET['id'] ?? 0);
$edit = $id > 0;
$row  = $edit ? ambil_satu_keluar($pdo, $id) : null;
if ($edit && !$row) {
    kembali('keluar_list.php', 'error', 'Data barang keluar tidak ditemukan.');
}
$d = ambil_old() ?? $row ?? [
    'tanggal' => date('Y-m-d'), 'id_barang' => 0, 'jumlah' => '', 'keterangan' => '',
];

$daftarBarang = ambil_pilihan_barang($pdo);

tampil_header(($edit ? 'Ubah' : 'Catat') . ' Barang Keluar', 'keluar', true);
?>
<form method="post" action="../php/keluar_aksi.php" class="form">
    <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
    <?php if ($edit): ?><input type="hidden" name="id_keluar" value="<?= $id ?>"><?php endif; ?>

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

    <label>Jumlah
        <input type="number" name="jumlah" min="1" step="1" required value="<?= e($d['jumlah']) ?>">
    </label>

    <label>Keterangan / Keperluan <span class="opsional">(opsional)</span>
        <input type="text" name="keterangan" maxlength="255" placeholder="Misal: Dipakai bagian keuangan" value="<?= e($d['keterangan'] ?? '') ?>">
    </label>

    <p class="catatan">Stok barang akan berkurang otomatis. Transaksi ditolak jika stok tidak mencukupi.</p>

    <div class="tombol">
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
        <a class="btn" href="keluar_list.php">Batal</a>
    </div>
</form>
<?php tampil_footer(); ?>
