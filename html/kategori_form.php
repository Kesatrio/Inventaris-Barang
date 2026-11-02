<?php
require_once __DIR__ . '/../php/kategori_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$id   = (int)($_GET['id'] ?? 0);
$edit = $id > 0;
$row  = $edit ? ambil_satu_kategori($pdo, $id) : null;
if ($edit && !$row) {
    kembali('kategori_list.php', 'error', 'Kategori tidak ditemukan.');
}
$d = ambil_old() ?? $row ?? ['nama_kategori' => ''];

tampil_header(($edit ? 'Ubah' : 'Tambah') . ' Kategori', 'kategori', true);
?>
<form method="post" action="../php/kategori_aksi.php" class="form">
    <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
    <?php if ($edit): ?><input type="hidden" name="id_kategori" value="<?= $id ?>"><?php endif; ?>

    <label>Nama Kategori
        <input type="text" name="nama_kategori" maxlength="100" required autofocus value="<?= e($d['nama_kategori']) ?>">
    </label>

    <div class="tombol">
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
        <a class="btn" href="kategori_list.php">Batal</a>
    </div>
</form>
<?php tampil_footer(); ?>
