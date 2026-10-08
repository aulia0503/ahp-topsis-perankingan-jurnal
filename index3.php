<?php
// INCLUDE FILE FUNCTIONS STANDAR
include 'functions.php'; 

$mod = $_GET['m'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SPK AHP TOPSIS MURNI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/css/flatly-bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/general.css" rel="stylesheet">
    <style>
        body { min-height:100vh; margin:0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wrapper { display:flex; }
        
        /* --- SIDEBAR --- */
        .sidebar { width:240px; background: #222d32; position:fixed; top:0; bottom:0; left:0; color:#b8c7ce; z-index: 100; transition: all 0.3s; }
        .sidebar .brand { 
            height: 50px; line-height: 50px; 
            text-align:center; background: #e67e22; 
            font-size:18px; font-weight:bold; color: #fff; 
            box-shadow: 0 2px 2px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }
        .sidebar .menu-header { padding:12px 15px 5px 15px; font-size:11px; color: #7f95a0; text-transform:uppercase; font-weight: bold; letter-spacing: 1px; margin-top: 5px;}
        .sidebar ul { list-style:none; padding:0; margin:0; }
        .sidebar ul li a { display:block; padding:12px 15px; color:#b8c7ce; text-decoration:none; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar ul li a:hover { background: #1e282c; color:#fff; }
        .sidebar ul li.active > a { background: #2c3b41; border-left-color: #e67e22; color:#fff; font-weight:bold; }
        .sidebar ul li.active > a i { color: #e67e22; }

        /* --- RIGHT SIDE --- */
        .right-side { margin-left:240px; width: 100%; min-height: 100vh; display: flex; flex-direction: column; padding-bottom: 60px; }
        .content { padding:20px; flex: 1; }
        
        /* --- FOOTER --- */
        .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #ddd; color: #777; padding: 15px 0; z-index: 1000; }
        .footer-content { margin-left: 240px; text-align: center; padding-right: 20px; }

        @media (max-width: 768px) { 
            .sidebar { width: 0; overflow: hidden; }
            .right-side { margin-left: 0; }
            .footer-content { margin-left: 0; }
        }
    </style>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</head>
<body>

<div class="wrapper">
    <div class="sidebar">
        <div class="brand"><i class="glyphicon glyphicon-fire"></i> AHP TOPSIS</div>
        <ul>
            <div class="menu-header">MAIN MENU</div>
            <li class="<?= $mod==''?'active':'' ?>">
                <a href="index3.php"><i class="glyphicon glyphicon-home"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?= $mod=='hitung_global_alone'?'active':'' ?>">
                <a href="?m=hitung_global_alone"><i class="glyphicon glyphicon-sort-by-attributes-alt"></i> <span>Ranking Jurnal</span></a>
            </li>

            <div class="menu-header">SYSTEM</div>
            <li>
                <a href="aksi.php?act=logout"><i class="glyphicon glyphicon-log-out"></i> <span>Logout</span></a>
            </li>
        </ul>
    </div>

    <div class="right-side">
        <div class="content">
            <?php
            // Logika Routing
            if ($mod == 'hitung_global_alone') {
                if (file_exists('hitung_global_alone.php')) {
                    include 'hitung_global_alone.php';
                } else {
                    echo "<div class='alert alert-danger'>Halaman ranking (hitung_global_alone.php) tidak ditemukan.</div>";
                }
            } else {
                include 'home.php'; 
            }
            ?>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            &copy; <?=date('Y')?> <strong>SPK Pemilihan Jurnal</strong>. All rights reserved.
        </div>
    </div>
</div>

</body>
</html>