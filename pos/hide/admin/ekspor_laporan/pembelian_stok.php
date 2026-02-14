<?php
require '../../ceklogin.php';

$filter = "";
if (isset($_GET['bulan']) && $_GET['bulan'] !== "") {
    $bulan = $_GET['bulan'];
    $filter .= " AND MONTH(m.tanggalmasuk) = '$bulan'";
}
if (isset($_GET['tahun']) && $_GET['tahun'] !== "") {
    $tahun = $_GET['tahun'];
    $filter .= " AND YEAR(m.tanggalmasuk) = '$tahun'";
}

$query = "SELECT m.*, p.namaproduk 
          FROM masuk m 
          JOIN produk p ON m.idproduk = p.idproduk
          WHERE 1=1 $filter
          ORDER BY m.tanggalmasuk DESC";

$judulLaporan = 'Laporan Stok Masuk - ' . date('F', mktime(0, 0, 0, $bulan, 10)) . " $tahun";

$result = mysqli_query($c, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Ekspor ke PDF
if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
    require('../fpdf/fpdf.php');

    $pdf = new FPDF();
    $pdf->AddPage('P');
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Laporan Pembelian Stok',0,1,'C');

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,10,'No',1);
    $pdf->Cell(80,10,'Nama Produk',1);
    $pdf->Cell(30,10,'Jumlah',1);
    $pdf->Cell(60,10,'Tanggal Masuk',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',10);
    $no = 1;
    foreach ($data as $row) {
        $pdf->Cell(10,10,$no++,1);
        $pdf->Cell(80,10,$row['namaproduk'],1);
        $pdf->Cell(30,10,$row['jumlah'],1);
        $pdf->Cell(60,10,$row['tanggalmasuk'],1);
        $pdf->Ln();
    }

    $pdf->Output('I', 'Laporan_Stok_Masuk.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stok Masuk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Laporan Stok Masuk</h2>

    <a href="pembelian_stok.php?format=pdf&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-danger mt-3 mb-3">
        Ekspor ke PDF
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($data as $row):
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['namaproduk'] ?></td>
                    <td><?= $row['jumlah'] ?></td>
                    <td><?= $row['tanggalmasuk'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
