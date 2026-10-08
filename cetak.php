<?php include 'functions.php'; ?>
<!doctype html>
<html>
<head>
    <title>Cetak Laporan</title>
    <style>
        body { font-family: Verdana; font-size: 12px; margin: 40px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        /* Paksa border muncul saat print */
        td, th { border: 1px solid #000 !important; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
        .text-center { text-align: center; }
        h1 { text-align: center; font-size: 18px; text-transform: uppercase; }
        
        @media print {
            .no-print { display: none; }
            /* Menghilangkan header/footer otomatis browser (url, date) jika diinginkan */
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <?php
    $m = $_GET['m'] ?? '';
    // Pastikan file benar-benar kriteria_cetak.php yang dipanggil
    if (is_file($m . '.php')) {
        include $m . '.php';
    } else {
        echo "<h3>File laporan [$m.php] tidak ditemukan.</h3>";
    }
    ?>

    <script>
        window.print();
        // Menutup jendela otomatis setelah print (opsional)
        // window.onafterprint = function() { window.close(); };
    </script>
</body>
</html>