<?php
$id = $_GET['ID'];
$row = $db->get_row("SELECT * FROM tb_kriteria WHERE kode_kriteria='$id'");
?>
<form method="post" action="aksi.php?act=kriteria_ubah&ID=<?= $id ?>">
    <input type="hidden" name="m" value="kriteria_ubah">
    
    <div class="form-group">
        <label>Kode Kriteria</label>
        <input class="form-control" name="kode" value="<?= $row->kode_kriteria ?>" readonly>
    </div>

    <div class="form-group">
        <label>Nama Kriteria</label>
        <input class="form-control" name="nama" value="<?= $row->nama_kriteria ?>" required>
    </div>

    <div class="form-group">
        <label>Atribut</label>
        <select class="form-control" name="atribut" required>
            <option value="benefit" <?= ($row->atribut == 'benefit') ? 'selected' : '' ?>>Benefit</option>
            <option value="cost" <?= ($row->atribut == 'cost') ? 'selected' : '' ?>>Cost</option>
        </select>
    </div>

    <button class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Simpan</button>
    <a href="?m=kriteria" class="btn btn-danger"><span class="glyphicon glyphicon-arrow-left"></span> Kembali</a>
</form>