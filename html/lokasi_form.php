<?php
require_once __DIR__ . '/../php/lokasi_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$id   = (int)($_GET['id'] ?? 0);
$edit = $id > 0;
$row  = $edit ? ambil_satu_lokasi($pdo, $id) : null;
if ($edit && !$row) {
    kembali('lokasi_list.php', 'error', 'Lokasi tidak ditemukan.');
}
$d = ambil_old() ?? $row ?? ['nama_lokasi' => '', 'keterangan' => ''];

tampil_header(($edit ? 'Ubah' : 'Tambah') . ' Lokasi', 'lokasi', true);
?>
<form method="post" action="../php/lokasi_aksi.php" class="form">
    <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
    <?php if ($edit): ?><input type="hidden" name="id_lokasi" value="<?= $id ?>"><?php endif; ?>

    <label>Nama Lokasi
        <input type="text" name="nama_lokasi" maxlength="100" required autofocus value="<?= e($d['nama_lokasi']) ?>">
    </label>
    <label>Keterangan <span class="opsional">(opsional)</span>
        <input type="text" name="keterangan" maxlength="255" placeholder="Misal: Lantai 1" value="<?= e($d['keterangan'] ?? '') ?>">
    </label>

    <div class="tombol">
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
        <a class="btn" href="lokasi_list.php">Batal</a>
    </div>
</form>
<?php tampil_footer(); ?>
