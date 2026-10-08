<?php
// Mengambil ID dari URL
$id = $_GET['ID'];
// Mengambil data lama dari database
$row = $db->get_row("SELECT * FROM tb_alternatif WHERE kode_alternatif='$id'");
?>
<div class="page-header">
    <h1>Ubah Jurnal (Alternatif)</h1>
</div>
<div class="row">
    <div class="col-sm-12">
        <form method="post" action="aksi.php?act=alternatif_ubah">
            <input type="hidden" name="m" value="alternatif_ubah">
            
            <div class="panel panel-primary">
                <div class="panel-heading">Edit Data Jurnal</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Kode Jurnal (Otomatis)</label>
                                <input class="form-control" type="text" name="kode" value="<?=$row->kode_alternatif?>" readonly/>
                            </div>
                            <div class="form-group">
                                <label>Judul Jurnal <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="tittle" value="<?=$row->tittle?>" placeholder="Masukkan nama jurnal" required/>
                            </div>
                            <div class="form-group">
                                <label>Bidang <span class="text-danger">*</span></label>
                                <select class="form-control" name="id_query" required>
                                    <option value="">-- Pilih Bidang --</option>
                                    <?=get_query_option($row->id_query)?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Link Jurnal (URL)</label>
                                <input class="form-control" type="url" name="url_jurnal" value="<?=$row->url_jurnal?>" placeholder="https://journal.contoh.ac.id/..." />
                                <p class="help-block small">Masukkan URL lengkap website jurnal.</p>
                            </div>
                            <div class="form-group">
                                <label>Akreditasi</label>
                                <select class="form-control" name="akreditasi">
                                    <?php
                                    $opts = ['Sinta 1', 'Sinta 2', 'Sinta 3', 'Sinta 4', 'Sinta 5', 'Sinta 6'];
                                    foreach($opts as $opt): ?>
                                        <option value="<?=$opt?>" <?=($row->akreditasi==$opt)?'selected':''?>><?=$opt?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Impact Factor (Angka)</label>
                                <input class="form-control" type="text" name="impact" value="<?=$row->impact?>" placeholder="Contoh: 1.2"/>
                                <p class="help-block small">Gunakan titik (.) untuk desimal. Skor: >4.0 (5), 2.1-4.0 (4), 1.1-2.0 (3), dst.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>APC (Biaya Jurnal)</label>
                                <input class="form-control" type="text" name="APC" value="<?=$row->APC?>" placeholder="Contoh: 2000000"/>
                                <p class="help-block small">Isi 0 jika gratis (Skor 5). >4jt (Skor 1).</p>
                            </div>
                            <div class="form-group">
                                <label>Total Citations (Angka)</label>
                                <input class="form-control" type="text" name="google_citation" value="<?=$row->google_citation?>" placeholder="Contoh: 5162"/>
                                <p class="help-block small">Isi dengan angka, Contoh: 5162 atau 0.8.</p>
                            </div>
                            <div class="form-group">
                                <label>Frekuensi Terbit (Bulan)</label>
                                <input class="form-control" type="text" name="publikasi" value="<?=$row->publikasi?>" placeholder="Januari, Juli"/>
                                <p class="help-block small">Pisahkan dengan koma. Semakin banyak bulan terbit, skor semakin tinggi.</p>
                            </div>
                            <div class="form-group">
                                <label>Indexing</label>
                                <textarea class="form-control" name="indexing" rows="2" placeholder="Scopus, DOAJ..."><?=$row->indexing?></textarea>
                                <p class="help-block small">Pisahkan index dengan koma (Ketik Scopus untuk skor maksimal).</p>
                            </div>
                            <div class="form-group">
                                <label>Aims & Scope (Kata Kunci)</label>
                                <textarea class="form-control" name="aims_scope" rows="2" placeholder="AI, Machine Learning..."><?=$row->aims_scope?></textarea>
                                <p class="help-block small">Pisahkan kata kunci dengan koma untuk menghitung cakupan skor.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <button class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Simpan Perubahan</button>
                    <a class="btn btn-danger" href="index_admin.php?m=alternatif"><span class="glyphicon glyphicon-arrow-left"></span> Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>