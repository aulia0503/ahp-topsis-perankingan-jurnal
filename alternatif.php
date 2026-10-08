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
$m = $_GET['m']; // Ambil modul saat ini

$queries = $db->get_results("SELECT * FROM tb_query ORDER BY id_query ASC");
?>

<div class="btn-group" style="margin-bottom: 15px;">
    
    <a href="?m=<?= $m ?>&id_query=0"
       class="btn btn-<?= ($id_query == 0 ? 'primary' : 'default') ?>">
       Semua
    </a>

    <?php foreach ($queries as $q): ?>
         <a href="?m=<?= $m ?>&id_query=<?= $q->id_query ?>"
            class="btn btn-<?= ($id_query == $q->id_query ? 'primary' : 'default') ?>">
            <?= $q->nama_query ?>
         </a>
    <?php endforeach; ?>
</div>

<div class="page-header">
    <h1>Alternatif</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <form class="form-inline">
            <input type="hidden" name="m" value="alternatif" />
            <div class="form-group">
                <input class="form-control" type="text" placeholder="Pencarian..." 
                       name="q" value="<?= set_value('q') ?>" />
            </div>
            <div class="form-group">
                <button class="btn btn-success"><span class="glyphicon glyphicon-refresh"></span> Refresh</button>
            </div>
            <div class="form-group">
                <a class="btn btn-primary" href="?m=alternatif_tambah"><span class="glyphicon glyphicon-plus"></span> Tambah</a>
            </div>
           <div class="form-group">
                <a class="btn btn-default" target="_blank" href="cetak.php?m=alternatif_cetak&id_query=<?= $id_query ?>">
                    <span class="glyphicon glyphicon-print"></span> Cetak
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Judul Alternatif</th>
                    <th>Bidang</th>
                    <th>Impact</th>
                    <th>Akreditasi</th>
                    <th>Publikasi</th>
                    <th>Indexing</th>
                    <th>Google Citation</th>
                    <th>APC</th>
                    <th>Aims & Scope</th>
                    <th>Link Jurnal</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $q = esc_field(set_value('q'));

            /* ================= QUERY DINAMIS DENGAN JOIN ================= */
            // Menggunakan JOIN agar bisa menampilkan nama_query dari tb_query
            $where = "WHERE a.tittle LIKE '%$q%'";
            if ($id_query != 0) {
                $where .= " AND a.id_query='$id_query'";
            }

            $rows = $db->get_results("
                SELECT a.*, b.nama_query 
                FROM tb_alternatif a 
                LEFT JOIN tb_query b ON b.id_query = a.id_query
                $where
                ORDER BY a.id_query, CAST(SUBSTRING(a.kode_alternatif,2) AS UNSIGNED)
            ");

            $no = 0;
            foreach ($rows as $row): ?>
                <tr>
                    <td><?= ++$no ?></td>
                    <td><?= $row->kode_alternatif ?></td>
                    <td><?= $row->tittle ?></td>
                    <td><?= $row->nama_query ?></td> <td><?= $row->impact ?></td>
                    <td><?= $row->akreditasi ?></td>
                    <td><?= $row->publikasi ?></td>
                    <td><?= $row->indexing ?></td>
                    <td><?= $row->google_citation ?></td>
                    <td><?= number_format($row->APC, 0, ',', '.') ?></td>
                    <td><?= $row->aims_scope ?></td>
                    <td class="text-center">
                        <?php if($row->url_jurnal): ?>
                            <a href="<?= $row->url_jurnal ?>" target="_blank" class="btn btn-xs btn-info">
                                <span class="glyphicon glyphicon-link"></span> Buka Jurnal
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    
                    <td class="nw">
                        <a class="btn btn-xs btn-warning" href="?m=alternatif_ubah&ID=<?= $row->kode_alternatif ?>">
                            <span class="glyphicon glyphicon-edit"></span>
                        </a>
                        <a class="btn btn-xs btn-danger" href="aksi.php?act=alternatif_hapus&ID=<?= $row->kode_alternatif ?>" onclick="return confirm('Hapus data?')">
                            <span class="glyphicon glyphicon-trash"></span>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>