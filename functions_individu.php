<?php
error_reporting(~E_NOTICE);
session_start();

include'config.php';
include'includes/db.php';
$db = new DB($config['server'], $config['username'], $config['password'], $config['database_name']);
include'includes/general.php';    
include'includes/paging.php';

$mod = $_GET['m'] ?? '';
$act = $_GET['act'] ?? '';

// Array Ratio Index (RI) untuk AHP
$nRI = array (
    1=>0, 2=>0, 3=>0.58, 4=>0.9, 5=>1.12, 6=>1.24, 7=>1.32,
    8=>1.41, 9=>1.46, 10=>1.49, 11=>1.51, 12=>1.48, 13=>1.56, 14=>1.57, 15=>1.59
);

// Ambil Alternatif
$rows = $db->get_results("SELECT kode_alternatif, tittle FROM tb_alternatif ORDER BY kode_alternatif");
foreach($rows as $row){
    $ALTERNATIF[$row->kode_alternatif] = $row->tittle;
}

// Ambil Kriteria
$rows = $db->get_results("SELECT kode_kriteria, nama_kriteria, atribut, bobot FROM tb_kriteria ORDER BY kode_kriteria");
foreach($rows as $row){
    $KRITERIA[$row->kode_kriteria] = array(
        'nama_kriteria'=>$row->nama_kriteria,
        'atribut'=>$row->atribut,
        'bobot' => $row->bobot ?? 0
    );
}

/* =========================================================
   BAGIAN AHP (Tidak Berubah)
   ========================================================= */

function AHP_get_relkriteria() {
    global $db;
    $kriteria = $db->get_results("SELECT kode_kriteria FROM tb_kriteria");
    $matriks = [];
    foreach ($kriteria as $k1) {
        foreach ($kriteria as $k2) {
            if ($k1->kode_kriteria == $k2->kode_kriteria) {
                $matriks[$k1->kode_kriteria][$k2->kode_kriteria] = 1;
            } else {
                $q = $db->get_row("SELECT nilai FROM tb_rel_kriteria WHERE ID1='$k1->kode_kriteria' AND ID2='$k2->kode_kriteria'");
                if ($q) {
                    $matriks[$k1->kode_kriteria][$k2->kode_kriteria] = $q->nilai;
                } else {
                    $q2 = $db->get_row("SELECT nilai FROM tb_rel_kriteria WHERE ID1='$k2->kode_kriteria' AND ID2='$k1->kode_kriteria'");
                    if ($q2)
                        $matriks[$k1->kode_kriteria][$k2->kode_kriteria] = 1 / $q2->nilai;
                    else
                        $matriks[$k1->kode_kriteria][$k2->kode_kriteria] = 1;
                }
            }
        }
    }
    return $matriks;
}

function AHP_get_relalternatif($kriteria=''){
    global $db;
    $rows = $db->get_results("SELECT * FROM tb_rel_alternatif WHERE kode_kriteria='$kriteria' ORDER BY kode1, kode2");
    $matriks = array();
    foreach($rows as $row){
        $matriks[$row->kode1][$row->kode2] = $row->nilai;
    }
    return $matriks;
}

function get_kriteria_option($selected = 0){
    global $KRITERIA;  
    foreach($KRITERIA as $key => $value){
        if($key==$selected)
            $a.="<option value='$key' selected>$value[nama_kriteria]</option>";
        else
            $a.="<option value='$key'>$value[nama_kriteria]</option>";
    }
    return $a;
}

function get_atribut_option($selected = ''){
    $atribut = array('benefit'=>'Benefit', 'cost'=>'Cost');   
    foreach($atribut as $key => $value){
        if($selected==$key)
            $a.="<option value='$key' selected>$value</option>";
        else
            $a.= "<option value='$key'>$value</option>";
    }
    return $a;
}

function AHP_get_alternatif_option($selected = ''){
    global $db;
    $rows = $db->get_results("SELECT kode_alternatif, tittle FROM tb_alternatif ORDER BY kode_alternatif");
    foreach($rows as $row){
        if($row->kode_alternatif==$selected)
            $a.="<option value='$row->kode_alternatif' selected>$row->kode_alternatif - $row->tittle</option>";
        else
            $a.="<option value='$row->kode_alternatif'>$row->kode_alternatif - $row->tittle</option>";
    }
    return $a;
}

function AHP_get_nilai_option($selected = ''){
    $nilai = array(
        '1' => 'Sama penting dengan',
        '2' => 'Mendekati sedikit lebih penting dari',
        '3' => 'Sedikit lebih penting dari',
        '4' => 'Mendekati lebih penting dari',
        '5' => 'Lebih penting dari',
        '6' => 'Mendekati sangat penting dari',
        '7' => 'Sangat penting dari',
        '8' => 'Mendekati mutlak dari',
        '9' => 'Mutlak sangat penting dari',
    );
    foreach($nilai as $key => $value){
        if($selected==$key)
            $a.="<option value='$key' selected>$key - $value</option>";
        else
            $a.= "<option value='$key'>$key - $value</option>";
    }
    return $a;
}

