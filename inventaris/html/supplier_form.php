<?php
require_once __DIR__ . '/../php/supplier_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$id   = (int)($_GET['id'] ?? 0);
$edit = $id > 0;
$row  = $edit ? ambil_satu_supplier($pdo, $id) : null;
if ($edit && !$row) {
    kembali('supplier_list.php', 'error', 'Supplier tidak ditemukan.');
}
$d = ambil_old() ?? $row ?? ['nama_supplier' => '', 'telepon' => '', 'alamat' => ''];

tampil_header(($edit ? 'Ubah' : 'Tambah') . ' Supplier', 'supplier', true);
?>
<form method="post" action="../php/supplier_aksi.php" class="form">
    <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
    <?php if ($edit): ?><input type="hidden" name="id_supplier" value="<?= $id ?>"><?php endif; ?>

    <label>Nama Supplier
        <input type="text" name="nama_supplier" maxlength="100" required autofocus value="<?= e($d['nama_supplier']) ?>">
    </label>
    <label>Telepon <span class="opsional">(opsional)</span>
        <input type="text" name="telepon" maxlength="20" placeholder="08xxxxxxxxxx" value="<?= e($d['telepon'] ?? '') ?>">
    </label>
    <label>Alamat <span class="opsional">(opsional)</span>
        <input type="text" name="alamat" maxlength="255" value="<?= e($d['alamat'] ?? '') ?>">
    </label>

    <div class="tombol">
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Simpan' ?></button>
        <a class="btn" href="supplier_list.php">Batal</a>
    </div>
</form>
<?php tampil_footer(); ?>
