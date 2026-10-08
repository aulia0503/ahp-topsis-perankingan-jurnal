<div class="page-header">
    <h1>Tambah Alternatif</h1>
</div>
<div class="row">
    <div class="col-sm-12">
        <?php if($_POST) include 'aksi.php'; ?>
        <form method="post">
            <input type="hidden" name="mod" value="alternatif_tambah">
            
            <div class="panel panel-primary">
                <div class="panel-heading">Form Pengisian Data Jurnal</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kode Jurnal (Otomatis)</label>
                                <input class="form-control" type="text" name="kode" value="<?= get_next_kode_alternatif() ?>" readonly />
                            </div>
                            <div class="form-group">
                                <label>Judul Jurnal <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="tittle" placeholder="Masukkan nama jurnal" required />
                            </div>
                            <div class="form-group">
                                <label>Link Jurnal (URL)</label>
                                <input class="form-control" type="url" name="url_jurnal" placeholder="https://journal.contoh.ac.id/index.php/jurnal" />
                                <p class="help-block small">Masukkan URL lengkap website jurnal.</p>
                            </div>
                            <div class="form-group">
                                <label>Bidang <span class="text-danger">*</span></label>
                                <select class="form-control" name="id_query" required>
                                    <option value="">-- Pilih Bidang --</option>
                                    <?= get_query_option() ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Akreditasi</label>
                                <select class="form-control" name="akreditasi">
                                    <?php
                                    $opts = ['Sinta 1', 'Sinta 2', 'Sinta 3', 'Sinta 4', 'Sinta 5', 'Sinta 6'];
                                    foreach($opts as $opt) echo "<option value='$opt'>$opt</option>";
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Impact Factor (Angka)</label>
                                <input class="form-control" type="number" step="0.01" name="impact" placeholder="Contoh: 1.2" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>APC (Biaya Jurnal)</label>
                                <input class="form-control" type="number" name="APC" placeholder="Contoh: 2000000 (0 jika gratis)" />
                            </div>
                            <div class="form-group">
                                <label>Total Citations (Angka)</label>
                                <input class="form-control" type="number" name="google_citation" placeholder="Contoh: 500" />
                                <p class="help-block small">Isi jumlah sitasi dari Google Scholar.</p>
                            </div>
                            <div class="form-group">
                                <label>Frekuensi Terbit (Bulan)</label>
                                <input class="form-control" type="text" name="publikasi" placeholder="Januari, Juli" />
                                <p class="help-block small">Pisahkan dengan koma.</p>
                            </div>
                            <div class="form-group">
                                <label>Aims & Scope (Kata Kunci)</label>
                                <textarea class="form-control" name="aims_scope" rows="2" placeholder="AI, Machine Learning..."></textarea>
                                <p class="help-block small">Pisahkan kata kunci dengan koma.</p>
                            </div>
                            <div class="form-group">
                                <label>Indexing</label>
                                <textarea class="form-control" name="indexing" rows="2" placeholder="Scopus, DOAJ..."></textarea>
                                <p class="help-block small">Pisahkan dengan koma (Ketik Scopus untuk skor maksimal).</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <button class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Simpan Alternatif</button>
                    <a class="btn btn-danger" href="?m=alternatif">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>