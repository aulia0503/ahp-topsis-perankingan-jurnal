<?php
// Ambil data untuk dropdown bidang ilmu
$rows = $db->get_results("SELECT id_query, nama_query FROM tb_query ORDER BY nama_query");

// Ambil nilai lama jika ada (agar tidak reset)
$old_target = $_POST['target'] ?? $_SESSION['target'] ?? [];
$old_id_query = $_POST['id_query'] ?? $_SESSION['id_query_filter'] ?? '';

// Fungsi helper kecil untuk menandai opsi terpilih
function is_selected($value, $old_value){
    return ($value == $old_value) ? 'selected' : '';
}
?>

<div class="page-header">
    <h1><i class="glyphicon glyphicon-tasks"></i> Form Preferensi Publikasi Individu</h1>
</div>

<style>
    .panel { background: #fff; border: 1px solid #ddd; } /* Border halus di luar panel */
    .panel-footer { background: #f9f9f9; padding: 15px; border-top: 1px solid #eee; }
    .btn-action { margin-right: 5px; }
    
    /* Menghilangkan border pada select box agar lebih bersih jika diinginkan, 
       atau biarkan default Bootstrap. Di sini saya pakai default Bootstrap tapi rapi. */
    .form-group { margin-bottom: 20px; }
    
    /* Style khusus untuk label wajib */
    .label-wajib { color: #d9534f; font-weight: bold; }
</style>

<form action="?m=ranking" method="post">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Masukkan Kriteria Jurnal yang Anda Cari</h3>
        </div>
        
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    
                    <div class="form-group">
                        <label class="label-wajib">* Pilih Bidang Ilmu Utama (Wajib)</label>
                        <select class="form-control" name="id_query" required style="border: 1px solid #d9534f;">
                            <option value="">-- Pilih Bidang --</option>
                            <?php foreach($rows as $r): ?>
                                <option value="<?= $r->id_query ?>" <?= is_selected($r->id_query, $old_id_query) ?>>
                                    <?= $r->nama_query ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="help-block small">Hanya jurnal di bidang ini yang akan dihitung.</p>
                    </div>

                    <div class="form-group">
                        <label>Target Akreditasi (AK)</label>
                        <select class="form-control" name="target[AK]">
                            <option value="5" <?= is_selected('5', $old_target['AK'] ?? '') ?>>Sinta 1 (Skor 5)</option>
                            <option value="4" <?= is_selected('4', $old_target['AK'] ?? '') ?>>Sinta 2 (Skor 4)</option>
                            <option value="3" <?= is_selected('3', $old_target['AK'] ?? '') ?>>Sinta 3 (Skor 3)</option>
                            <option value="2" <?= is_selected('2', $old_target['AK'] ?? '') ?>>Sinta 4 (Skor 2)</option>
                            <option value="1" <?= is_selected('1', $old_target['AK'] ?? '') ?>>Sinta 5 (Skor 1)</option>
                        </select>
                    </div>

        <div class="form-group">
            <label>Anggaran Biaya / APC (APC)</label>
            <select class="form-control" name="target[APC]">
                <option value="5" <?= is_selected('5', $old_target['APC'] ?? '') ?>>Rp 0 (Gratis) (Skor 5)</option>
                <option value="4" <?= is_selected('4', $old_target['APC'] ?? '') ?>>Rp 1 - Rp 1.000.000 (Skor 4)</option>
                <option value="3" <?= is_selected('3', $old_target['APC'] ?? '') ?>>Rp 1.000.001 - Rp 2.000.000 (Skor 3)</option>
                <option value="2" <?= is_selected('2', $old_target['APC'] ?? '') ?>>Rp 2.000.001 - Rp 4.000.000 (Skor 2)</option>
                <option value="1" <?= is_selected('1', $old_target['APC'] ?? '') ?>>> Rp 4.000.000 (Skor 1)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Target Indexing (IND)</label>
            <select class="form-control" name="target[IND]">
                <option value="5" <?= is_selected('5', $old_target['IND'] ?? '') ?>>Terindeks Scopus (Skor 5)</option>
                <option value="4" <?= is_selected('4', $old_target['IND'] ?? '') ?>>Non-Scopus (>= 6 Pengindeks) (Skor 4)</option>
                <option value="3" <?= is_selected('3', $old_target['IND'] ?? '') ?>>Non-Scopus (4 - 5 Pengindeks) (Skor 3)</option>
                <option value="2" <?= is_selected('2', $old_target['IND'] ?? '') ?>>Non-Scopus (2 - 3 Pengindeks) (Skor 2)</option>
                <option value="1" <?= is_selected('1', $old_target['IND'] ?? '') ?>>Hanya Terindeks Sinta (Skor 1)</option>
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Aims & Scope / Jumlah Keyword (AS)</label>
            <select class="form-control" name="target[AS]">
                <option value="5" <?= is_selected('5', $old_target['AS'] ?? '') ?>>> 15 Bidang/Kata Kunci (Skor 5)</option>
                <option value="4" <?= is_selected('4', $old_target['AS'] ?? '') ?>>11 - 15 Bidang (Skor 4)</option>
                <option value="3" <?= is_selected('3', $old_target['AS'] ?? '') ?>>6 - 10 Bidang (Skor 3)</option>
                <option value="2" <?= is_selected('2', $old_target['AS'] ?? '') ?>>3 - 5 Bidang (Skor 2)</option>
                <option value="1" <?= is_selected('1', $old_target['AS'] ?? '') ?>> < 3 Bidang (Skor 1)</option>
            </select>
            <p class="help-block small text-muted">Cakupan bidang ilmu yang dapat diterima oleh jurnal.</p>
        </div>

        <div class="form-group">
            <label>Citations Total (GC)</label>
            <select class="form-control" name="target[GC]">
                <option value="5" <?= is_selected('5', $old_target['GC'] ?? '') ?>>> 5.000 Citations (Skor 5)</option>
                <option value="4" <?= is_selected('4', $old_target['GC'] ?? '') ?>>2.501 - 5.000 Citations (Skor 4)</option>
                <option value="3" <?= is_selected('3', $old_target['GC'] ?? '') ?>>1.001 - 2.500 Citations (Skor 3)</option>
                <option value="2" <?= is_selected('2', $old_target['GC'] ?? '') ?>>501 - 1.000 Citations (Skor 2)</option>
                <option value="1" <?= is_selected('1', $old_target['GC'] ?? '') ?>> < 500 Citations (Skor 1)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Impact Score (IF)</label>
            <select class="form-control" name="target[IF]">
                <option value="5" <?= is_selected('5', $old_target['IF'] ?? '') ?>>> 4.0 (Skor 5)</option>
                <option value="4" <?= is_selected('4', $old_target['IF'] ?? '') ?>>2.1 - 4.0 (Skor 4)</option>
                <option value="3" <?= is_selected('3', $old_target['IF'] ?? '') ?>>1.1 - 2.0 (Skor 3)</option>
                <option value="2" <?= is_selected('2', $old_target['IF'] ?? '') ?>>0.6 - 1.0 (Skor 2)</option>
                <option value="1" <?= is_selected('1', $old_target['IF'] ?? '') ?>>0 - 0.5 (Skor 1)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Waktu Proses / Frekuensi Terbit (WP)</label>
            <select class="form-control" name="target[WP]">
                <option value="5" <?= is_selected('5', $old_target['WP'] ?? '') ?>>> 6 kali setahun (Bulanan) (Skor 5)</option>
                <option value="4" <?= is_selected('4', $old_target['WP'] ?? '') ?>>4 - 5 kali setahun (Quarterly) (Skor 4)</option>
                <option value="3" <?= is_selected('3', $old_target['WP'] ?? '') ?>>3 kali setahun (Skor 3)</option>
                <option value="2" <?= is_selected('2', $old_target['WP'] ?? '') ?>>2 kali setahun (Skor 2)</option>
                <option value="1" <?= is_selected('1', $old_target['WP'] ?? '') ?>>1 kali setahun (Skor 1)</option>
            </select>
        </div>
    </div>
</div>
            
            <p class="help-block small text-muted text-center" style="margin-top: 10px;">
                *Data preferensi ini akan dibandingkan dengan database jurnal menggunakan metode TOPSIS.
            </p>
        </div>

        <div class="panel-footer">
            <div class="row">
                <div class="col-md-6 text-left">
                    <a href="?m=hitung&act=reset" class="btn btn-danger btn-action" onclick="return confirm('Yakin reset?')"><i class="glyphicon glyphicon-trash"></i> Reset Hitungan</a>
                    <button class="btn btn-warning btn-action" type="reset"><i class="glyphicon glyphicon-refresh"></i> Reset Form</button>
                </div>
                <div class="col-md-6 text-right">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="glyphicon glyphicon-search"></i> SPK AHP TOPSIS Jurnal
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="alert alert-info" style="margin-top: 20px;">
    <p><i class="glyphicon glyphicon-info-sign"></i> <strong>Alur Sistem:</strong></p>
    <ol>
        <li>Sistem mengambil <strong>Bobot Kepentingan</strong> dari Matriks AHP.</li>
        <li>Anda memilih <strong>Bidang Ilmu</strong> sebagai filter utama.</li>
        <li>Preferensi (Nilai 1-5) yang Anda masukkan menjadi <strong>Solusi Ideal Positif</strong>.</li>
        <li>Metode <strong>TOPSIS</strong> akan merekomendasikan jurnal yang paling sesuai.</li>
    </ol>
</div>