<?php

if (isset($_GET['id_query'])) {
    $_SESSION['id_query'] = intval($_GET['id_query']);
}
$id_query = $_SESSION['id_query'] ?? 1;
$queries = $db->get_results("SELECT * FROM tb_query ORDER BY id_query ASC");

// Ambil data hasil perangkingan
$data = $db->get_results("
    SELECT 
        h.rank,
        h.kode_alternatif,
        a.tittle,
        h.total
    FROM tb_hasil h
    INNER JOIN tb_alternatif a 
        ON a.kode_alternatif = h.kode_alternatif
    WHERE h.id_query = '$id_query'
    ORDER BY h.rank ASC
");

?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-sort-amount-down"></i> Hasil Perangkingan Alternatif
    </h3>
  </div>

  <div class="card-body">
    <table class="table table-bordered table-striped table-hover">
      <thead class="text-center">
        <tr>
          <th width="8%">Peringkatan</th>
          <th width="12%">Kode</th>
          <th>Nama Jurnal</th>
          <th width="15%">Nilai Preferensi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if($data):
          foreach($data as $row):
        ?>
        <tr>
          <td class="text-center text-primary">
            <?= $row->rank ?>
          </td>
          <td class="text-center">
            <?= $row->kode_alternatif ?>
          </td>
          <td>
            <?= $row->tittle ?>
          </td>
          <td class="text-center text-primary">
            <?= round($row->total, 4) ?>
          </td>
        </tr>
        <?php
          endforeach;
        else:
        ?>
        <tr>
          <td colspan="4" class="text-center">
            Data perangkingan belum tersedia.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
