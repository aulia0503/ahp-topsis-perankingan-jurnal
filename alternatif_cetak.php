<?php
// Ambil filter id_query dari URL
$id_query = $_GET['id_query'] ?? 0;

// Tentukan judul dinamis
$judul_bidang = "SEMUA BIDANG";
if ($id_query != 0) {
    $row_q = $db->get_row("SELECT nama_query FROM tb_query WHERE id_query='$id_query'");
    $judul_bidang = strtoupper($row_q->nama_query);
}
?>

<h1 class="text-center">LAPORAN DATA ALTERNATIF (JURNAL)</h1>
<h3 class="text-center">BIDANG: <?= $judul_bidang ?></h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th width="30">No</th>
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
        </tr>
    </thead>
    <tbody>
    <?php
    // Query dinamis berdasarkan filter
    $where = "";
    if ($id_query != 0) {
        $where = " WHERE a.id_query='$id_query'";
    }

    $rows = $db->get_results("SELECT a.*, q.nama_query 
                              FROM tb_alternatif a 
                              LEFT JOIN tb_query q ON q.id_query = a.id_query 
                              $where
                              ORDER BY a.kode_alternatif ASC");
    $no = 1;
    if ($rows) :
        foreach($rows as $row): ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= $row->kode_alternatif ?></td>
                <td><?= $row->tittle ?></td>
                <td><?= $row->nama_query ?></td>
                <td><?= $row->impact ?></td>
                <td><?= $row->akreditasi ?></td>
                <td><?= $row->publikasi ?></td>
                <td><?= $row->indexing ?></td>
                <td><?= $row->google_citation ?></td>
                <td><?= number_format($row->APC, 0, ',', '.') ?></td>
                <td><?= $row->aims_scope ?></td>
                <td style="font-size: 11px;"><?= $row->url_jurnal ? $row->url_jurnal : '-' ?></td>
            </tr>
        <?php endforeach; 
    else: ?>
        <tr><td colspan="10" class="text-center">Data tidak ditemukan.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<p style="text-align:right; margin-top: 20px;">Dicetak pada: <?= date('d/m/Y H:i') ?></p>