<?php
require '../../ceklogin.php';

$masuk = mysqli_query($c, "SELECT m.*, p.namaproduk 
                              FROM masuk m 
                              JOIN produk p ON m.idproduk = p.idproduk 
                              ORDER BY m.tanggalmasuk DESC");

$keluar = mysqli_query($c, "SELECT dp.idproduk, p.namaproduk, SUM(dp.jumlah) as total_keluar 
                               FROM detailpenjualan dp 
                               JOIN produk p ON dp.idproduk = p.idproduk 
                               GROUP BY dp.idproduk");

$data_masuk = [];
while ($row = mysqli_fetch_assoc($masuk)) {
    $data_masuk[] = $row;
}

$data_keluar = [];
while ($row = mysqli_fetch_assoc($keluar)) {
    $data_keluar[] = $row;
}

// Ekspor ke PDF
if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
    require('../fpdf/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage('P');
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Laporan Stok Masuk dan Keluar',0,1,'C');

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Stok Masuk',0,1);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,10,'No',1);
    $pdf->Cell(70,10,'Nama Produk',1);
    $pdf->Cell(30,10,'Jumlah Masuk',1);
    $pdf->Cell(60,10,'Tanggal Masuk',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',10);
    $no = 1;
    foreach ($data_masuk as $row) {
        $pdf->Cell(10,10,$no++,1);
        $pdf->Cell(70,10,$row['namaproduk'],1);
        $pdf->Cell(30,10,$row['jumlah'],1);
        $pdf->Cell(60,10,$row['tanggalmasuk'],1);
        $pdf->Ln();
    }

    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Stok Keluar (Penjualan)',0,1);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,10,'No',1);
    $pdf->Cell(100,10,'Nama Produk',1);
    $pdf->Cell(60,10,'Total Keluar',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',10);
    $no = 1;
    foreach ($data_keluar as $row) {
        $pdf->Cell(10,10,$no++,1);
        $pdf->Cell(100,10,$row['namaproduk'],1);
        $pdf->Cell(60,10,$row['total_keluar'],1);
        $pdf->Ln();
    }

    $pdf->Output('I', 'Laporan_Stok_Keluar_Masuk.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stok Keluar & Masuk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Laporan Stok Masuk</h2>
    <a href="stok_keluar_masuk.php?format=pdf" class="btn btn-danger mb-3">Ekspor ke PDF</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah Masuk</th>
                <th>Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($data_masuk as $row):
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

    <h2 class="mt-5">Laporan Stok Keluar (Penjualan)</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Total Keluar</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($data_keluar as $row):
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['namaproduk'] ?></td>
                    <td><?= $row['total_keluar'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
