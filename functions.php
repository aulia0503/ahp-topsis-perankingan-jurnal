<?php
error_reporting(~E_NOTICE);
session_start();

// Hapus pengambilan session id_query karena tidak dipakai
// $id_query = $_SESSION['id_query'] ?? null;

include 'config.php';
include 'includes/db.php';
$db = new DB($config['server'], $config['username'], $config['password'], $config['database_name']);
include 'includes/general.php';
include 'includes/paging.php';

$mod = $_GET['m'] ?? '';
$act = $_GET['act'] ?? '';

$nRI = array(
    1 => 0,
    2 => 0,
    3 => 0.58,
    4 => 0.9,
    5 => 1.12,
    6 => 1.24,
    7 => 1.32,
    8 => 1.41,
    9 => 1.46,
    10 => 1.49,
    11 => 1.51,
    12 => 1.48,
    13 => 1.56,
    14 => 1.57,
    15 => 1.59
);

// Mengambil data Alternatif
$rows = $db->get_results("SELECT kode_alternatif, tittle FROM tb_alternatif ORDER BY kode_alternatif");
foreach ($rows as $row) {
    $ALTERNATIF[$row->kode_alternatif] = $row->tittle;
}

// Mengambil data Kriteria (Menghapus WHERE id_query)
$rows = $db->get_results("
    SELECT kode_kriteria, nama_kriteria, atribut, bobot
    FROM tb_kriteria
    ORDER BY kode_kriteria
");

foreach ($rows as $row) {
    $KRITERIA[$row->kode_kriteria] = array(
        'nama_kriteria' => $row->nama_kriteria,
        'atribut' => $row->atribut,
        'bobot' => $row->bobot ?? 0
    );
}

function AHP_get_relkriteria()
{
    global $db; // Hapus global $id_query

    // Ambil semua kriteria tanpa filter id_query
    $kriteria = $db->get_results("SELECT kode_kriteria FROM tb_kriteria ORDER BY kode_kriteria");
    $matriks = [];

    foreach ($kriteria as $k1) {
        foreach ($kriteria as $k2) {
            if ($k1->kode_kriteria == $k2->kode_kriteria) {
                $matriks[$k1->kode_kriteria][$k2->kode_kriteria] = 1;
            } else {
                // Hapus filter id_query pada query relasi
                $q = $db->get_row("
                    SELECT nilai FROM tb_rel_kriteria 
                    WHERE ID1='$k1->kode_kriteria' 
                    AND ID2='$k2->kode_kriteria'
                ");

                if ($q) {
                    $matriks[$k1->kode_kriteria][$k2->kode_kriteria] = $q->nilai;
                } else {
                    $q2 = $db->get_row("
                        SELECT nilai FROM tb_rel_kriteria 
                        WHERE ID1='$k2->kode_kriteria' 
                        AND ID2='$k1->kode_kriteria'
                    ");
                    $matriks[$k1->kode_kriteria][$k2->kode_kriteria] = $q2 ? 1 / $q2->nilai : 1;
                }
            }
        }
    }
    return $matriks;
}


function AHP_get_relalternatif($kriteria = '')
{
    global $db; // Hapus global $id_query

    // Hapus logic pengecekan id_query
    $sql = "
        SELECT * FROM tb_rel_alternatif 
        WHERE kode_kriteria='$kriteria'
        ORDER BY kode1, kode2
    ";

    $rows = $db->get_results($sql);
    $matriks = [];
    foreach ($rows as $row) {
        $matriks[$row->kode1][$row->kode2] = $row->nilai;
    }
    return $matriks;
}


function get_kriteria_option($selected = '')
{
    global $db;
    
    // Hapus id_query session check
    // Ambil data kriteria murni dari tabel
    $rows = $db->get_results("
        SELECT kode_kriteria, nama_kriteria
        FROM tb_kriteria
        ORDER BY kode_kriteria
    ");

    $a = '';
    foreach ($rows as $row) {
        $sel = ($row->kode_kriteria == $selected) ? 'selected' : '';
        $a .= "<option value='{$row->kode_kriteria}' $sel>
                   {$row->nama_kriteria}
               </option>";
    }
    return $a;
}

function get_atribut_option($selected = '')
{
    $atribut = array('benefit' => 'Benefit', 'cost' => 'Cost');
    $a = '';
    foreach ($atribut as $key => $value) {
        if ($selected == $key)
            $a .= "<option value='$key' selected>$value</option>";
        else
            $a .= "<option value='$key'>$value</option>";
    }
    return $a;
}

function AHP_get_alternatif_option($selected = '')
{
    global $db;
    $rows = $db->get_results("SELECT kode_alternatif, tittle FROM tb_alternatif ORDER BY kode_alternatif");
    $a = '';
    foreach ($rows as $row) {
        if ($row->kode_alternatif == $selected)
            $a .= "<option value='$row->kode_alternatif' selected>$row->kode_alternatif - $row->tittle</option>";
        else
            $a .= "<option value='$row->kode_alternatif'>$row->kode_alternatif - $row->tittle</option>";
    }
    return $a;
}

function AHP_get_nilai_option($selected = '')
{
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
    $a = '';
    foreach ($nilai as $key => $value) {
        if ($selected == $key)
            $a .= "<option value='$key' selected>$key - $value</option>";
        else
            $a .= "<option value='$key'>$key - $value</option>";
    }
    return $a;
}

function AHP_get_total_kolom($matriks)
{
    $total = [];
    foreach ($matriks as $baris) {
        foreach ($baris as $kolom => $nilai) {
            if (!isset($total[$kolom])) $total[$kolom] = 0;
            $total[$kolom] += $nilai;
        }
    }
    return $total;
}

function AHP_normalize($matriks, $total)
{
    $normal = [];
    foreach ($matriks as $k1 => $baris) {
        foreach ($baris as $k2 => $nilai) {
            $normal[$k1][$k2] = $nilai / $total[$k2];
        }
    }
    return $normal;
}


function AHP_get_rata($normal)
{
    $rata = [];
    foreach ($normal as $k => $baris) {
        $rata[$k] = array_sum($baris) / count($baris);
    }
    return $rata;
}


function AHP_mmult($matriks = array(), $rata = array())
{
    $data = array();
    $rata = array_values($rata);

    foreach ($matriks as $key => $value) {
        if (!isset($data[$key])) {
            $data[$key] = 0;
        }

        $no = 0;
        foreach ($value as $k => $v) {
            $data[$key] += $v * $rata[$no];
            $no++;
        }
    }
    return $data;
}


function AHP_consistency_measure($matriks, $rata)
{
    $matriks = AHP_mmult($matriks, $rata);
    $data = [];
    foreach ($matriks as $key => $value) {
        $data[$key] = $value / $rata[$key];
    }
    return $data;
}

function AHP_get_eigen_alternatif($kriteria = array())
{
    $data = array();
    foreach ($kriteria as $key => $value) {
        $kode_kriteria = $key;
        $matriks = AHP_get_relalternatif($kode_kriteria);
        $total = AHP_get_total_kolom($matriks);
        $normal = AHP_normalize($matriks, $total);
        $rata = AHP_get_rata($normal);
        $data[$kode_kriteria] = $rata;
    }
    $new = array();
    foreach ($data as $key => $value) {
        foreach ($value as $k => $v) {
            $new[$k][$key] = $v;
        }
    }
    return $new;
}

function AHP_get_rank($array)
{
    $data = $array;
    arsort($data);
    $no = 1;
    $new = array();
    foreach ($data as $key => $value) {
        $new[$key] = $no++;
    }
    return $new;
}

function TOPSIS_get_hasil_analisa($id_query = null)
{
    global $db; 

    // Jika id_query diberikan, filter datanya. Jika tidak, ambil semua.
    $where = "";
    if ($id_query) {
        $where = " WHERE a.id_query = '$id_query' ";
    }

    $sql = "
        SELECT a.kode_alternatif, k.kode_kriteria, ra.nilai
        FROM tb_alternatif a
        INNER JOIN tb_rel_alternatif ra ON ra.kode_alternatif=a.kode_alternatif
        INNER JOIN tb_kriteria k ON k.kode_kriteria=ra.kode_kriteria
        $where
        ORDER BY a.kode_alternatif, k.kode_kriteria
    ";

    $rows = $db->get_results($sql);

    $data = [];
    if($rows){
        foreach ($rows as $row) {
            $data[$row->kode_alternatif][$row->kode_kriteria] = $row->nilai;
        }
    }
    return $data;
}


function TOPSIS_hasil_analisa($echo = true)
{
    global $ALTERNATIF, $KRITERIA;

    $data = TOPSIS_get_hasil_analisa();

    if (!$echo) return $data;

    if (empty($data)) return "<tr><td colspan='10'>Data tidak tersedia</td></tr>";

    $r = "<tr><th></th>";
    foreach ($data[array_key_first($data)] as $key => $value) {
        $r .= "<th>" . $KRITERIA[$key]['nama_kriteria'] . "</th>";
    }
    $r .= "</tr>";

    foreach ($data as $key => $value) {
        $r .= "<tr><th>" . $ALTERNATIF[$key] . "</th>";
        foreach ($value as $v) {
            $r .= "<td>" . $v . "</td>";
        }
        $r .= "</tr>";
    }
    return $r;
}


function TOPSIS_nomalize($array, $max = true)
{
    $data = array();
    $kuadrat = array();

    // Hitung kuadrat
    foreach ($array as $key => $value) {
        foreach ($value as $k => $v) {
            $kuadrat[$k] = ($kuadrat[$k] ?? 0) + ($v * $v);
        }
    }

    // Normalisasi
    foreach ($array as $key => $value) {
        foreach ($value as $k => $v) {
            $div = sqrt($kuadrat[$k] ?? 1);
            $data[$key][$k] = $div == 0 ? 0 : ($v / $div);
        }
    }
    return $data;
}


function TOPSIS_nomal_terbobot($array, $bobot)
{
    $data = array();

    foreach ($array as $key => $value) {
        foreach ($value as $k => $v) {
            $data[$key][$k] = $v * $bobot[$k];
        }
    }

    return $data;
}

function TOPSIS_solusi_ideal($array)
{
    global $KRITERIA;

    $data = array();
    $temp = array();

    foreach ($array as $key => $value) {
        foreach ($value as $k => $v) {
            $temp[$k][] = $v;
        }
    }

    foreach ($temp as $key => $value) {
        $max = max($value);
        $min = min($value);

        if ($KRITERIA[$key]['atribut'] == 'benefit') {
            $data['positif'][$key] = $max ?? 0;
            $data['negatif'][$key] = $min ?? 0;
        } else {
            $data['positif'][$key] = $min ?? 0;
            $data['negatif'][$key] = $max ?? 0;
        }
    }
    return $data;
}


function TOPSIS_jarak_solusi($array, $ideal)
{
    $temp = array();
    $arr = array();
    foreach ($array as $key => $value) {
        foreach ($value as $k => $v) {
            $ip = $ideal['positif'][$k] ?? 0;
            $in = $ideal['negatif'][$k] ?? 0;

            $arr['positif'][$key][$k] = pow(($v - $ip), 2);
            $arr['negatif'][$key][$k] = pow(($v - $in), 2);

            $temp[$key]['positif'] = ($temp[$key]['positif'] ?? 0) + pow(($v - $ip), 2);
            $temp[$key]['negatif'] = ($temp[$key]['negatif'] ?? 0) + pow(($v - $in), 2);
        }
        $temp[$key]['positif'] = sqrt($temp[$key]['positif']);
        $temp[$key]['negatif'] = sqrt($temp[$key]['negatif']);
    }
    return $temp;
}

function TOPSIS_preferensi($array)
{
    global $KRITERIA;

    $temp = array();

    foreach ($array as $key => $value) {
        $pembagi = $value['positif'] + $value['negatif'];
        // Mencegah division by zero
        $temp[$key] = $pembagi == 0 ? 0 : ($value['negatif'] / $pembagi);
    }

    return $temp;
}

function get_rank($array)
{
    $data = $array;
    arsort($data);
    $no = 1;
    $new = array();
    foreach ($data as $key => $value) {
        $new[$key] = $no++;
    }
    return $new;
}
function get_next_kode_alternatif() {
    global $db;
    // Ambil semua nomor dari kode (A1, A2, dst)
    $rows = $db->get_results("SELECT kode_alternatif FROM tb_alternatif ORDER BY CAST(SUBSTRING(kode_alternatif, 2) AS UNSIGNED) ASC");
    
    $used_numbers = [];
    if($rows){
        foreach($rows as $row){
            $used_numbers[] = (int) substr($row->kode_alternatif, 1);
        }
    }

    // Cari angka terkecil yang belum terpakai (dimulai dari 1)
    $next_num = 1;
    while (in_array($next_num, $used_numbers)) {
        $next_num++;
    }
    
    return 'A' . $next_num;
}

// Fungsi untuk dropdown Bidang
function get_query_option($selected = '') {
    global $db;
    $rows = $db->get_results("SELECT * FROM tb_query ORDER BY id_query");
    $res = '';
    foreach($rows as $row){
        $s = ($row->id_query == $selected) ? 'selected' : '';
        $res .= "<option value='{$row->id_query}' $s>{$row->nama_query}</option>";
    }
    return $res;
}
// Fungsi Skoring Otomatis berdasarkan kriteria Anda
function get_skor_kriteria($tipe, $value) {
    if ($tipe == 'AK') { // Akreditasi
        if (stripos($value, 'Sinta 1') !== false) return 5;
        if (stripos($value, 'Sinta 2') !== false) return 4;
        if (stripos($value, 'Sinta 3') !== false) return 3;
        if (stripos($value, 'Sinta 4') !== false) return 2;
        return 1;
    }
    if ($tipe == 'APC') { // APC (Cost) - Diubah ke 2.5jt sesuai Excel
        $val = floatval(str_replace(',', '.', $value));
        if ($val == 0) return 5;
        if ($val <= 1000000) return 4;
        if ($val <= 2500000) return 3; // Sebelumnya 2000000
        if ($val <= 4000000) return 2;
        return 1;
    }
    if ($tipe == 'AS') { // Aims & Scope - Diubah ke >= 15 & >= 10
        $tags = explode(',', $value);
        $count = count(array_filter(array_map('trim', $tags)));
        if ($count >= 15) return 5; // Sebelumnya > 15
        if ($count >= 10) return 4; // Sebelumnya >= 11
        if ($count >= 6) return 3;
        if ($count >= 3) return 2;
        return 1;
    }
    if ($tipe == 'GC') { // Google Citation
        $val = floatval(str_replace(',', '.', $value)); 
        if ($val > 5000) return 5;
        if ($val > 2500) return 4;
        if ($val > 1000) return 3;
        if ($val > 500) return 2;
        return 1;
    }
    if ($tipe == 'IF') { // Impact Factor
        $val = floatval(str_replace(',', '.', $value));
        if ($val > 4) return 5;
        if ($val > 2) return 4;
        if ($val > 1) return 3;
        if ($val > 0.5) return 2;
        return 1;
    }
    if ($tipe == 'IND') { // Indexing
        if (stripos($value, 'Scopus') !== false) return 5;
        $tags = explode(',', $value);
        $count = count(array_filter(array_map('trim', $tags)));
        if ($count >= 6) return 4;
        if ($count >= 4) return 3;
        if ($count >= 2) return 2;
        return 1;
    }
    if ($tipe == 'WP') { // Waktu Proses - Diubah ke >=
        $tags = explode(',', $value);
        $val = count(array_filter(array_map('trim', $tags)));
        if ($val >= 6) return 5; // Sebelumnya > 6
        if ($val >= 4) return 4;
        if ($val >= 3) return 3;
        if ($val >= 2) return 2;
        return 1;
    }
    return 0;
}
?>