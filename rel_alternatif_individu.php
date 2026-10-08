<?php
session_start();

if (isset($_GET['id_query'])) {
    $_SESSION['id_query'] = $_GET['id_query'];
}

/*
0 = semua bidang
1/2/3 = sesuai query
*/
$id_query = $_SESSION['id_query'] ?? 0;

$queries = $db->get_results("SELECT * FROM tb_query ORDER BY id_query ASC");
?>

<!-- ================= FILTER BUTTON ================= -->
<div class="btn-group" style="margin-bottom: 15px;">
    
    <!-- SEMUA -->
    <a href="?m=<?= $_GET['m'] ?>&id_query=0"
       class="btn btn-<?= ($id_query == 0 ? 'primary' : 'default') ?>">
       Semua
    </a>

    <?php foreach ($queries as $q): ?>
        <a href="?m=<?= $_GET['m'] ?>&id_query=<?= $q->id_query ?>"
           class="btn btn-<?= ($id_query == $q->id_query ? 'primary' : 'default') ?>">
            <?= $q->nama_query ?>
        </a>
    <?php endforeach; ?>
</div>


<div class="page-header">
    <h1>Nilai Bobot Alternatif</h1>
</div>


<div class="panel panel-default">
    <div class="panel-heading">
        <form class="form-inline">
            <input type="hidden" name="m" value="rel_alternatif" />
            <input type="hidden" name="id_query" value="<?= $id_query ?>" />

            <div class="form-group">
                <input class="form-control" type="text" name="q"
                    value="<?= set_value('q') ?>" placeholder="Pencarian..." />
            </div>

            <button class="btn btn-success">Refresh</button>
        </form>
    </div>


    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Alternatif</th>

                    <?php
                    $jumlah_kriteria = $db->get_var("SELECT COUNT(*) FROM tb_kriteria");

                    for ($a = 1; $a <= $jumlah_kriteria; $a++) {
                        echo "<th>C$a</th>";
                    }
                    ?>
<!-- 
                    <th>Aksi</th> -->
                </tr>
            </thead>

            <tbody>
            <?php
            $q = esc_field(set_value('q'));

            /* ================= QUERY DINAMIS ================= */
            if ($id_query == 0) {
                $rows = $db->get_results("
                    SELECT * FROM tb_alternatif
                    WHERE tittle LIKE '%$q%'
                    ORDER BY id_query,
                    CAST(SUBSTRING(kode_alternatif,2) AS UNSIGNED)
                ");
            } else {
                $rows = $db->get_results("
                    SELECT * FROM tb_alternatif
                    WHERE id_query='$id_query'
                    AND tittle LIKE '%$q%'
                    ORDER BY CAST(SUBSTRING(kode_alternatif,2) AS UNSIGNED)
                ");
            }

            /* kirim id_query ke TOPSIS */
            $data = TOPSIS_get_hasil_analisa($id_query);


            foreach ($rows as $row): ?>
                <tr>
                    <td><?= $row->kode_alternatif ?></td>
                    <td><?= $row->tittle ?></td>

                    <?php
                    if (!empty($data[$row->kode_alternatif])):
                        foreach ($data[$row->kode_alternatif] as $val):
                            echo "<td>$val</td>";
                        endforeach;
                    else:
                        for ($i=0;$i<$jumlah_kriteria;$i++)
                            echo "<td>-</td>";
                    endif;
                    ?>

                    <!-- <td>
                        <a class="btn btn-xs btn-warning"
                           href="?m=rel_alternatif_ubah&kode=<?= $row->kode_alternatif ?>&id_query=<?= $id_query ?>">
                           Edit
                        </a>
                    </td> -->
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>