function AHP_get_total_kolom($matriks) {
    $total = [];
    foreach ($matriks as $baris) {
        foreach ($baris as $kolom => $nilai) {
            if (!isset($total[$kolom])) $total[$kolom] = 0;
            $total[$kolom] += $nilai;
        }
    }
    return $total;
}

function AHP_normalize($matriks, $total) {
    $normal = [];
    foreach ($matriks as $k1 => $baris) {
        foreach ($baris as $k2 => $nilai) {
            $normal[$k1][$k2] = $nilai / $total[$k2];
        }
    }
    return $normal;
}

function AHP_get_rata($normal) {
    $rata = [];
    foreach ($normal as $k => $baris) {
        $rata[$k] = array_sum($baris) / count($baris);
    }
    return $rata;
}

function AHP_mmult($matriks = array(), $rata = array()){ 
    $data = array();
    $rata = array_values($rata);
    foreach($matriks as $key => $value){
        if (!isset($data[$key])) {
            $data[$key] = 0; 
        }
        $no=0;
        foreach($value as $k => $v){
            $data[$key] += $v * $rata[$no];
            $no++;  
        }               
    }  
    return $data;
}

function AHP_consistency_measure($matriks, $rata){
    $matriks = AHP_mmult($matriks, $rata);    
    foreach($matriks as $key => $value){
        $data[$key]=$value/$rata[$key];        
    }
    return $data;
}

function AHP_get_eigen_alternatif($kriteria=array()){
    $data = array();
    foreach($kriteria as $key => $value){
        $kode_kriteria = $key;
        $matriks = AHP_get_relalternatif($kode_kriteria);
        $total = AHP_get_total_kolom($matriks);
        $normal = AHP_normalize($matriks, $total);
        $rata = AHP_get_rata($normal);
        $data[$kode_kriteria] = $rata;                
    }
    $new = array();
    foreach($data as $key => $value){
        foreach($value as $k => $v){
            $new[$k][$key] = $v;
        }
    }
    return $new;
}

function AHP_get_rank($array){
    $data = $array;
    arsort($data);
    $no=1;
    $new = array();
    foreach($data as $key => $value){
        $new[$key] = $no++;
    }
    return $new;
}

/* =========================================================
   BAGIAN TOPSIS (DIPERBAIKI)
   ========================================================= */

// [BARU] Helper untuk mengambil atribut (Benefit/Cost)
function get_atribut_kriteria() {
    global $db;
    $rows = $db->get_results("SELECT kode_kriteria, atribut FROM tb_kriteria");
    $data = array();
    foreach($rows as $row){
        $data[$row->kode_kriteria] = $row->atribut;
    }
    return $data;
}

// [BARU] Hitung Pembagi (Denominator) untuk Normalisasi Input User
function TOPSIS_get_pembagi($data) {
    $pembagi = array();
    foreach($data as $key => $val){
        foreach($val as $k => $v){
            $v = (float)$v;
            $pembagi[$k] = isset($pembagi[$k]) ? $pembagi[$k] + ($v * $v) : ($v * $v);
        }
    }
    foreach($pembagi as $k => $v){
        $pembagi[$k] = sqrt($v);
    }
    return $pembagi;
}

// [MODIFIKASI] Solusi Ideal Individu (5 Parameter)
function TOPSIS_solusi_ideal_individu($terbobot, $target_user, $bobot_ahp, $pembagi, $atribut){
    
    $ideal = [
        'positif' => [],
        'negatif' => []
    ];

    // 1. Kumpulkan data kolom database
    $data_kolom_db = [];
    foreach($terbobot as $id_alt => $val){
        foreach($val as $kode_kriteria => $nilai){
            $data_kolom_db[$kode_kriteria][] = $nilai;
        }
    }

    // 2. Loop per Kriteria
    foreach($bobot_ahp as $kode => $nilai_bobot){
        
        // --- A+ (Input User) ---
        $nilai_mentah = isset($target_user[$kode]) ? (float)$target_user[$kode] : 0;
        $nilai_pembagi = (isset($pembagi[$kode]) && $pembagi[$kode] != 0) ? $pembagi[$kode] : 1;
        
        $user_ternormalisasi = $nilai_mentah / $nilai_pembagi;
        $ideal['positif'][$kode] = $user_ternormalisasi * $nilai_bobot;

        // --- A- (Database Min/Max) ---
        $kolom_db = isset($data_kolom_db[$kode]) ? $data_kolom_db[$kode] : [0];
        $tipe = isset($atribut[$kode]) ? $atribut[$kode] : 'benefit';

        if($tipe == 'cost'){
            $ideal['negatif'][$kode] = max($kolom_db); // Mahal = Buruk
        } else {
            $ideal['negatif'][$kode] = min($kolom_db); // Rendah = Buruk
        }
    }

    return $ideal;
}

