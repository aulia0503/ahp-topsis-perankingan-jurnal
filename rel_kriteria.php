<div class="page-header">
    <h1>Nilai Bobot Kriteria (AHP)</h1>
</div>

<style>
    /* Style untuk mempertebal tabel agar tidak menyatu dengan background */
    .table-thick {
        border: 2px solid #333 !important;
        background-color: #ffffff; /* Memastikan background tabel putih bersih */
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Memberi efek bayangan agar menonjol */
    }
    .table-thick thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #333 !important;
    }
    .table-thick td, .table-thick th {
        border: 1px solid #999 !important; /* Warna border sel lebih gelap */
        text-align: center;
        vertical-align: middle !important;
    }
    /* Warna sel khusus seperti di gambar */
    .bg-diagonal { background-color: #1abc9c !important; color: white; font-weight: bold; }
    .bg-upper { background-color: #e74c3c !important; color: white; }
</style>

<?php
if ($_POST) include 'aksi.php';

$KR = $db->get_results("SELECT kode_kriteria, nama_kriteria FROM tb_kriteria ORDER BY kode_kriteria");
$criterias = [];
foreach ($KR as $r) {
    $criterias[$r->kode_kriteria] = $r->nama_kriteria;
}

$matriks = AHP_get_relkriteria();
$total = AHP_get_total_kolom($matriks);
?>

<div class="panel panel-default" style="border: 1px solid #ccc;">
    <div class="panel-heading">
        <form class="form-inline" method="post">
            <div class="form-group">
                <select class="form-control" name="ID1">
                    <?= get_kriteria_option($_POST['ID1'] ?? '') ?>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" name="nilai">
                    <?= AHP_get_nilai_option($_POST['nilai'] ?? '') ?>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" name="ID2">
                    <?= get_kriteria_option($_POST['ID2'] ?? '') ?>
                </select>
            </div>
            <button class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span> Ubah</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-thick">
            <thead>
                <tr>
                    <th>Kode</th>
                    <?php foreach ($matriks as $key => $val): ?>
                        <th><?= $key ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_index = 0;
                foreach ($matriks as $key => $rowValues): 
                    $row_index++;
                ?>
                    <tr>
                        <th class="nw" style="background-color: #f1f1f1;"><?= $key ?></th>
                        <?php 
                        $col_index = 0;
                        foreach ($rowValues as $k => $dt): 
                            $col_index++;
                            // Logika pewarnaan seperti di gambar
                            $class = '';
                            if ($row_index == $col_index) $class = 'bg-diagonal'; // Diagonal hijau
                            elseif ($col_index > $row_index) $class = 'bg-upper'; // Atas diagonal merah
                        ?>
                            <td class="<?= $class ?>">
                                <?php 
                                    // MENGATASI MASALAH DESIMAL: 
                                    // number_format memaksa tampilan 2 digit desimal (3.00)
                                    echo number_format($dt, 2); 
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #fcf8e3; font-weight: bold;">
                    <th>Total Kolom</th>
                    <?php foreach ($total as $t): ?>
                        <td><?= number_format($t, 3) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading"><strong>Matriks Bobot Prioritas & Konsistensi</strong></div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="nw">
                        <th>Kode</th>
                        <?php foreach ($matriks as $key => $val): ?>
                            <th><?= $key ?></th>
                        <?php endforeach; ?>
                        <th class="warning">Bobot (Prioritas)</th>
                        <th class="danger">CM (λ)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $normal = AHP_normalize($matriks, $total);
                $rata = AHP_get_rata($normal);
                $cm = AHP_consistency_measure($matriks, $rata);

                foreach ($normal as $key => $value): ?>
                    <tr>
                        <th class="nw"><?= $key ?></th>
                        <?php foreach ($value as $k => $v): ?>
                            <td><?= round($v, 3) ?></td>
                        <?php endforeach; ?>
                        <td class="warning"><strong><?= round($rata[$key], 3) ?></strong></td>
                        <td class="danger"><?= round($cm[$key], 3) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $n = count($matriks);
        if ($n > 1):
            $CI = (($n > 0) ? (array_sum($cm) / $n - $n) / ($n - 1) : 0);
            $RI = $nRI[$n] ?? 0;
            $CR = ($RI > 0) ? $CI / $RI : 0;
        ?>
        <div class="well well-sm" style="margin-top: 20px;">
            <p><strong>Consistency Index (CI):</strong> <?= round($CI, 3) ?></p>
            <p><strong>Ratio Index (RI):</strong> <?= round($RI, 3) ?></p>
            <p><strong>Consistency Ratio (CR):</strong> 
                <span class="<?= ($CR > 0.1) ? 'text-danger' : 'text-success' ?>" style="font-weight: bold;">
                    <?= round($CR, 3) ?> 
                    <?= ($CR > 0.1) ? "(Tidak Konsisten)" : "(Konsisten)" ?>
                </span>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>