<?php
session_start();
?>
<style>
    .text-primary{font-weight: bold;}
    .filter-btn { margin-right: 5px; margin-bottom: 5px; }
    .label-bidang { font-size: 85%; }
    .nw { white-space: nowrap; }
    
    /* MODIFIKASI PRAKTIS: Sekali tulis untuk memaksa semua isi tabel ke tengah */
    .table th, .table td {
        text-align: center !important;
        vertical-align: middle !important;
    }
    /* Opsional: Membuat khusus teks Nama Jurnal tetap rata kiri agar rapi saat dibaca */
    .table .text-left-jurnal {
        text-align: left !important;
    }
</style>

<div class="page-header">
    <h1>Perhitungan Rekomendasi Jurnal (Individu)</h1>
</div>

<?php
if(isset($_GET['act']) && $_GET['act'] == 'reset'){
    unset($_SESSION['target']);
    unset($_SESSION['id_query_filter']);
    echo "<script>location.href='?m=form';</script>";
    exit;
}

// ============================================================
// 1. TANGKAP INPUT DARI FORM
// ============================================================

// Ambil array target (AK, APC, IND, GC, IF, WP, AS)
// Nilai AS sekarang sudah otomatis masuk dari form user (1-5)
$target = $_POST['target'] ?? $_SESSION['target'] ?? [];

// Ambil Filter Bidang Ilmu (id_query)
$id_query = $_POST['id_query'] ?? $_SESSION['id_query_filter'] ?? '';

if(!$target || !$id_query){
    echo "<div class='alert alert-warning'>
            <strong>Data Belum Lengkap!</strong><br>
            Preferensi atau Bidang Ilmu belum dipilih. 
            <a href='?m=form' class='btn btn-xs btn-primary'>Kembali ke Form</a>
          </div>";
    exit;
}

// KITA TIDAK LAGI MEMAKSA NILAI AS JADI 5 DI SINI
// KARENA USER SUDAH MEMILIH SENDIRI TINGKAT KESESUAIANNYA DI FORM.
// $target['AS'] = 5;  <-- Baris ini dihapus agar preferensi user tidak tertimpa

// Simpan ke session
$_SESSION['target'] = $target;
$_SESSION['id_query_filter'] = $id_query;

// ============================================================
// 2. CEK KELENGKAPAN DATA DATABASE
// ============================================================
$c = $db->get_results("SELECT * FROM tb_rel_alternatif WHERE nilai>0"); 
if (!$ALTERNATIF || !$KRITERIA):
    echo "<div class='alert alert-danger'>Data Alternatif atau Kriteria masih kosong.</div>";
elseif (!$c):
    echo "<div class='alert alert-danger'>Nilai bobot alternatif belum diisi oleh Admin.</div>";
else:
?>

