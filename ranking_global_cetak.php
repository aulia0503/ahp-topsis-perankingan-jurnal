<?php
$filter = $_GET['filter'] ?? 'semua';
$judul_kategori = "SEMUA DATA (GLOBAL)";

if ($filter != 'semua') {
    $kat = $db->get_row("SELECT nama_query FROM tb_query WHERE id_query = '$filter'");
    if ($kat) $judul_kategori = strtoupper($kat->nama_query);
}

// PERBAIKAN: Tambahkan a.url_jurnal ke dalam SELECT
$sql = "SELECT a.kode_alternatif, a.tittle, a.total, a.url_jurnal, q.nama_query
        FROM tb_alternatif a
        LEFT JOIN tb_query q ON a.id_query = q.id_query
        WHERE a.total > 0";

if ($filter != 'semua') {
    $sql .= " AND a.id_query = '$filter'";
}

$sql .= " ORDER BY a.total DESC";
$rows = $db->get_results($sql);
?>

<h1>LAPORAN PERANGKINGAN JURNAL</h1>
<h3>KATEGORI: <?= $judul_kategori ?></h3>

<table class="table table-bordered">
    <thead>
        <tr style="background-color: #f4f4f4;">
            <th width="50" class="text-center">Rank</th>
            <th width="80">Kode</th>
            <th>Nama Jurnal</th>
            <?php if($filter == 'semua'): ?>
                <th>Bidang</th>
            <?php endif; ?>
            <th width="120" class="text-center">Total Skor (V)</th>
            <th>Link Jurnal</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($rows):
        $no = 1;
        foreach ($rows as $row): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= $row->kode_alternatif ?></td>
            <td><?= $row->tittle ?></td>
            <?php if($filter == 'semua'): ?>
                <td><?= $row->nama_query ?></td>
            <?php endif; ?>
            <td class="text-center"><?= number_format($row->total, 6) ?></td>
            <td style="font-size: 10px;"><?= isset($row->url_jurnal) ? $row->url_jurnal : '-' ?></td>
        </tr>
        <?php endforeach;
    else: ?>
        <tr>
            <td colspan="<?= ($filter == 'semua') ? '6' : '5' ?>" class="text-center">Data tidak ditemukan.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
