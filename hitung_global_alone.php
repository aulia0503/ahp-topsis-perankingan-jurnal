<?php
/**
 * 1. TANGKAP FILTER
 */
$modul = $_GET['m'] ?? 'hitung_global_alone'; 
$filter = $_GET['filter'] ?? 'semua';
$id_query = ($filter == 'semua') ? 0 : $filter;

// DETEKSI HALAMAN INDUK (Agar user tidak terlempar ke index_admin.php)
$current_page = basename($_SERVER['PHP_SELF']);
// Jika diakses oleh user biasa lewat index3.php, maka link filter tetap ke index3.php
$index_page = ($current_page == 'index_admin.php') ? 'index_admin.php' : 'index3.php';

// Jalankan fungsi perhitungan jika ada
if (function_exists('TOPSIS_get_hasil_analisa')) {
    TOPSIS_get_hasil_analisa($id_query);
}

$kategori = $db->get_results("SELECT * FROM tb_query ORDER BY id_query ASC");
?>

<style>
    .text-primary { font-weight: bold; color: #2c3e50 !important; }
    .text-dark { color: #000 !important; }
    .btn-filter { margin-right: 5px; margin-bottom: 5px; border-radius: 4px; }
    .table-striped > tbody > tr:nth-of-type(odd) { background-color: #f9f9f9; }
</style>

<div class="page-header">
    <h1>Perangkingan Jurnal</h1>
</div>

<div class="well" style="padding: 15px; background: #fff; border-top: 3px solid #2c3e50; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div class="pull-right">
        <a href="cetak.php?m=ranking_global_cetak&filter=<?= $filter ?>" target="_blank" class="btn btn-default">
            <span class="glyphicon glyphicon-print"></span> Cetak Peringkat Ini
        </a>
    </div>

    <p style="color:#000; font-weight:bold; margin-bottom:10px;">Pilih Kategori Perangkingan Bidang Ilmu:</p>
    
    <a href="<?= $index_page ?>?m=<?= $modul ?>&filter=semua" 
       class="btn btn-sm btn-filter <?= ($filter == 'semua') ? 'btn-primary' : 'btn-default' ?>">
        Semua Data (Global)
    </a>
    
    <?php if($kategori): foreach ($kategori as $kat): ?>
        <a href="<?= $index_page ?>?m=<?= $modul ?>&filter=<?= $kat->id_query ?>" 
           class="btn btn-sm btn-filter <?= ($filter == $kat->id_query) ? 'btn-primary' : 'btn-default' ?>">
            <?= $kat->nama_query ?>
        </a>
    <?php endforeach; endif; ?>
    <div class="clearfix"></div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <strong>Tabel Peringkat Jurnal <?= ($filter != 'semua') ? '(Kategori Bidang)' : '(Global)' ?></strong>
    </div>

    <div class="panel-body oxa">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr class="active">
                    <th width="50" class="text-center text-dark">Rank</th>
                    <th width="80" class="text-dark">Kode</th>
                    <th class="text-dark">Nama Jurnal</th>
                    <?php if($filter == 'semua'): ?>
                    <th class="text-dark">Bidang Ilmu</th>
                    <?php endif; ?>
                    <th class="text-dark">Total Skor (V)</th>
                    <th width="120" class="text-center text-dark">Info Jurnal</th> 
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT a.kode_alternatif, a.tittle, a.total, a.url_jurnal, q.nama_query
                    FROM tb_alternatif a
                    LEFT JOIN tb_query q ON a.id_query = q.id_query
                    WHERE a.total > 0";

            if ($filter != 'semua') {
                $sql .= " AND a.id_query = '$id_query'";
            }

            $sql .= " ORDER BY a.total DESC, a.tittle ASC";
            $rows = $db->get_results($sql);

            if ($rows):
                $no = 1; 
                foreach ($rows as $row):
                    $bg_badge = "#7f8c8d"; 
                    if($no == 1) $bg_badge = "#f1c40f"; 
                    elseif($no == 2) $bg_badge = "#bdc3c7"; 
                    elseif($no == 3) $bg_badge = "#d35400"; 
            ?>
                <tr>
                    <td class="text-center">
                        <span class="badge" style="background-color: <?= $bg_badge ?>; font-size:14px; padding:5px 10px;">
                            #<?= $no++ ?>
                        </span>
                    </td>
                    <td class="text-dark"><?= $row->kode_alternatif ?></td>
                    <td class="text-dark"><?= $row->tittle ?></td>
                    <?php if($filter == 'semua'): ?>
                    <td class="text-dark"><?= $row->nama_query ?></td>
                    <?php endif; ?>
                    <td class="text-primary"><?= round($row->total, 6) ?></td>
                    
                    <td class="text-center">
                        <?php if($row->url_jurnal): ?>
                            <a href="<?= $row->url_jurnal ?>" target="_blank" class="btn btn-xs btn-info">
                                <span class="glyphicon glyphicon-link"></span> Buka Jurnal
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="<?= ($filter == 'semua') ? 6 : 5 ?>" class="text-center text-danger">
                        Data tidak ditemukan atau perhitungan belum dijalankan oleh Admin.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>