<style>
    .text-primary { font-weight: bold; }
    .topik-container { margin-bottom: 50px; border-bottom: 3px double #333; padding-bottom: 20px; }
    .topik-header { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin-bottom: 20px; }
    /* MODIFIKASI PRAKTIS: Sekali tulis untuk memaksa semua isi tabel ke tengah */
    .table th, .table td {
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    /* Membuat teks Nama Jurnal tetap rata kiri agar lebih rapi dibaca */
    .table .text-left-jurnal {
        text-align: left !important;
    }
</style>

<div class="page-header">
    <h1>Perhitungan</h1>
</div>

<?php
// FIX ERROR INDEPENDENT VARIABLES: Inisialisasi default jika belum ter-load dari functions.php
if (!isset($ALTERNATIF)) {
    $ALTERNATIF = [];
    $rows = $db->get_results("SELECT kode_alternatif, nama_alternatif FROM tb_alternatif");
    if ($rows) {
        foreach ($rows as $row) {
            $ALTERNATIF[$row->kode_alternatif] = $row->nama_alternatif;
        }
    }
}

if (!isset($KRITERIA)) {
    $KRITERIA = [];
    $rows = $db->get_results("SELECT id_kriteria, nama_kriteria FROM tb_kriteria");
    if ($rows) {
        foreach ($rows as $row) {
            $KRITERIA[$row->id_kriteria] = ['nama_kriteria' => $row->nama_kriteria];
        }
    }
}

// Array Nilai Ratio Index (nRI) standar AHP untuk mengukur konsistensi (Ordo 1 - 10)
if (!isset($nRI)) {
    $nRI = [
        1  => 0,
        2  => 0,
        3  => 0.58,
        4  => 0.90,
        5  => 1.12,
        6  => 1.24,
        7  => 1.32,
        8  => 1.41,
        9  => 1.45,
        10 => 1.49
    ];
}

// Validasi data sebelum perhitungan
$c = $db->get_results("SELECT * FROM tb_rel_alternatif WHERE nilai > 0");

if (empty($ALTERNATIF) || empty($KRITERIA)):
    echo "Tampaknya anda belum mengatur alternatif dan kriteria. Silahkan tambahkan minimal 3 alternatif dan 3 kriteria.";
elseif (!$c):
    echo "Tampaknya anda belum mengatur nilai alternatif. Silahkan atur pada menu <strong>Nilai Bobot</strong> > <strong>Nilai Bobot Alternatif</strong>.";
else:
?>

<div class="panel panel-primary">
    <div class="panel-heading"><strong>Mengukur Konsistensi Kriteria (AHP)</strong></div>
    <div class="panel-body">
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#c11" aria-expanded="false" aria-controls="c11">
                        Matriks Perbandingan Kriteria
                    </a>
                </h3>
            </div>
            <div class="panel-body collapse" id="c11">
                <p>Pertama-tama menyusun hirarki dimana diawali dengan tujuan, kriteria dan alternatif-alternatif lokasi pada tingkat paling bawah. 
                Selanjutnya menetapkan perbandingan berpasangan antara kriteria-kriteria dalam bentuk matrik. 
                Nilai diagonal matrik untuk perbandingan suatu elemen dengan elemen itu sendiri diisi dengan bilangan (1) sedangkan isi nilai perbandingan antara (1) sampai dengan (9) kebalikannya, kemudian dijumlahkan perkolom. 
                Data matrik tersebut seperti terlihat pada tabel berikut.</p> 
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                    <?php           
                        $matriks = AHP_get_relkriteria();   
                        $total = AHP_get_total_kolom($matriks);
                        
                        echo "<thead><tr><th></th>";     
                        foreach($matriks as $key => $value){
                            echo "<th class='nw'>$key</th>";        
                        }    
                        echo "</tr></thead>";    
                        foreach($matriks as $key => $value){
                            echo "<tr><th class='nw'>$key</th>";
                            foreach($value as $k => $v){
                                echo "<td>".number_format($v, 2)."</td>";
                            }        
                            echo "</tr>";
                        }    
                        echo "<tfoot><tr><th class='nw'>Total</th>";
                        foreach($total as $key => $value){
                            echo "<td class='text-primary'>".number_format($value, 2)."</td>";        
                        }
                        echo "</tr></tfoot>";
                    ?>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#c12" aria-expanded="false" aria-controls="c12">
                        Matriks Bobot Prioritas Kriteria
                    </a>
                </h3>
            </div>
            <div class="panel-body collapse" id="c12">
                <p>Setelah terbentuk matrik perbandingan maka dilihat bobot prioritas untuk perbandingan kriteria. 
                Dengan cara membagi isi matriks perbandingan dengan jumlah kolom yang bersesuaian, kemudian menjumlahkan perbaris setelah itu hasil penjumlahan dibagi dengan banyaknya kriteria sehingga ditemukan bobot prioritas seperti terlihat pada berikut.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                    <?php                
                        $normal_ahp = AHP_normalize($matriks, $total);                  
                        $rata = AHP_get_rata($normal_ahp);
                        
                        echo "<thead><tr><th></th>";   
                        foreach($normal_ahp as $key => $value){
                            echo "<th class='nw'>$key</th>";
                        }      
                        echo "<th class='nw'>Bobot Prioritas</th></tr></thead>";  
                        foreach($normal_ahp as $key => $value){
                            echo "<tr>";
                            echo "<th class='nw'>$key</th>";
                            foreach($value as $k => $v){
                                echo "<td>".round($v,3)."</td>";
                            }                                    
                            echo "<td class='text-primary'>".round($rata[$key],3)."</td>";
                            echo "</tr>";
                        }    
                    ?>
                    </table> 
                </div> 
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#c13" aria-expanded="false" aria-controls="c13">
                        Matriks Konsistensi Kriteria
                    </a>
                </h3>
            </div>
            <div class="panel-body collapse" id="c13">
                <p>Untuk mengetahui konsisten matriks perbandingan dilakukan perkalian seluruh isi kolom matriks A perbandingan dengan bobot prioritas kriteria A, isi kolom B matriks perbandingan dengan bobot prioritas kriteria B dan seterusnya. Kemudian dijumlahkan setiap barisnya dan dibagi penjumlahan baris dengan bobot prioritas bersesuaian seperti terlihat pada tabel berikut.</p> 
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                    <?php                        
                        $cm = AHP_consistency_measure($matriks, $rata);
                        
                        echo "<thead><tr><th></th>";   
                        foreach($normal_ahp as $key => $value){
                            echo "<th class='nw'>$key</th>";
                        }      
                        echo "<th>Nilai λ</th></tr></thead>";  
                        foreach($normal_ahp as $key => $value){
                            echo "<tr>";
                            echo "<th class='nw'>$key</th>";
                            foreach($value as $k => $v){
                                echo "<td>".round($v,3)."</td>";
                            }                                    
                            echo "<td class='text-primary'>".round($cm[$key],3)."</td>";
                            echo "</tr>";
                        }    
                    ?>
                    </table> 
                </div>
                <p>Berikut tabel ratio index berdasarkan ordo matriks.</p>    
                
                <table class="table table-bordered">
                    <tr>
                        <th>Ordo matriks</th>
                        <?php
                            foreach($nRI as $key => $value){
                                if(count($matriks)==$key)
                                    echo "<td class='text-primary'>$key</td>";
                                else
                                    echo "<td>$key</td>";
                            }
                        ?>
                    </tr>
                    <tr>
                        <th>Ratio index</th>
                        <?php
                            foreach($nRI as $key => $value){
                                if(count($matriks)==$key)
                                    echo "<td class='text-primary'>$value</td>";
                                else
                                    echo "<td>$value</td>";
                            }
                        ?>
                    </tr>
                </table>
            </div>
            <div class="panel-footer">
            <?php
                $CI = ((array_sum($cm)/count($cm))-count($cm))/(count($cm)-1);  
                $RI = isset($nRI[count($matriks)]) ? $nRI[count($matriks)] : 1;
                $CR = $RI > 0 ? $CI/$RI : 0;
                echo "<p>Consistency Index: ".round($CI, 3)."<br />";   
                echo "Ratio Index: ".round($RI, 3)."<br />";
                echo "Consistency Ratio: ".round($CR, 3);
                if($CR>0.10){
                    echo " (Tidak konsisten)<br />";    
                } else {
                    echo " (Konsisten)<br />";
                }
            ?>
            </div>
        </div>
    </div>
</div>

<?php
// MULAI PERULANGAN PER ID_QUERY UNTUK TOPSIS
$daftar_query = $db->get_results("SELECT * FROM tb_query WHERE id_query IN (SELECT DISTINCT id_query FROM tb_alternatif) ORDER BY id_query");

if($daftar_query):
foreach($daftar_query as $q_row):
    $current_id_query = $q_row->id_query;
?>

<div class="topik-container">
    <div class="topik-header">
        <h2 class="text-center">HASIL PERHITUNGAN BIDANG: <strong><?php echo $q_row->nama_query; ?></strong></h2>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading"><strong>Perhitungan TOPSIS - <?php echo $q_row->nama_query; ?></strong></div>
        <div class="panel-body">
            
            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Hasil Analisa</strong></div>
                <div class="panel-body oxa"> 
                    <table class="table table-bordered table-striped table-hover">
                    <?php                                                          
                        $data_analisa = TOPSIS_get_hasil_analisa($current_id_query); 
                        
                        if (empty($data_analisa)) {
                            echo "<tr><td>Data tidak tersedia untuk bidang ini</td></tr>";
                        } else {
                            $r = "<tr><th></th>";
                            foreach (current($data_analisa) as $key => $value) {
                                $r .= "<th>" . ($KRITERIA[$key]['nama_kriteria'] ?? $key) . "</th>";
                            }
                            $r .= "</tr>";

                            foreach ($data_analisa as $key => $value) {
                                $nama_alt = $ALTERNATIF[$key] ?? $key;
                                $r .= "<tr><th>" . $nama_alt . "</th>";
                                foreach ($value as $v) {
                                    $r .= "<td>" . (is_numeric($v) ? round($v, 3) : $v) . "</td>";
                                }
                                $r .= "</tr>";
                            }
                            echo $r;
                        }
                    ?>
                    </table>
                </div>
            </div>

            <?php if (!empty($data_analisa)): ?>
            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Normalisasi</strong></div>
                <div class="panel-body oxa">
                    <table class="table table-bordered table-striped table-hover">
                    <?php    
                    $normal = TOPSIS_nomalize($data_analisa);
                    $r = "";
                    $r.= "<tr><th></th>";       
                    foreach(current($normal) as $key => $value){
                        $r.= "<th>$key</th>";
                    }    
                    $r.= "</tr>";
                    
                    foreach($normal as $key => $value){
                        $r.= "<tr>";
                        $r.= "<th>".$key."</th>";
                        foreach($value as $k => $v){
                            $r.= "<td>".round($v,3)."</td>";
                        }        
                        $r.= "</tr>";
                    }    
                    echo $r;
                    ?>
                    </table>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Normalisasi Terbobot</strong></div>
                <div class="panel-body oxa">
                    <table class="table table-bordered table-striped table-hover">
                    <?php    
                    $terbobot = TOPSIS_nomal_terbobot($normal, $rata);
                    $r="";            
                    $r.= "<tr><th></th>";       
                    foreach(current($terbobot) as $key => $value){
                        $r.= "<th>$key</th>";
                    }    
                    $r.= "</tr>";
                    
                    foreach($terbobot as $key => $value){
                        $r.= "<tr>";
                        $r.= "<th>$key</th>";
                        foreach($value as $k => $v){
                            $r.= "<td>".round($v,3)."</td>";
                        }        
                        $r.= "</tr>";
                    }    
                    echo $r;
                    ?>
                    </table>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Matriks Solusi Ideal</strong></div>
                <div class="panel-body oxa">
                    <table class="table table-bordered table-striped table-hover">
                    <?php    
                    $ideal = TOPSIS_solusi_ideal($terbobot);
                    $r="";            
                    $r.= "<tr><th></th>";       
                    foreach($ideal[key($ideal)] as $key => $value){
                        $r.= "<th>".$key."</th>";
                    }    
                    $r.= "</tr>";
                    
                    foreach($ideal as $key => $value){
                        $r.= "<tr>";
                        $r.= "<th>".$key."</th>";
                        foreach($value as $k => $v){
                            $r.= "<td>".round($v,3)."</td>";
                        }        
                        $r.= "</tr>";
                    }    
                    echo $r;
                    ?>
                    </table>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Jarak Solusi &amp; Nilai Preferensi</strong></div>
                <div class="panel-body oxa">
                    <table class="table table-bordered table-striped table-hover">
                        <tr>
                            <th></th>
                            <th>Positif</th>
                            <th>Negatif</th>
                            <th>Preferensi</th>
                        </tr>
                    <?php            
                    $jarak = TOPSIS_jarak_solusi($terbobot, $ideal);
                    $pref = TOPSIS_preferensi($jarak);
                                                                                 
                    foreach($normal as $key => $value){
                        echo"<tr>";
                        echo"<th>$key</th>";
                        echo"<td>".round($jarak[$key]['positif'], 3)."</td>";
                        echo"<td>".round($jarak[$key]['negatif'], 3)."</td>";
                        echo"<td>".round($pref[$key], 3)."</td>";
                        echo "</tr>";
                    }                                                                
                    ?>
                    </table>        
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Perangkingan</strong></div>
                <div class="panel-body oxa">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Jurnal</th>
                                <th>Total</th>
                                <th>Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php    
                    $rank = get_rank($pref);   
                    $hasil = [];
                    foreach ($pref as $kode => $nilai) {
                        $hasil[] = [
                            'kode'  => $kode,
                            'nama'  => $ALTERNATIF[$kode] ?? $kode,
                            'total' => $nilai,
                            'rank'  => $rank[$kode],
                        ];
                    }

                    usort($hasil, function($a, $b) {
                        return $a['rank'] <=> $b['rank'];
                    });

                    foreach ($hasil as $row) {
                        $db->query("UPDATE tb_alternatif SET total='{$row['total']}', `rank`='{$row['rank']}' WHERE kode_alternatif='{$row['kode']}'");
                    ?>
                        <tr>
                            <td><?= $row['kode'] ?></td>
                            <td class="text-left-jurnal"><?= $row['nama'] ?></td>
                            <td class="text-primary"><?= round($row['total'], 3) ?></td>
                            <td class="text-primary"><strong><?= $row['rank'] ?></strong></td>
                        </tr>
                    <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
endforeach; 
endif;
?>

<?php endif; ?>