<?php
include 'functions_individu.php';
// Mendapatkan modul dari URL
$mod = $_GET['m'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SPK AHP TOPSIS Jurnal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="assets/css/flatly-bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/general.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { min-height:100vh; margin:0; background-color: #f4f7f6; }
        .wrapper { display:flex; }

        /* SIDEBAR */
        .sidebar {
            width:240px;
            background:#2c3e50;
            position:fixed;
            top:0; bottom:0;
            color:#fff;
            z-index: 1000;
        }
        .sidebar .brand {
            padding:20px;
            text-align:center;
            background:#1a252f;
            font-size:18px;
            font-weight:bold;
            letter-spacing: 1px;
        }
        .sidebar .menu-header {
            padding:15px 15px 5px;
            font-size:11px;
            color:#95a5a6;
            text-transform:uppercase;
            font-weight: bold;
        }
        .sidebar ul { list-style:none; padding:0; margin:0; }
        .sidebar ul li a {
            display:block;
            padding:12px 20px;
            color:#ecf0f1;
            text-decoration:none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar ul li a i { margin-right: 10px; color:#bdc3c7; }
        .sidebar ul li a:hover {
            background:#34495e;
            border-left: 4px solid #18bc9c;
        }
        .sidebar ul li.active > a {
            background:#1a252f;
            border-left: 4px solid #18bc9c;
            font-weight:bold;
            color:#18bc9c;
        }
        .sidebar ul li.active > a i { color:#18bc9c; }
        .sidebar ul ul { background:#16222d; }
        .sidebar ul ul li a { padding-left:45px; font-size:13px; }

        /* CONTENT */
        .content {
            margin-left:240px;
            padding:30px;
            padding-bottom:100px; 
            width:100%;
        }

        /* DASHBOARD COMPONENTS */
        .cta-box {
            background: linear-gradient(135deg, #3d8b94, #2a636a);
            color: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .cta-text h4 { margin-top: 0; font-weight: bold; color: #fff; }
        .cta-text p { margin-bottom: 0; opacity: 0.9; }
        .btn-cta {
            background: #fff; color: #3d8b94; font-weight: bold; border: none; padding: 12px 25px;
            border-radius: 50px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-cta:hover { transform: scale(1.05); background: #f8f9fa; color: #224d53; text-decoration: none; }

        .panel-stats { border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .panel-stats .panel-heading { color: #fff; padding: 25px 15px; border: none; }
        .huge { font-size: 35px; font-weight: bold; }
        .chart-container { position: relative; height: 280px; width: 100%; }

        /* ================= FOOTER ================= */
        .footer {
            background: #fff; 
            color: #7f8c8d; 
            text-align: center;
            padding: 25px;
            font-size: 13px;
            border-top: 1px solid #e3e6f0; 
            margin-top: 50px; 
        }

        .footer-content {
            text-align: center;
        }

        @media (max-width: 768px) {
            .sidebar { width: 0; display: none; }
            .content { margin-left: 0; }
            .footer-content { margin-left: 0; }
        }
    </style>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</head>

<body>

<div class="wrapper">
    <div class="sidebar">
        <div class="brand"><i class="glyphicon glyphicon-stats"></i> AHP TOPSIS</div>
        <ul>
            <div class="menu-header">MAIN</div>
            <li class="<?= $mod==''?'active':'' ?>"><a href="?"><i class="glyphicon glyphicon-home"></i> Dashboard</a></li>
            <li class="<?= $mod=='form'?'active':'' ?>"><a href="?m=form"><i class="glyphicon glyphicon-edit"></i> Preferensi Individu</a></li>

            <div class="menu-header">MASTER DATA</div>
            <li>
                <a data-toggle="collapse" href="#menuKriteria"><i class="glyphicon glyphicon-th-large"></i> Kriteria <i class="glyphicon glyphicon-chevron-down pull-right"></i></a>
                <ul id="menuKriteria" class="collapse <?= in_array($mod,['kriteria','rel_kriteria'])?'in':'' ?>">
                    <li class="<?= $mod=='kriteria'?'active':'' ?>"><a href="?m=kriteria"><i class="glyphicon glyphicon-list"></i> Data Kriteria</a></li>
                    <li class="<?= $mod=='rel_kriteria'?'active':'' ?>"><a href="?m=rel_kriteria"><i class="glyphicon glyphicon-scale"></i> Bobot Kriteria</a></li>
                </ul>
            </li>
            <li>
                <a data-toggle="collapse" href="#menuAlternatif"><i class="glyphicon glyphicon-user"></i> Alternatif <i class="glyphicon glyphicon-chevron-down pull-right"></i></a>
                <ul id="menuAlternatif" class="collapse <?= in_array($mod,['alternatif','rel_alternatif'])?'in':'' ?>">
                    <li class="<?= $mod=='alternatif'?'active':'' ?>"><a href="?m=alternatif"><i class="glyphicon glyphicon-user"></i> Data Alternatif</a></li>
                    <li class="<?= $mod=='rel_alternatif'?'active':'' ?>"><a href="?m=rel_alternatif"><i class="glyphicon glyphicon-signal"></i> Bobot Alternatif</a></li>
                </ul>
            </li>

            <div class="menu-header">PROSES</div>
            <li class="<?= $mod=='hitung'?'active':'' ?>"><a href="?m=hitung"><i class="glyphicon glyphicon-random"></i> Perhitungan</a></li>
            <li class="<?= $mod=='ranking'?'active':'' ?>"><a href="?m=ranking"><i class="glyphicon glyphicon-list-alt"></i> Hasil Ranking</a></li>
            
            <div class="menu-header">SYSTEM</div>
            <li><a href="aksi_individu.php?act=logout"><i class="glyphicon glyphicon-log-out"></i> Logout</a></li>
        </ul>
    </div>

    <div class="content">
    <?php
    switch ($mod) {
        case 'hitung':
            include 'hitung_individu.php';
            break;
        case 'ranking':
            include 'hitung_ranking.php';
            break;
        case 'form':
            include 'form_individu.php';
            break;
        default:
            if($mod && file_exists($mod . '_individu.php')){
                include $mod . '_individu.php';
            } else {
                
                $total_jurnal = $db->get_var("SELECT COUNT(*) FROM tb_alternatif");
                $total_kriteria = $db->get_var("SELECT COUNT(*) FROM tb_kriteria");
                $total_bidang = $db->get_var("SELECT COUNT(*) FROM tb_query");

                $grafik_bidang = $db->get_results("SELECT q.nama_query, COUNT(a.kode_alternatif) as jumlah FROM tb_alternatif a JOIN tb_query q ON a.id_query = q.id_query GROUP BY q.nama_query");
                $grafik_sinta = $db->get_results("SELECT akreditasi, COUNT(*) as jumlah FROM tb_alternatif WHERE akreditasi != '' GROUP BY akreditasi ORDER BY akreditasi ASC");
                ?>
                
                <div class="page-header">
                    <h1>Dashboard </h1>
                    <small>Selamat Datang di Sistem Perankingan Jurnal</small>
                </div>

                <div class="cta-box">
                    <div class="cta-text">
                        <h4><i class="glyphicon glyphicon-user"></i> Ingin Hasil Sesuai Preferensi Sendiri?</h4>
                        <p>Simulasikan perankingan dengan bobot kriteria yang dapat Anda tentukan sendiri secara dinamis.</p>
                    </div>
                    <div>
                        <a href="?m=form" class="btn btn-cta">Coba Mode Individu <i class="glyphicon glyphicon-arrow-right"></i></a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-stats">
                            <div class="panel-heading" style="background-color: #3498db;">
                                <div class="row">
                                    <div class="col-xs-3"><i class="glyphicon glyphicon-book" style="font-size: 4em; opacity: 0.5;"></i></div>
                                    <div class="col-xs-9 text-right"><div class="huge"><?= $total_jurnal ?></div><div>Total Jurnal</div></div>
                                </div>
                            </div>
                            <a href="?m=alternatif"><div class="panel-footer">Lihat Data <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span><div class="clearfix"></div></div></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-stats">
                            <div class="panel-heading" style="background-color: #2ecc71;">
                                <div class="row">
                                    <div class="col-xs-3"><i class="glyphicon glyphicon-tags" style="font-size: 4em; opacity: 0.5;"></i></div>
                                    <div class="col-xs-9 text-right"><div class="huge"><?= $total_bidang ?></div><div>Bidang Ilmu</div></div>
                                </div>
                            </div>
                            <div class="panel-footer">Kategori Terdaftar<div class="clearfix"></div></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-stats">
                            <div class="panel-heading" style="background-color: #f1c40f;">
                                <div class="row">
                                    <div class="col-xs-3"><i class="glyphicon glyphicon-list-alt" style="font-size: 4em; opacity: 0.5;"></i></div>
                                    <div class="col-xs-9 text-right"><div class="huge"><?= $total_kriteria ?></div><div>Kriteria Penilaian</div></div>
                                </div>
                            </div>
                            <a href="?m=kriteria"><div class="panel-footer">Lihat Kriteria <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span><div class="clearfix"></div></div></a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong><i class="glyphicon glyphicon-stats"></i> Sebaran Jurnal per Bidang Ilmu</strong></div>
                            <div class="panel-body"><div class="chart-container"><canvas id="chartBidang"></canvas></div></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong><i class="glyphicon glyphicon-certificate"></i> Statistik Akreditasi</strong></div>
                            <div class="panel-body"><div class="chart-container"><canvas id="chartSinta"></canvas></div></div>
                        </div>
                    </div>
                </div>

                <h3 style="margin-top: 30px; margin-bottom: 20px;"><i class="glyphicon glyphicon-star"></i> 5 Jurnal Tertinggi Tiap Bidang</h3>
                <div class="row">
                    <?php
                    $bidang_list = $db->get_results("SELECT * FROM tb_query ORDER BY nama_query ASC");
                    foreach ($bidang_list as $bidang):
                        $top_jurnal = $db->get_results("SELECT tittle, total, akreditasi FROM tb_alternatif WHERE id_query = '$bidang->id_query' AND total > 0 ORDER BY total DESC LIMIT 5");
                        if($top_jurnal): ?>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading" style="background: #34495e; color: #fff;"><strong>Bidang: <?= $bidang->nama_query ?></strong></div>
                                <table class="table table-hover table-striped">
                                    <thead><tr><th>Rank</th><th>Nama Jurnal</th><th class="text-right">Skor</th></tr></thead>
                                    <tbody>
                                        <?php $n=1; foreach($top_jurnal as $j): ?>
                                        <tr>
                                            <td><span class="badge" style="background:#e67e22"><?= $n++ ?></span></td>
                                            <td><?= $j->tittle ?> <br><small class="text-muted">Akreditasi: <?= $j->akreditasi ?></small></td>
                                            <td class="text-right text-primary"><strong><?= round($j->total, 4) ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; endforeach; ?>
                </div>

                <script>
                // Chart Bidang
                new Chart(document.getElementById('chartBidang'), {
                    type: 'doughnut',
                    data: {
                        labels: [<?php foreach($grafik_bidang as $g) echo "'$g->nama_query',"; ?>],
                        datasets: [{ data: [<?php foreach($grafik_bidang as $g) echo "$g->jumlah,"; ?>], backgroundColor: ['#3498db', '#e74c3c', '#f1c40f', '#2ecc71', '#9b59b6'] }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });
                // Chart Akreditasi
                new Chart(document.getElementById('chartSinta'), {
                    type: 'bar',
                    data: {
                        labels: [<?php foreach($grafik_sinta as $g) echo "'$g->akreditasi',"; ?>],
                        datasets: [{ label: 'Jumlah Jurnal', data: [<?php foreach($grafik_sinta as $g) echo "$g->jumlah,"; ?>], backgroundColor: '#18bc9c' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
                </script>
            <?php }
    } ?>
    </div>
</div>

<div class="footer">
        &copy; <?=date('Y')?> <strong>AHP TOPSIS</strong> - Pemeringkatan Tempat Publikasi Artikel 
    </div>

</body>
</html>