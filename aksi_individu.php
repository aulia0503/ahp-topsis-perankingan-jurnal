<?php
require_once 'functions_individu.php';

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
   TAMBAH ALTERNATIF
=========================================== */
elseif ($mod == 'alternatif_tambah') {
    $kode            = esc_field($_POST['kode']);
    $query           = esc_field($_POST['query']);
    $impact          = esc_field($_POST['impact']);
    $akreditasi      = esc_field($_POST['akreditasi']);
    $publikasi       = esc_field($_POST['publikasi']);
    $indexing        = esc_field($_POST['indexing']);
    $google_citation = esc_field($_POST['google_citation']);
    $APC             = floatval($_POST['APC']);
    $aims_scope      = esc_field($_POST['aims_scope']);
    $tittle          = esc_field($_POST['tittle']);

    if ($kode == '' || $tittle == '') {
        print_msg("Field bertanda * tidak boleh kosong!");
    } elseif ($db->get_results("SELECT * FROM tb_alternatif WHERE kode_alternatif='$kode'")) {
        print_msg("Kode alternatif sudah ada!");
    } else {
        // insert lengkap dengan kolom baru
        $db->query("INSERT INTO tb_alternatif 
            (kode_alternatif, query, impact, akreditasi, publikasi, indexing, google_citation, APC, aims_scope, tittle, total, rank)
            VALUES 
            ('$kode', '$query', '$impact', '$akreditasi', '$publikasi', '$indexing', '$google_citation', '$APC', '$aims_scope', '$tittle', 0, 0)");

        // isi relasi alternatif default = 0 (AMAN UNTUK TOPSIS)
        $db->query("INSERT INTO tb_rel_alternatif (kode_alternatif, kode_kriteria, nilai)
                    SELECT '$kode', kode_kriteria, 0 FROM tb_kriteria");

        redirect_js("index.php?m=alternatif");
    }
}

/* ===========================================
   UBAH ALTERNATIF
=========================================== */
elseif ($mod == 'alternatif_ubah') {
    $kode            = esc_field($_POST['kode']);
    $query           = esc_field($_POST['query']);
    $impact          = esc_field($_POST['impact']);
    $akreditasi      = esc_field($_POST['akreditasi']);
    $publikasi       = esc_field($_POST['publikasi']);
    $indexing        = esc_field($_POST['indexing']);
    $google_citation = esc_field($_POST['google_citation']);
    $APC             = floatval($_POST['APC']);
    $aims_scope      = esc_field($_POST['aims_scope']);
    $tittle          = esc_field($_POST['tittle']);

    if ($kode == '' || $tittle == '') {
        print_msg("Field bertanda * tidak boleh kosong!");
    } else {
        $db->query("UPDATE tb_alternatif SET
                        query='$query',
                        impact='$impact',
                        akreditasi='$akreditasi',
                        publikasi='$publikasi',
                        indexing='$indexing',
                        google_citation='$google_citation',
                        APC='$APC',
                        aims_scope='$aims_scope',
                        tittle='$tittle'
                    WHERE kode_alternatif='$_GET[ID]'");

        redirect_js("index.php?m=alternatif");
    }
}

/* ===========================================
   HAPUS ALTERNATIF
=========================================== */
elseif ($act == 'alternatif_hapus') {
    $id = esc_field($_GET['ID']);
    $db->query("DELETE FROM tb_alternatif WHERE kode_alternatif='$id'");
    $db->query("DELETE FROM tb_rel_alternatif WHERE kode_alternatif='$id'");
    header("location:index.php?m=alternatif");
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
   TAMBAH KRITERIA
=========================================== */
elseif ($mod == 'kriteria_tambah') {
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $atribut = strtolower($_POST['atribut']);
    $bobot = $_POST['bobot'] ?? 0;

    if ($kode == '' || $nama == '' || $atribut == '')
        print_msg("Field bertanda * tidak boleh kosong!");
    elseif ($db->get_results("SELECT * FROM tb_kriteria WHERE kode_kriteria='$kode'"))
        print_msg("Kode sudah ada!");
    else {

        // Insert lengkap
        $db->query("INSERT INTO tb_kriteria 
                    (kode_kriteria, nama_kriteria, atribut, bobot) 
                    VALUES ('$kode', '$nama', '$atribut', '$bobot')");

        // Tambahkan relasi kriteria lengkap NxN
        foreach ($db->get_results("SELECT kode_kriteria FROM tb_kriteria") as $kr) {
            $k2 = $kr->kode_kriteria;
            $v  = ($kode == $k2) ? 1 : 1;

            // ID1 = kode baru, ID2 = existing
            $db->query("INSERT INTO tb_rel_kriteria (ID1, ID2, nilai)
                        VALUES ('$kode', '$k2', $v)");

            // kebalikannya
            if ($kode != $k2) {
                $db->query("INSERT INTO tb_rel_kriteria (ID1, ID2, nilai)
                            VALUES ('$k2', '$kode', 1)");
            }
        }

        // nilai alternatif default
        $db->query("INSERT INTO tb_rel_alternatif(kode_alternatif, kode_kriteria, nilai)
                    SELECT kode_alternatif, '$kode', 0 FROM tb_alternatif");

        redirect_js("index.php?m=kriteria");
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

    if ($kode == '' || $nama == '' || $atribut == '')
        print_msg("Field bertanda * tidak boleh kosong!");
    elseif ($db->get_results("SELECT * FROM tb_kriteria 
                              WHERE kode_kriteria='$kode' AND kode_kriteria<>'$_GET[ID]'"))
        print_msg("Kode sudah ada!");
    else {
        $db->query("UPDATE tb_kriteria 
                    SET kode_kriteria='$kode',
                        nama_kriteria='$nama',
                        atribut='$atribut',
                        bobot='$bobot'
                    WHERE kode_kriteria='$_GET[ID]'");

        redirect_js("index.php?m=kriteria");
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
    header("location:index.php?m=kriteria");
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
