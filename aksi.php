<?php
require_once 'functions.php';

$act = $_GET['act'] ?? '';
$mod = $_POST['m'] ?? $_GET['m'] ?? '';
/* ===========================================
   LOGIN
=========================================== */
if ($act == 'login') {
    $user = esc_field($_POST['user']);
    $pass = esc_field($_POST['pass']);

    $row = $db->get_row("SELECT * FROM tb_user 
                         WHERE user='$user' AND pass='$pass'");

    if ($row) {
        $_SESSION['login'] = $row->user;
        $_SESSION['level'] = strtolower($row->level ?? 'admin');
        redirect_js("index_admin.php");
    } else {
        print_msg("Salah kombinasi username dan password.");
    }
}

/* ===========================================
   GANTI PASSWORD
=========================================== */
elseif ($mod == 'password') {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    $pass3 = $_POST['pass3'];

    $row = $db->get_row("SELECT * FROM tb_user 
                         WHERE user='$_SESSION[login]' AND pass='$pass1'");

    if ($pass1 == '' || $pass2 == '' || $pass3 == '')
        print_msg("Field bertanda * tidak boleh kosong!");
    elseif (!$row)
        print_msg('Password lama salah.');
    elseif ($pass2 != $pass3)
        print_msg('Password baru dan konfirmasi tidak sama.');
    else {
        $db->query("UPDATE tb_user SET pass='$pass2' 
                    WHERE user='$_SESSION[login]'");
        print_msg('Password berhasil diubah.', 'success');
    }
}

/* ===========================================
   LOGOUT
=========================================== */
elseif ($act == 'logout') {
    unset($_SESSION['login']);
    header("location:index.html");
}

/* ===========================================
   TAMBAH ALTERNATIF & AUTO-SCORING
=========================================== */
elseif ($mod == 'alternatif_tambah') {
    $kode            = esc_field($_POST['kode']);
    $id_query        = esc_field($_POST['id_query']);
    $tittle          = esc_field($_POST['tittle']);
    $url_jurnal      = esc_field($_POST['url_jurnal']);
    $impact          = esc_field($_POST['impact']);
    $akreditasi      = esc_field($_POST['akreditasi']);
    $APC             = esc_field($_POST['APC']);
    $google_citation = esc_field($_POST['google_citation']);
    $publikasi       = esc_field($_POST['publikasi']);
    $aims_scope      = esc_field($_POST['aims_scope']);
    $indexing        = esc_field($_POST['indexing']);

    if ($kode == '' || $tittle == '' || $id_query == '') {
        print_msg("Field bertanda * tidak boleh kosong!");
    } else {
        $impact = str_replace(',', '.', $impact); 
$APC = str_replace(',', '.', $APC);
        // 1. Simpan ke tb_alternatif (Gunakan backtick pada `rank` karena ini reserved word)
        $db->query("INSERT INTO tb_alternatif 
            (kode_alternatif, id_query, url_jurnal, impact, akreditasi, publikasi, indexing, google_citation, APC, aims_scope, tittle, total, `rank`)
            VALUES 
            ('$kode', '$id_query', '$url_jurnal' , '$impact', '$akreditasi', '$publikasi', '$indexing', '$google_citation', '$APC', '$aims_scope', '$tittle', 0, 0)");

        // 2. PROSES AUTO-SCORING (Key harus sesuai kode_kriteria di database)
        $skoring = [
            'AK'  => get_skor_kriteria('AK', $akreditasi),
            'IND' => get_skor_kriteria('IND', $indexing),
            'APC' => get_skor_kriteria('APC', $APC),
            'AS'  => get_skor_kriteria('AS', $aims_scope),
            'WP'  => get_skor_kriteria('WP', $publikasi),
            'IF'  => get_skor_kriteria('IF', $impact),
            'GC'  => get_skor_kriteria('GC', $google_citation)
        ];

        foreach ($skoring as $kode_kriteria => $nilai) {
            // Gunakan REPLACE agar data otomatis masuk ke tb_rel_alternatif
            $db->query("REPLACE INTO tb_rel_alternatif (kode_alternatif, kode_kriteria, id_query, nilai)
                        VALUES ('$kode', '$kode_kriteria', '$id_query', '$nilai')");
        }

        redirect_js("index_admin.php?m=alternatif");
    }
}

/* ===========================================
    UBAH ALTERNATIF & AUTO-SCORING
=========================================== */
elseif ($mod == 'alternatif_ubah') {
    $kode            = esc_field($_POST['kode']);
    $id_query        = esc_field($_POST['id_query']);
    $tittle          = esc_field($_POST['tittle']);
    $url_jurnal      = esc_field($_POST['url_jurnal']);
    $impact          = str_replace(',', '.', esc_field($_POST['impact'])); 
    $akreditasi      = esc_field($_POST['akreditasi']);
    $APC             = str_replace(',', '.', esc_field($_POST['APC']));    
    $google_citation = esc_field($_POST['google_citation']);
    $publikasi       = esc_field($_POST['publikasi']);
    $aims_scope      = esc_field($_POST['aims_scope']);
    $indexing        = esc_field($_POST['indexing']);

    if ($tittle == '' || $id_query == '') {
        print_msg("Field bertanda * tidak boleh kosong!");
    } else {
        // 1. Update Tabel Master (tb_alternatif)
        $db->query("UPDATE tb_alternatif SET 
                        id_query='$id_query', 
                        tittle='$tittle', 
                        url_jurnal='$url_jurnal',
                        impact='$impact', 
                        akreditasi='$akreditasi', 
                        APC='$APC', 
                        google_citation='$google_citation', 
                        publikasi='$publikasi', 
                        aims_scope='$aims_scope', 
                        indexing='$indexing',
                        total=0,
                        `rank`=0
                    WHERE kode_alternatif='$kode'");

        // 2. Bersihkan data lama di tabel relasi untuk menghindari error "Duplicate Entry"
        $db->query("DELETE FROM tb_rel_alternatif WHERE kode_alternatif='$kode'");

        // 3. PROSES AUTO-SCORING
        $skoring = [
            'AK'  => get_skor_kriteria('AK', $akreditasi),
            'IND' => get_skor_kriteria('IND', $indexing),
            'APC' => get_skor_kriteria('APC', $APC),
            'AS'  => get_skor_kriteria('AS', $aims_scope),
            'WP'  => get_skor_kriteria('WP', $publikasi),
            'IF'  => get_skor_kriteria('IF', $impact),
            'GC'  => get_skor_kriteria('GC', $google_citation)
        ];

        foreach ($skoring as $kode_kriteria => $nilai) {
            $db->query("INSERT INTO tb_rel_alternatif (kode_alternatif, kode_kriteria, id_query, nilai) 
                        VALUES ('$kode', '$kode_kriteria', '$id_query', '$nilai')");
        }

        redirect_js("index_admin.php?m=alternatif");
    }
}
/* ===========================================
   UBAH REL ALTERNATIF (Edit Manual Skor)
=========================================== */
elseif ($act == 'rel_alternatif_ubah') {
    $kode_alternatif = $_POST['kode_alternatif'];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'ID-') !== false) {
            $id = str_replace('ID-', '', $key);
            $nilai = esc_field($value);
            $db->query("UPDATE tb_rel_alternatif SET nilai='$nilai' WHERE ID='$id'");
        }
    }
    header("location:index_admin.php?m=rel_alternatif_ubah&kode=$kode_alternatif&saved=1");
}
/* ===========================================
   HAPUS ALTERNATIF
=========================================== */
elseif ($act == 'alternatif_hapus') {
    $id = esc_field($_GET['ID']);
    $db->query("DELETE FROM tb_alternatif WHERE kode_alternatif='$id'");
    $db->query("DELETE FROM tb_rel_alternatif WHERE kode_alternatif='$id'");
    header("location:index_admin.php?m=alternatif");
}

/* ===========================================
   UBAH NILAI RELASI ALTERNATIF (BOBOT)
=========================================== */
elseif (isset($_POST['act']) && $_POST['act'] == 'rel_alternatif_ubah') {
    $kode = esc_field($_POST['kode_alternatif']);

    foreach ($_POST as $key => $val) {
        if (strpos($key, 'ID-') === 0) {
            $id = str_replace('ID-', '', $key);
            $nilai = floatval($val);
            $db->query("UPDATE tb_rel_alternatif SET nilai='$nilai' WHERE ID='$id'");
        }
    }

    // tampilkan SweetAlert sukses dan redirect otomatis
    echo "
    <html>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data bobot alternatif berhasil disimpan.',
            showConfirmButton: false,
            timer: 1800
        }).then(() => {
            window.location = 'index.php?m=rel_alternatif';
        });
        </script>
    </body>
    </html>";
    exit;
}

/* ===========================================
   TAMBAH KRITERIA (DIPERBAIKI TOTAL)
=========================================== */
elseif ($mod == 'kriteria_tambah') {
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $atribut = strtolower($_POST['atribut']);
    $bobot = $_POST['bobot'] ?? 0;

    if ($kode == '' || $nama == '' || $atribut == '') {
        print_msg("Field bertanda * tidak boleh kosong!");
    } elseif ($db->get_results("SELECT * FROM tb_kriteria WHERE kode_kriteria='$kode'")) {
        print_msg("Kode sudah ada!");
    } else {
        // 1. HITUNG ID MANUAL (Karena database tidak Auto Increment)
        $id_baru = $db->get_var("SELECT MAX(id_kriteria) FROM tb_kriteria") + 1;

        // 2. Simpan ke tb_kriteria (Sertakan id_kriteria)
        $db->query("INSERT INTO tb_kriteria (id_kriteria, kode_kriteria, nama_kriteria, atribut, bobot) 
                    VALUES ('$id_baru', '$kode', '$nama', '$atribut', '$bobot')");

        // 3. OTOMATIS: Tambahkan relasi NxN di tb_rel_kriteria
        // Tambahkan relasi kriteria baru terhadap dirinya sendiri (Nilai 1)
        $db->query("INSERT INTO tb_rel_kriteria (ID1, ID2, nilai) VALUES ('$kode', '$kode', 1)");

        // Tambahkan relasi kriteria baru terhadap kriteria yang sudah ada, dan sebaliknya
        $existing = $db->get_results("SELECT kode_kriteria FROM tb_kriteria WHERE kode_kriteria <> '$kode'");
        if($existing){
            foreach ($existing as $ex) {
                $db->query("INSERT INTO tb_rel_kriteria (ID1, ID2, nilai) VALUES ('$kode', '$ex->kode_kriteria', 1)");
                $db->query("INSERT INTO tb_rel_kriteria (ID1, ID2, nilai) VALUES ('$ex->kode_kriteria', '$kode', 1)");
            }
        }

        // 4. OTOMATIS: Daftarkan kriteria baru ini ke semua alternatif di tb_rel_alternatif (Nilai default 0)
        $db->query("INSERT INTO tb_rel_alternatif(kode_alternatif, kode_kriteria, id_query, nilai)
                    SELECT kode_alternatif, '$kode', id_query, 0 FROM tb_alternatif");

        redirect_js("index_admin.php?m=kriteria");
    }
}

/* ===========================================
   UBAH KRITERIA
=========================================== */
elseif ($mod == 'kriteria_ubah') {
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $atribut = strtolower($_POST['atribut']);
    $bobot = $_POST['bobot'] ?? 0;
    $id_lama = $_GET['ID'];

    if ($kode == '' || $nama == '' || $atribut == '') {
        print_msg("Field bertanda * tidak boleh kosong!");
    } else {
        $db->query("UPDATE tb_kriteria 
                    SET kode_kriteria='$kode', nama_kriteria='$nama', atribut='$atribut', bobot='$bobot'
                    WHERE kode_kriteria='$id_lama'");
        redirect_js("index_admin.php?m=kriteria");
    }
}

/* ===========================================
   HAPUS KRITERIA
=========================================== */
elseif ($act == 'kriteria_hapus') {
    $id = $_GET['ID'];
    $db->query("DELETE FROM tb_kriteria WHERE kode_kriteria='$id'");
    $db->query("DELETE FROM tb_rel_kriteria WHERE ID1='$id' OR ID2='$id'");
    $db->query("DELETE FROM tb_rel_alternatif WHERE kode_kriteria='$id'");
    header("location:index_admin.php?m=kriteria");
}


/* ===========================================
   RELASI KRITERIA (PAIRWISE)
=========================================== */
elseif ($mod == 'rel_kriteria') {
    $ID1 = $_POST['ID1'];
    $ID2 = $_POST['ID2'];
    $nilai = abs($_POST['nilai']);

    if ($ID1 == $ID2 && $nilai != 1)
        print_msg("Kriteria yang sama harus bernilai 1.");
    else {

        // jika belum ada record → INSERT
        $cek = $db->get_row("SELECT * FROM tb_rel_kriteria 
                             WHERE ID1='$ID1' AND ID2='$ID2'");
        if (!$cek) {
            $db->query("INSERT INTO tb_rel_kriteria (ID1, ID2, nilai)
                        VALUES ('$ID1', '$ID2', $nilai)");
        } else {
            // kalau sudah ada → UPDATE
            $db->query("UPDATE tb_rel_kriteria SET nilai=$nilai
                        WHERE ID1='$ID1' AND ID2='$ID2'");
        }

        // inverse = 1/nilai
        if ($ID1 != $ID2) {
            $db->query("UPDATE tb_rel_kriteria 
                        SET nilai = 1/$nilai
                        WHERE ID1='$ID2' AND ID2='$ID1'");
        }

        print_msg("Nilai kriteria berhasil diubah.", "success");
    }
}
