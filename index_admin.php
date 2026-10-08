<?php
include 'functions.php';
$mod = $_GET['m'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>SPK AHP TOPSIS</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="assets/css/flatly-bootstrap.min.css" rel="stylesheet">
<link href="assets/css/general.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
body{
  min-height:100vh;
  margin:0;
  font-family: Arial, sans-serif;
}
.wrapper{
  display:flex;
}

/* ================= SIDEBAR ================= */
.sidebar{
  width:240px;
  background:#102542; /* biru gelap */
  position:fixed;
  top:0;
  bottom:0;
  color:#fff;
}

.sidebar .brand{
  padding:15px;
  text-align:center;
  background:#0d47a1; /* biru tua */
  font-size:18px;
  font-weight:bold;
  letter-spacing:1px;
}

.sidebar .menu-header{
  padding:15px 15px 5px 15px;
  font-size:11px;
  color:#bbdefb;
  text-transform:uppercase;
  letter-spacing: 0.5px;
  opacity: 0.8;
}

.sidebar ul{
  list-style:none;
  padding:0;
  margin:0;
}

.sidebar ul li a{
  display:block;
  padding:12px 15px;
  color:#e3f2fd;
  text-decoration:none;
  transition:all .2s ease;
  line-height: 1.5;
}

/* Penyesuaian khusus untuk Font Awesome agar sejajar */
.sidebar ul li a i.fas, 
.sidebar ul li a i.glyphicon {
  width: 25px;
  color: #90caf9;
  vertical-align: middle;
  font-size: 14px;
}

/* HOVER */
.sidebar ul li a:hover{
  background:#1565c0;
}
.sidebar ul li a:hover i{
  color:#fff;
}

/* ACTIVE */
.sidebar ul li.active > a {
  background: #1976d2; 
  font-weight: bold;
  color: #fff;
  border-left: 4px solid #64b5f6; 
  padding-left: 11px; 
}
.sidebar ul li.active > a i{
  color:#fff;
}

/* SUBMENU */
.sidebar ul ul{
  background:#1c2a44;
}
.sidebar ul ul li a{
  padding-left:35px;
  font-size:13px;
}
.sidebar ul ul li.active > a {
  border-left: 4px solid #64b5f6;
  padding-left: 31px;
}

/* ================= CONTENT ================= */
.content{
  margin-left:240px;
  padding:20px;
  padding-bottom:60px;
  width:100%;
  background:#f5f7fb;
  min-height:100vh;
}

/* ================= FOOTER ================= */
.footer {
  background: #fff; 
  color: #7f8c8d; 
  text-align: center;
  padding: 20px;
  font-size: 13px;
  border-top: 1px solid #e3e6f0; 
  margin-top: 20px; 
}
</style>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</head>

<body>

<div class="wrapper">

<div class="sidebar">
  <div class="brand">
    <i class="fas fa-chart-bar"></i> AHP TOPSIS
  </div>

  <ul>

    <div class="menu-header">MAIN</div>
    <li class="<?= $mod==''?'active':'' ?>">
      <a href="?">
        <i class="fas fa-home"></i> Dashboard
      </a>
    </li>

    <div class="menu-header">MASTER DATA</div>
    <li>
      <a data-toggle="collapse" href="#menuKriteria">
        <i class="fas fa-th-large"></i> Kriteria
        <i class="fas fa-chevron-down pull-right" style="font-size: 11px; margin-top: 4px;"></i>
      </a>
      <ul id="menuKriteria" class="collapse <?= in_array($mod,['kriteria','rel_kriteria'])?'in':'' ?>">
        <li class="<?= $mod=='kriteria'?'active':'' ?>">
          <a href="?m=kriteria">
            <i class="fas fa-list"></i> Data Kriteria
          </a>
        </li>
        <li class="<?= $mod=='rel_kriteria'?'active':'' ?>">
          <a href="?m=rel_kriteria">
            <i class="fas fa-balance-scale"></i> Bobot Kriteria
          </a>
        </li>
      </ul>
    </li>

    <li>
      <a data-toggle="collapse" href="#menuAlternatif">
        <i class="fas fa-user"></i> Alternatif
        <i class="fas fa-chevron-down pull-right" style="font-size: 11px; margin-top: 4px;"></i>
      </a>
      <ul id="menuAlternatif" class="collapse <?= in_array($mod,['alternatif','rel_alternatif'])?'in':'' ?>">
        <li class="<?= $mod=='alternatif'?'active':'' ?>">
          <a href="?m=alternatif">
            <i class="fas fa-user"></i> Data Alternatif
          </a>
        </li>
        <li class="<?= $mod=='rel_alternatif'?'active':'' ?>">
          <a href="?m=rel_alternatif">
            <i class="fas fa-chart-line"></i> Bobot Alternatif
          </a>
        </li>
      </ul>
    </li>

    <div class="menu-header">PROSES</div>

    <li class="<?= $mod=='hitung'?'active':'' ?>">
      <a href="?m=hitung">
        <i class="fas fa-calculator"></i> Perhitungan
      </a>
    </li>

    <div class="menu-header">SYSTEM</div>
    
    <li class="<?= $mod=='ranking'?'active':'' ?>">
      <a href="?m=ranking">
        <i class="fas fa-sort-amount-down"></i> Ranking
      </a>
    </li>

    <li>
      <a href="aksi.php?act=logout">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </li>

  </ul>
</div>

<div class="content">
<?php
switch ($mod) {
    case 'hitung':
        include 'hitung.php';
        break;

    case 'ranking':
        include 'hitung_ranking_global.php';
        break;

    case 'kriteria':
    case 'kriteria_tambah':
    case 'kriteria_ubah':
    case 'rel_kriteria':
    case 'alternatif':
    case 'alternatif_tambah': 
    case 'alternatif_ubah':
    case 'rel_alternatif':
    case 'rel_alternatif_ubah':
        include $mod . '.php';
        break;

    default:
        include 'home_admin.php';
}
?>
</div>

</div>

<div class="footer">
      &copy; <?=date('Y')?> <strong>AHP TOPSIS</strong> - Pemeringkatan Tempat Publikasi Artikel 
</div>

</body>
</html>