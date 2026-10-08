<?php
// Pastikan variabel global database tersedia
global $db, $ALTERNATIF;

// ============================================================
// 1. TANGKAP INPUT JIKA DARI FORM
// ============================================================
if(isset($_POST['target'])){
    $input_target = $_POST['target'];
    $input_query  = $_POST['id_query'] ?? '';

    // Simpan ke Session
    $_SESSION['target'] = $input_target;
    $_SESSION['id_query_filter'] = $input_query;
}

// Ambil data dari Session
$target = $_SESSION['target'] ?? [];
$id_query = $_SESSION['id_query_filter'] ?? '';

// Validasi Input
if(!$target || !$id_query){
    echo "<div class='alert alert-warning'>
            <i class='glyphicon glyphicon-info-sign'></i> <strong>Data Belum Lengkap!</strong><br>
            Anda belum mengisi Form Preferensi.<br><br>
            <a href='?m=form' class='btn btn-primary'>Ke Form Preferensi</a>
          </div>";
    return;
}

// ============================================================
// 2. PROSES HITUNG (SILENT CALCULATION)
// ============================================================

// A. Hitung Bobot AHP
$matriks = AHP_get_relkriteria();   
$total = AHP_get_total_kolom($matriks);
$normal_ahp = AHP_normalize($matriks, $total);
$rata = AHP_get_rata($normal_ahp); 

// B. TOPSIS Logic - Ambil data terbatas pada bidang yang dipilih
$res_analisa = $db->get_results("SELECT kode_alternatif, kode_kriteria, nilai 
                                FROM tb_rel_alternatif 
                                WHERE id_query = '$id_query' 
                                ORDER BY kode_alternatif ASC");
$dataAnalisa = [];
foreach($res_analisa as $row) {
    $dataAnalisa[$row->kode_alternatif][$row->kode_kriteria] = $row->nilai;
}

$pembagi      = TOPSIS_get_pembagi($dataAnalisa);
$normal       = TOPSIS_nomalize($dataAnalisa);
$terbobot     = TOPSIS_nomal_terbobot($normal, $rata);

$atribut_kriteria = get_atribut_kriteria();
$ideal = TOPSIS_solusi_ideal_individu($terbobot, $target, $rata, $pembagi, $atribut_kriteria);

$jarak = TOPSIS_jarak_solusi($terbobot, $ideal);
$pref  = TOPSIS_preferensi($jarak);

// ============================================================
// 3. TAMPILAN HASIL
// ============================================================

// Ambil Nama Bidang Ilmu
$nama_bidang_user = $db->get_row("SELECT nama_query FROM tb_query WHERE id_query='$id_query'");
$judul_bidang = $nama_bidang_user ? $nama_bidang_user->nama_query : 'Semua Bidang';

// Ambil Info Jurnal (Termasuk URL Jurnal)
$info_jurnal = [];
$q_info = $db->get_results("SELECT a.kode_alternatif, a.tittle, a.url_jurnal, q.nama_query, q.id_query 
                            FROM tb_alternatif a 
                            LEFT JOIN tb_query q ON a.id_query = q.id_query");
foreach($q_info as $qi){
    $info_jurnal[$qi->kode_alternatif] = [
        'nama' => $qi->tittle,
        'bidang' => $qi->nama_query,
        'id_bidang' => $qi->id_query,
        'url' => $qi->url_jurnal
    ];
}
?>

<div class="page-header">
    <h1><i class="glyphicon glyphicon-certificate"></i> Hasil Rekomendasi</h1>
    <p class="lead">Bidang Ilmu: <strong><?= $judul_bidang ?></strong></p>
</div>

<div class="panel panel-info">
    <div class="panel-heading"><h3 class="panel-title">1. Jarak Terhadap Solusi Ideal</h3></div>
    <div class="panel-body">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr class="active">
                    <th width="80">Kode</th>
                    <th>Nama Jurnal</th>
                    <th class="text-center">Jarak ke Target (D+)</th>
                    <th class="text-center">Jarak ke Terburuk (D-)</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            foreach ($jarak as $kode => $nilai_jarak): 
            ?>
                <tr>
                    <td><strong><?= $kode ?></strong></td>
                    <td><?= $info_jurnal[$kode]['nama'] ?? '-' ?></td>
                    <td class="text-center text-success"><?= number_format($nilai_jarak['positif'], 3) ?></td>
                    <td class="text-center text-danger"><?= number_format($nilai_jarak['negatif'], 3) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading"><h3 class="panel-title">2. Perangkingan Akhir (Nilai Preferensi V)</h3></div>
    <div class="panel-body">
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="active">
                    <th width="50" class="text-center">Rank</th>
                    <th width="80" class="text-center">Kode</th>
                    <th>Nama Jurnal</th>
                    <th width="120" class="text-center">Nilai (V)</th>
                    <th width="130" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $hasil_akhir = [];
            foreach($pref as $kode => $nilai){
                $hasil_akhir[] = [
                    'kode'  => $kode,
                    'nama'  => $info_jurnal[$kode]['nama'] ?? '-',
                    'nilai' => $nilai,
                    'url'   => $info_jurnal[$kode]['url'] ?? '#'
                ];
            }
            usort($hasil_akhir, function($a, $b) { return $b['nilai'] <=> $a['nilai']; });

            $rank = 1;
            foreach ($hasil_akhir as $row): 
                // WARNA PASTEL KHUSUS
                $bg_style = "";
                if($rank == 1) {
                    $bg_style = "background-color: #D1EAFF;"; // Blue Pastel
                } elseif($rank == 2) {
                    $bg_style = "background-color: #DFFFD6;"; // Green Pastel
                } elseif($rank == 3) {
                    $bg_style = "background-color: #FFF9C4;"; // Yellow Pastel
                }
                
                $icon = ($rank == 1) ? '<i class="glyphicon glyphicon-star text-primary"></i> ' : '';
            ?>
                <tr style="<?= $bg_style ?> <?= ($rank <= 3) ? 'font-weight:bold;' : '' ?>">
                    <td class="text-center"><span class="badge"><?= $rank ?></span></td>
                    <td class="text-center"><?= $row['kode'] ?></td>
                    <td><?= $icon . $row['nama'] ?></td>
                    <td class="text-center text-primary"><?= number_format($row['nilai'], 3) ?></td>
                    <td class="text-center">
                        <?php if(!empty($row['url']) && $row['url'] != '#'): ?>
                            <a href="<?= $row['url'] ?>" target="_blank" class="btn btn-xs btn-info">
                                <span class="glyphicon glyphicon-link"></span> Buka Jurnal
                            </a>
                        <?php else: ?>
                            <span class="label label-default">No Link</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php $rank++; endforeach; ?>
            </tbody>
        </table>
        
        <div class="text-right" style="margin-top: 20px;">
            <a class="btn btn-primary" href="?m=form"><i class="glyphicon glyphicon-edit"></i> Hitung Ulang</a>
            
        </div>
    </div>
</div>