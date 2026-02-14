<?php
require '../ceklogin.php';
require '../fpdf/fpdf.php';

if (!isset($_GET['idp'])) {
    die("ID penjualan tidak ditemukan.");
}

$idp = $_GET['idp'];

$ambilnamakasir = mysqli_query($c,"SELECT * FROM penjualan p, kasir pl WHERE p.idkasir=pl.idkasir AND p.idorder='$idp'");
$np = mysqli_fetch_array($ambilnamakasir);
$namapel = $np['namakasir'];
$tanggal = date('d-m-Y', strtotime($np['tanggal']));


// Ekspor ke PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, 'Data penjualan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'ID penjualan: ' . $idp, 0, 1);
$pdf->Cell(0, 10, 'Nama kasir: ' . $namapel, 0, 1);
$pdf->Cell(0, 10, 'Tanggal penjualan: ' . $tanggal, 0, 1);

$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(10, 10, 'No', 1);
$pdf->Cell(60, 10, 'Nama Produk', 1);
$pdf->Cell(30, 10, 'Harga', 1);
$pdf->Cell(30, 10, 'Jumlah', 1);
$pdf->Cell(35, 10, 'Sub-total', 1);
$pdf->Ln();

$get = mysqli_query($c, "SELECT * FROM detailpenjualan p, produk pr WHERE p.idproduk=pr.idproduk AND idpenjualan='$idp'");
$i = 1;
$total = 0;

$pdf->SetFont('Arial', '', 12);

while ($p = mysqli_fetch_array($get)) {
    $namaproduk = $p['namaproduk'];
    // $desc = $p['deskripsi'];
    $hargajual = $p['hargajual'];
    $jumlah = $p['jumlah'];
    $subtotal = $jumlah * $hargajual;
    $total += $subtotal;

    $pdf->Cell(10, 10, $i++, 1);
        // $pdf->Cell(60, 10, $namaproduk . ' (' . $desc . ')', 1);
    $pdf->Cell(60, 10, $namaproduk, 1);
    $pdf->Cell(30, 10, 'Rp. ' . number_format($hargajual), 1);
    $pdf->Cell(30, 10, $jumlah, 1);
    $pdf->Cell(35, 10, 'Rp. ' . number_format($subtotal), 1);
    $pdf->Ln();
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 10, 'Total', 1);
$pdf->Cell(35, 10, 'Rp. ' . number_format($total), 1);

$pdf->Output("I", "penjualan_$idp.pdf");
?>
