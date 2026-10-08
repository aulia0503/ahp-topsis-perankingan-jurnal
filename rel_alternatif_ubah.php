<?php
$kode = esc_field($_GET['kode']);
$alt = $db->get_row("SELECT * FROM tb_alternatif WHERE kode_alternatif='$kode'");

if (!$alt) {
    echo '<div class="alert alert-danger">Data alternatif tidak ditemukan!</div>';
    exit;
}
?>

<div class="page-header">
    <h1>Ubah Nilai Bobot &raquo; <small><?= htmlspecialchars($alt->tittle) ?></small></h1>
</div>

<?php if (!empty($_GET['saved'])): ?>
<div class="alert alert-success">Data bobot berhasil diperbarui secara manual!</div>
<script src="assets/js/sweetalert2@11.js"></script> <script>
    // Jika ingin pakai SweetAlert
    // Swal.fire('Berhasil!', 'Data bobot telah disimpan.', 'success');
</script>
<?php endif; ?>

<div class="row">
    <div class="col-sm-5">
        <?php if ($_POST) include 'aksi.php'; ?>
        
        <form method="post" action="aksi.php?act=rel_alternatif_ubah">
            <input type="hidden" name="kode_alternatif" value="<?= htmlspecialchars($kode) ?>">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Input Nilai Bobot (Skor 1 - 5)</strong>
                </div>
                <div class="panel-body">
                    <?php
                    $rows = $db->get_results("
                        SELECT ra.ID, k.kode_kriteria, k.nama_kriteria, ra.nilai
                        FROM tb_rel_alternatif ra
                        INNER JOIN tb_kriteria k ON k.kode_kriteria = ra.kode_kriteria
                        WHERE ra.kode_alternatif = '$kode'
                        ORDER BY k.kode_kriteria
                    ");

                    if ($rows):
                        foreach ($rows as $r): ?>
                            <div class="form-group">
                                <label><?= htmlspecialchars($r->nama_kriteria) ?> (<?= htmlspecialchars($r->kode_kriteria) ?>)</label>
                                <input class="form-control" 
                                       type="number" step="0.01" min="0" max="5"
                                       name="ID-<?= $r->ID ?>" 
                                       value="<?= htmlspecialchars($r->nilai) ?>" 
                                       required>
                                <p class="help-block small">Skor hasil konversi otomatis.</p>
                            </div>
                        <?php endforeach;
                    else:
                        echo '<div class="alert alert-warning">Belum ada data relasi kriteria. Silakan tambah data alternatif ulang.</div>';
                    endif;
                    ?>
                </div>
                <div class="panel-footer">
                    <button class="btn btn-primary" type="submit">
                        <span class="glyphicon glyphicon-save"></span> Simpan Perubahan
                    </button>
                    <a class="btn btn-danger" href="?m=rel_alternatif">
                        <span class="glyphicon glyphicon-arrow-left"></span> Kembali
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-sm-7">
        <div class="alert alert-info">
            <h4><i class="glyphicon glyphicon-info-sign"></i> Info</h4>
            <p>Nilai di samping adalah hasil <strong>Auto-Scoring</strong> dari data riil jurnal. Anda tetap bisa mengubahnya secara manual jika diperlukan untuk penyesuaian khusus.</p>
        </div>
    </div>
</div>