// [MODIFIKASI] Bisa menerima parameter filter (meskipun kita kirim false)
function TOPSIS_get_hasil_analisa($filter_query = false){
    global $db;
    
    $sql_filter = "";
    if($filter_query){
        $sql_filter = " AND a.id_query = '$filter_query' ";
    }

    $rows = $db->get_results("SELECT a.kode_alternatif, k.kode_kriteria, ra.nilai
        FROM tb_alternatif a 
            INNER JOIN tb_rel_alternatif ra ON ra.kode_alternatif=a.kode_alternatif
            INNER JOIN tb_kriteria k ON k.kode_kriteria=ra.kode_kriteria
        WHERE 1=1 $sql_filter
        ORDER BY a.kode_alternatif, k.kode_kriteria");
        
    $data = array();
    foreach($rows as $row){
        $data[$row->kode_alternatif][$row->kode_kriteria] = $row->nilai;
    }
    return $data;
}

function TOPSIS_hasil_analisa($dataAnalisa){
    global $ALTERNATIF, $KRITERIA;
    
    $r = "<thead><tr><th>Alternatif</th>";      
    if(!empty($dataAnalisa)){
        foreach($dataAnalisa[key($dataAnalisa)] as $key => $value){
            $nama = isset($KRITERIA[$key]['nama_kriteria']) ? $KRITERIA[$key]['nama_kriteria'] : $key;
            $r.= "<th>".$nama."</th>";
        }    
    }
    $r.= "</tr></thead><tbody>";

    foreach($dataAnalisa as $key => $value){
        $r.= "<tr>";
        $nama_alt = isset($ALTERNATIF[$key]) ? $ALTERNATIF[$key] : $key;
        $r.= "<th nowrap>".$nama_alt."</th>";
        foreach($value as $k => $v){
            $r.= "<td>".$v."</td>";
        }        
        $r.= "</tr>";
    }    
    $r.= "</tbody>";
    return $r;
}

function TOPSIS_nomalize($array, $max = true){
    $data = array();
    $kuadrat = array();
    
    foreach($array as $key => $value){     
        foreach($value as $k => $v){
            $v = (float)$v;
            $kuadrat[$k] = ($kuadrat[$k] ?? 0) + ($v * $v);
        }                
    }    
    
    foreach($array as $key => $value){                
        foreach($value as $k => $v){
            $div = sqrt($kuadrat[$k] ?? 1);
            $div = ($div == 0) ? 1 : $div;
            $data[$key][$k] = (float)$v / $div;
        }
    }
    return $data;
}

function TOPSIS_nomal_terbobot($array, $bobot){    
    $data = array();
    foreach($array as $key => $value){                
        foreach($value as $k => $v){
            $b = isset($bobot[$k]) ? (float)$bobot[$k] : 0;
            $data[$key][$k] = (float)$v * $b;
        }
    }    
    return $data;
}

function TOPSIS_jarak_solusi($array, $ideal){    
    $temp = array();
    foreach($array as $key => $value){                
        $sum_pos = 0;
        $sum_neg = 0;
        foreach($value as $k => $v){
            $v = (float)$v;
            $ip = isset($ideal['positif'][$k]) ? (float)$ideal['positif'][$k] : 0;
            $in = isset($ideal['negatif'][$k]) ? (float)$ideal['negatif'][$k] : 0;
            
            $sum_pos += pow(($v - $ip), 2);
            $sum_neg += pow(($v - $in), 2);
            
            $temp[$key]['positif'] = sqrt($sum_pos);
            $temp[$key]['negatif'] = sqrt($sum_neg);
        }
    }        
    return $temp;
}

function TOPSIS_preferensi($array){
    $temp = array();
    foreach($array as $key => $value){                
        $pos = $value['positif'];
        $neg = $value['negatif'];
        if(($pos + $neg) == 0){
            $temp[$key] = 0;
        } else {
            $temp[$key] = $neg / ($pos + $neg);
        }
    }    
    return $temp;
}

function get_rank($array){
    $data = $array;
    arsort($data);
    $no=1;
    $new = array();
    foreach($data as $key => $value){
        $new[$key] = $no++;
    }
    return $new;
}
function TOPSIS_get_hasil_analisa_by_query($id_query) {
    global $db;
    // Mengambil nilai alternatif HANYA untuk id_query tertentu
    $rows = $db->get_results("SELECT a.kode_alternatif, r.kode_kriteria, r.nilai 
                              FROM tb_alternatif a 
                              JOIN tb_rel_alternatif r ON a.kode_alternatif = r.kode_alternatif 
                              WHERE a.id_query = '$id_query'
                              ORDER BY a.kode_alternatif, r.kode_kriteria");
    $data = [];
    foreach($rows as $row){
        $data[$row->kode_alternatif][$row->kode_kriteria] = $row->nilai;
    }
    return $data;
}

?>