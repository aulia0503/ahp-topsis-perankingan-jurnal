<div class="container">
    <?php
    $id_query = $_GET['id_query'] ?? 0;
    
    if ($id_query == 0) {
        echo "<h3 class='text-center'>Pilih salah satu bidang untuk dicetak.</h3>";
    } else {
        $q_name = $db->get_row("SELECT nama_query FROM tb_query WHERE id_query='$id_query'");
    ?>
        <h1 class="text-center">Hasil Perangkingan AHP-TOPSIS</h1>
        <h4 class="text-center">Bidang: <?= $q_name->nama_query ?></h4>
        
        <table class="table table-bordered">
            <thead>
                <tr style="background-color: #f4f4f4;">
                    <th>Rank</th>
                    <!-- <th>Kode</th> -->
                    <th>Nama Jurnal</th>
                    <th>Total Skor</th>
                    <th>Link Jurnal</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Ambil data yang totalnya sudah dihitung (>0)
            $rows = $db->get_results("SELECT * FROM tb_alternatif 
                                      WHERE id_query = '$id_query' AND total > 0 
                                      ORDER BY total DESC");
            if ($rows):
                foreach($rows as $row): ?>
                <tr>
                    <td><strong><?= $row->rank ?></strong></td>
                    <td><?= $row->kode_alternatif ?></td>
                    <td><?= $row->tittle ?></td>
                    <td><?= number_format($row->total, 4) ?></td>
                    <td style="font-size: 11px;"><?= $row->url_jurnal ? $row->url_jurnal : '-' ?></td>
                </tr>
                <?php endforeach;
            else:
                echo "<tr><td colspan='4' class='text-center'>Data belum diproses hitung.</td></tr>";
            endif; ?>
            </tbody>
        </table>
    <?php } ?>
</div>