<div class="panel panel-primary">
    <div class="panel-heading"><strong>1. Perhitungan AHP (Analytical Hierarchy Process)</strong></div>
    <div class="panel-body">
        
        <?php 
        // 1. Matriks Perbandingan Berpasangan
        $matriks = AHP_get_relkriteria();   
        $total = AHP_get_total_kolom($matriks);
        ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">A. Matriks Perbandingan Kriteria</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover text-center">
                        <thead>
                            <tr>
                                <th class="text-center">Kriteria</th>
                                <?php foreach($matriks as $key => $value){ echo "<th class='nw text-center'>$key</th>"; } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($matriks as $key => $value): ?>
                            <tr>
                                <th class='nw text-center'><?= $key ?></th>
                                <?php 
                                foreach($value as $k => $v){ 
                                    // Mengubah round($v,3) menjadi number_format($v, 2) agar angka bulat memiliki .00
                                    echo "<td>".number_format($v, 2)."</td>"; 
                                } 
                                ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class='nw text-center'>Total Kolom</th>
                                <?php 
                                foreach($total as $key => $value){ 
                                    // Mengubah round menjadi number_format dengan 2/3 desimal sesuai gambar 3 (Total Kolom di gambar 3 menggunakan 3 desimal, jika ingin 2 desimal ganti angka 3 menjadi 2)
                                    echo "<td class='text-primary' style='text-align: center;'>".number_format($total[$key], 3)."</td>"; 
                                } 
                                ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <?php 
        // 2. Normalisasi & Bobot Prioritas
        $normal_ahp = AHP_normalize($matriks, $total);
        $rata = AHP_get_rata($normal_ahp); // INI BOBOT AKHIR (W)
        ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">B. Matriks Bobot Prioritas (Normalisasi)</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <?php foreach($normal_ahp as $key => $value){ echo "<th class='nw text-center'>$key</th>";} ?>
                                <th class='nw success'>Bobot Prioritas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($normal_ahp as $key => $value): ?>
                            <tr>
                                <th class='nw'><?= $key ?></th>
                                <?php foreach($value as $k => $v){ echo "<td>".round($v,3)."</td>"; } ?>
                                <td class='text-primary success'><?= round($rata[$key],3) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php 
        // 3. Konsistensi (CM, CI, RI, CR)
        $cm = AHP_consistency_measure($matriks, $rata);
        ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">C. Matriks Konsistensi (CM)</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <?php foreach($normal_ahp as $key => $value){ echo "<th class='nw'>$key</th>"; } ?>
                                <th class='nw warning'>Consistency Measure</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($normal_ahp as $key => $value): ?>
                            <tr>
                                <th class='nw'><?= $key ?></th>
                                <?php foreach($value as $k => $v){ echo "<td>".round($v,3)."</td>"; } ?>
                                <td class='text-primary warning'><?= round($cm[$key],3) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="panel-footer">
                <?php
                    $CI = ((array_sum($cm)/count($cm))-count($cm))/(count($cm)-1);  
                    $RI = $nRI[count($matriks)];
                    $CR = ($RI == 0) ? 0 : $CI/$RI;
                ?>
                <div class="row">
                    <div class="col-md-4"><strong>Consistency Index (CI):</strong> <?= round($CI, 4) ?></div>
                    <div class="col-md-4"><strong>Ratio Index (RI):</strong> <?= round($RI, 4) ?></div>
                    <div class="col-md-4">
                        <strong>Consistency Ratio (CR):</strong> 
                        <span class="<?= ($CR <= 0.1) ? 'text-success' : 'text-danger' ?>">
                            <?= round($CR, 4) ?>
                        </span>
                        <?= ($CR <= 0.1) ? " (KONSISTEN)" : " (TIDAK KONSISTEN - Perbaiki Bobot!)" ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="panel panel-primary">
    <?php
    // Ambil nama bidang secara dinamis berdasarkan id_query
    $nama_bidang = $db->get_var("SELECT nama_query FROM tb_query WHERE id_query = '$id_query'");
    ?>
    <div class="panel-heading"><strong>2. Perhitungan TOPSIS (Bidang: <?= $nama_bidang ?>)</strong></div>
    <div class="panel-body">

        <?php 
        /**
         * 1. AMBIL DATA KHUSUS BIDANG & URUTKAN BERDASARKAN KODE
         */
        $data_db = $db->get_results("SELECT kode_alternatif, kode_kriteria, nilai 
                                    FROM tb_rel_alternatif 
                                    WHERE id_query = '$id_query'
                                    ORDER BY kode_alternatif ASC, kode_kriteria ASC");
        
        $dataAnalisa = [];
        foreach($data_db as $row) {
            $dataAnalisa[$row->kode_alternatif][$row->kode_kriteria] = $row->nilai;
        }

        if (empty($dataAnalisa)):
            echo "<div class='alert alert-danger'>Data untuk bidang $nama_bidang tidak ditemukan.</div>";
        else:
            // Urutkan array agar A21, A22, dst berurutan secara sistem
            ksort($dataAnalisa);

            // Hitung Pembagi & Normalisasi (R)
            $pembagi = TOPSIS_get_pembagi($dataAnalisa);
            $normal = TOPSIS_nomalize($dataAnalisa);
        ?>
        
        <div class="panel panel-default">
            <div class="panel-heading"><strong>A. Normalisasi Matriks (R)</strong></div>
            <div class="panel-body oxa">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <?php foreach(current($dataAnalisa) as $k => $v) echo "<th>$k</th>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($normal as $kode => $values): ?>
                        <tr>
                            <th><?= $kode ?></th>
                            <?php foreach($values as $v) echo "<td>".number_format($v, 3)."</td>"; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php 
        // B. MATRIKS TERBOBOT (Y) 
        $terbobot = TOPSIS_nomal_terbobot($normal, $rata); 
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><strong>B. Matriks Terbobot (Y)</strong></div>
            <div class="panel-body oxa">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <?php foreach(current($terbobot) as $k => $v) echo "<th>$k</th>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($terbobot as $kode => $values): ?>
                        <tr>
                            <th><?= $kode ?></th>
                            <?php foreach($values as $v) echo "<td>".number_format($v, 3)."</td>"; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php 
        // C. SOLUSI IDEAL
        $atribut_kriteria = get_atribut_kriteria();
        $ideal = TOPSIS_solusi_ideal_individu($terbobot, $target, $rata, $pembagi, $atribut_kriteria);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><strong>C. Matriks Solusi Ideal (A+ & A-)</strong></div>
            <div class="panel-body oxa">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <?php foreach($ideal['positif'] as $k => $v) echo "<th>$k</th>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="success">
                            <th>Ideal Positif (A+)</th>
                            <?php foreach($ideal['positif'] as $v) echo "<td>".number_format($v, 3)."</td>"; ?>
                        </tr>
                        <tr class="danger">
                            <th>Ideal Negatif (A-)</th>
                            <?php foreach($ideal['negatif'] as $v) echo "<td>".number_format($v, 3)."</td>"; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php            
        // D. JARAK SOLUSI & PREFERENSI
        $jarak = TOPSIS_jarak_solusi($terbobot, $ideal);
        $pref  = TOPSIS_preferensi($jarak);
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><strong>D. Jarak Solusi (D+/D-) & Nilai Preferensi (V)</strong></div>
            <div class="panel-body oxa">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr class="info">
                            <th>Kode</th>
                            <th>Jarak Positif (D+)</th>
                            <th>Jarak Negatif (D-)</th>
                            <th>Nilai Preferensi (V)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($jarak as $kode => $val): ?>
                        <tr>
                            <th><?= $kode ?></th>
                            <td><?= number_format($val['positif'], 3) ?></td>
                            <td><?= number_format($val['negatif'], 3) ?></td>
                            <td class="text-primary"><strong><?= number_format($pref[$kode], 3) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>        
            </div>
        </div>

        <div class="panel panel-success">
            <div class="panel-heading"><strong>E. Hasil Rekomendasi Ranking</strong></div>
            <div class="panel-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="active">
                            <th width="50">Rank</th>
                            <th width="80">Kode</th>
                            <th>Nama Jurnal</th>
                            <th width="150">Total Nilai (V)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php     
                    $q_info = $db->get_results("SELECT kode_alternatif, tittle FROM tb_alternatif WHERE id_query = '$id_query'");
                    $info_jurnal = [];
                    foreach($q_info as $qi) $info_jurnal[$qi->kode_alternatif] = $qi->tittle;

                    $rank_data = [];
                    foreach($pref as $kode => $nilai) {
                        $rank_data[] = ['kode' => $kode, 'nama' => $info_jurnal[$kode] ?? '-', 'nilai' => $nilai];
                    }

                    usort($rank_data, function($a, $b) { return $b['nilai'] <=> $a['nilai']; });

                    $n = 1;
                    foreach ($rank_data as $row): 
                        // WARNA BERBEDA TIAP RANK
                        $row_class = "";
                        $style = "";
                        if($n == 1) $row_class = "success"; // Hijau
                        elseif($n == 2) { 
                            $style = "background-color: #fff9c4;"; // Kuning Cerah (Emas)
                        } 
                        elseif($n == 3) {
                            $style = "background-color: #fce4ec;"; // Merah Muda (Pink)
                        }
                    ?>
                        <tr class="<?= $row_class ?>" style="<?= $style ?>">
                            <td><span class="badge"><?= $n++ ?></span></td>
                            <td><?= $row['kode'] ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><strong><?= number_format($row['nilai'], 3) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.filter-btn');
    const rows = document.querySelectorAll('.row-data');
    let activeFilter = 'all';
    
    // Cek tombol awal yang aktif
    buttons.forEach(btn => {
        if(btn.classList.contains('btn-info')){ activeFilter = btn.getAttribute('data-id'); }
    });
    
    filterTable(activeFilter);

    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            buttons.forEach(b => { b.classList.remove('btn-primary', 'btn-info'); b.classList.add('btn-default'); });
            this.classList.remove('btn-default'); this.classList.add('btn-primary');
            const filter = this.getAttribute('data-id');
            filterTable(filter);
        });
    });

    function filterTable(filterValue) {
        rows.forEach(row => {
            const bidang = row.getAttribute('data-bidang');
            if (filterValue === 'all' || bidang === filterValue) { row.style.display = ''; } 
            else { row.style.display = 'none'; }
        });
    }
});
</script>

<?php endif; ?>