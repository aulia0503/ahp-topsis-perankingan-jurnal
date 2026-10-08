<form method="post" action="aksi.php?act=kriteria_tambah">
    <input type="hidden" name="m" value="kriteria_tambah">
    
    <div class="form-group">
        <label>Kode <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="kode" value="<?=set_value('kode')?>"/>
    </div>
    <div class="form-group">
        <label>Nama Kriteria <span class="text-danger">*</span></label>
        <input class="form-control" type="text" name="nama" value="<?=set_value('nama')?>"/>
    </div>
    <div class="form-group">
        <label>Atribut <span class="text-danger">*</span></label>
        <select class="form-control" name="atribut">
            <option value=""></option>
            <?=get_atribut_option(set_value('atribut'))?>
        </select>
    </div>
    <div class="form-group">
        <button class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Simpan</button>
        <a class="btn btn-danger" href="?m=kriteria"><span class="glyphicon glyphicon-arrow-left"></span> Kembali</a>
    </div>
</form>