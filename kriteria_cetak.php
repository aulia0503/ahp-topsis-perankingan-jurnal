<h1 class="text-center">LAPORAN DATA KRITERIA</h1>

<table class="table table-bordered">
    <thead>
        <tr>
            <th width="80" class="text-center">Kode</th>
            <th>Nama Kriteria</th>
            <th width="150">Atribut</th>
            <th width="100" class="text-center">Bobot</th>
        </tr>
    </thead>
    <tbody>
    <?php
    // Ambil data tanpa filter pencarian untuk laporan formal
    $rows = $db->get_results("SELECT * FROM tb_kriteria ORDER BY kode_kriteria");
    
    if($rows):
        foreach($rows as $row): ?>
        <tr>
            <td class="text-center"><?= $row->kode_kriteria ?></td>
            <td><?= $row->nama_kriteria ?></td>
            <td><?= ucfirst($row->atribut) ?></td>
            <td class="text-center"><?= $row->bobot ?></td>
        </tr>
        <?php endforeach; 
    else: ?>
        <tr><td colspan="4" class="text-center">Data kriteria kosong.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<p style="text-align:right; margin-top: 20px;">Dicetak pada: <?= date('d/m/Y H:i') ?></p>