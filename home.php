
<div class="page-header">
    <h1>Dashboard </h1>
	<small>Selamat Datang di Sistem Perankingan Jurnal</small>
</div>
<?php
// --- LOGIC PHP PENGAMBILAN DATA ---

// 1. Hitung Jumlah Data Ringkas
$total_jurnal = $db->get_var("SELECT COUNT(*) FROM tb_alternatif");
$total_kriteria = $db->get_var("SELECT COUNT(*) FROM tb_kriteria");
// Asumsi tb_query digunakan sebagai kategori bidang ilmu
$total_bidang = $db->get_var("SELECT COUNT(*) FROM tb_query");

// 2. Data untuk Grafik 1 (Lingkaran): Jumlah Jurnal per Bidang Ilmu
// Menggabungkan tabel alternatif dan query (bidang)
$grafik_bidang = $db->get_results("SELECT q.nama_query, COUNT(a.kode_alternatif) as jumlah 
                                   FROM tb_alternatif a 
                                   JOIN tb_query q ON a.id_query = q.id_query 
                                   GROUP BY q.nama_query");

// 3. Data untuk Grafik 2 (Batang): Sebaran Akreditasi
// Mengelompokkan berdasarkan kolom akreditasi
$grafik_sinta = $db->get_results("SELECT akreditasi, COUNT(*) as jumlah 
                                  FROM tb_alternatif 
                                  WHERE akreditasi != '' 
                                  GROUP BY akreditasi 
                                  ORDER BY akreditasi ASC");

// 4. Data Tabel: 3 Jurnal Terbaik per Masing-masing Bidang
// Kita mengambil data dari tb_alternatif yang diranking berdasarkan total per id_query
$top_jurnal_per_bidang = $db->get_results("
    SELECT * FROM (
        SELECT a.tittle, a.total, a.akreditasi, q.nama_query,
               (SELECT COUNT(*) FROM tb_alternatif a2 
                WHERE a2.id_query = a.id_query AND a2.total >= a.total) as urutan
        FROM tb_alternatif a
        JOIN tb_query q ON a.id_query = q.id_query
    ) as tabel_ranking
    WHERE urutan <= 3 AND total > 0
    ORDER BY nama_query ASC, total DESC
");
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Styling Tambahan Khusus Halaman Ini */
    .panel-stats { border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
    .panel-stats .panel-heading { color: #fff; padding: 15px; border: none; }
    .panel-stats .huge { font-size: 36px; font-weight: bold; line-height: 1; }
    .panel-stats .panel-footer { background: #fff; border-top: 1px solid #eee; color: #777; }
    
    .cta-box {
        background: linear-gradient(to right, #2c3e50, #4ca1af);
        color: #fff;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .cta-text h4 { margin-top: 0; font-weight: bold; color: #fff; }
    .cta-text p { margin-bottom: 0; opacity: 0.9; }
    .btn-cta {
        background: #fff; color: #2c3e50; font-weight: bold; border: none; padding: 10px 20px;
        border-radius: 50px; transition: transform 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .btn-cta:hover { transform: scale(1.05); color: #e67e22; text-decoration: none; }

    .chart-container { position: relative; height: 250px; width: 100%; }
</style>



<div class="cta-box">
    <div class="cta-text">
        <h4><i class="glyphicon glyphicon-user"></i> Ingin Hasil Sesuai Preferensi Sendiri?</h4>
        <p>Anda dapat mencoba melakukan simulasi perankingan dengan kriteria yang Anda tentukan sendiri.</p>
    </div>
    <div>
        <a href="index.php?m=form" class="btn btn-cta">
            Coba Mode Individu <i class="glyphicon glyphicon-arrow-right"></i>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-primary panel-stats">
            <div class="panel-heading" style="background-color: #3498db;">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="glyphicon glyphicon-book" style="font-size: 4em;"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= $total_jurnal ?></div>
                        <div>Total Jurnal</div>
                    </div>
                </div>
            </div>
            <a href="?m=alternatif">
                <div class="panel-footer">
                    <!-- <span class="pull-left">Lihat Data</span>
                    <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span> -->
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="panel panel-green panel-stats">
            <div class="panel-heading" style="background-color: #2ecc71;">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="glyphicon glyphicon-tags" style="font-size: 4em;"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= $total_bidang ?></div>
                        <div>Bidang Ilmu</div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <!-- <span class="pull-left">Kategori Terdaftar</span> -->
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-yellow panel-stats">
            <div class="panel-heading" style="background-color: #f1c40f;">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="glyphicon glyphicon-list-alt" style="font-size: 4em;"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= $total_kriteria ?></div>
                        <div>Kriteria Penilaian</div>
                    </div>
                </div>
            </div>
            <a href="?m=kriteria">
                <div class="panel-footer">
                    <!-- <span class="pull-left">Lihat Kriteria</span>
                    <span class="pull-right"><i class="glyphicon glyphicon-circle-arrow-right"></i></span> -->
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="glyphicon glyphicon-stats"></i> Sebaran Jurnal per Bidang Ilmu</h3>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="chartBidang"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="glyphicon glyphicon-certificate"></i> Statistik Akreditasi</h3>
            </div>
            <div class="panel-body">
                <div class="chart-container">
                    <canvas id="chartSinta"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="glyphicon glyphicon-star"></i> 3 Jurnal Terbaik per Bidang Ilmu</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr style="background: #f9f9f9;">
                                <th width="150">Bidang Ilmu</th>
                                <th width="50" class="text-center">Rank</th>
                                <th>Nama Jurnal</th>
                                <th>Akreditasi</th>
                                <th class="text-center">Total Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if(!$top_jurnal_per_bidang): ?>
                            <tr><td colspan="5" class="text-center">Belum ada data perhitungan.</td></tr>
                        <?php else: 
                            $current_bidang = "";
                            foreach($top_jurnal_per_bidang as $row): ?>
                            <tr>
                                <?php if($current_bidang != $row->nama_query): ?>
                                    <td rowspan="3" style="vertical-align: middle; font-weight: bold; background: #fff;">
                                        <?= $row->nama_query ?>
                                    </td>
                                    <?php $current_bidang = $row->nama_query; ?>
                                <?php endif; ?>

                                <td class="text-center">
                                    <?php if($row->urutan == 1): ?>
                                        <span class="label label-warning">1</span>
                                    <?php elseif($row->urutan == 2): ?>
                                        <span class="label label-default" style="background:#bdc3c7">2</span>
                                    <?php else: ?>
                                        <span class="label label-default" style="background:#cd7f32">3</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row->tittle ?></td>
                                <td><span class="label label-info"><?= $row->akreditasi ?></span></td>
                                <td class="text-center text-primary" style="font-weight:bold"><?= number_format($row->total, 4) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// --- KONFIGURASI CHART.JS ---

// 1. Data Chart Bidang (Lingkaran)
const ctxBidang = document.getElementById('chartBidang').getContext('2d');
const chartBidang = new Chart(ctxBidang, {
    type: 'doughnut', 
    data: {
        labels: [<?php foreach($grafik_bidang as $g) echo "'$g->nama_query',"; ?>],
        datasets: [{
            data: [<?php foreach($grafik_bidang as $g) echo "$g->jumlah,"; ?>],
            backgroundColor: [
                '#3498db', '#e74c3c', '#f1c40f', '#2ecc71', '#9b59b6', '#34495e', '#e67e22'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' }
        }
    }
});

// 2. Data Chart Sinta (Bar)
const ctxSinta = document.getElementById('chartSinta').getContext('2d');
const chartSinta = new Chart(ctxSinta, {
    type: 'bar',
    data: {
        labels: [<?php foreach($grafik_sinta as $g) echo "'$g->akreditasi',"; ?>],
        datasets: [{
            label: 'Jumlah Jurnal',
            data: [<?php foreach($grafik_sinta as $g) echo "$g->jumlah,"; ?>],
            backgroundColor: 'rgba(52, 152, 219, 0.7)',
            borderColor: 'rgba(52, 152, 219, 1)',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { 
                beginAtZero: true, 
                ticks: { stepSize: 1 } // Agar sumbu Y bilangan bulat
            }
        },
        plugins: {
            legend: { display: false } // Sembunyikan legenda karena cuma 1 warna
        }
    }
});
</script>