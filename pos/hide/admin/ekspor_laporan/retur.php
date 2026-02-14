<?php
require '../../fpdf/fpdf.php';
require '../ceklogin.php';

$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
$pelanggan = $_GET['pelanggan'] ?? '';
$produk = $_GET['produk'] ?? '';

$where = "WHERE 1=1";
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $where .= " AND DATE(p.tanggal) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
}
if (!empty($pelanggan)) {
    $where .= " AND pl.idpelanggan = '$pelanggan'";
}
if (!empty($produk)) {
    $where .= " AND pr.idproduk = '$produk'";
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Laporan Retur Produk',0,1,'C');
        $this->SetFont('Arial','B',10);
        $this->Cell(10,7,'No',1);
        $this->Cell(40,7,'Nama Pelanggan',1);
        $this->Cell(50,7,'Nama Produk',1);
        $this->Cell(20,7,'Jumlah',1);
        $this->Cell(30,7,'Tanggal',1);
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$query = "
SELECT 
    p.idorder,
    pl.namapelanggan,
    pr.namaproduk,
    dp.jumlah,
    p.tanggal
FROM detailpenjualan dp
JOIN penjualan p ON dp.idpenjualan = p.idorder
JOIN pelanggan pl ON p.idpelanggan = pl.idpelanggan
JOIN produk pr ON dp.idproduk = pr.idproduk
$where
ORDER BY p.tanggal DESC
";
$result = $c->query($query);

// Ekspor ke PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

$no = 1;
$total_jumlah = 0;

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(10,7,$no++,1);
    $pdf->Cell(40,7,$row['namapelanggan'],1);
    $pdf->Cell(50,7,$row['namaproduk'],1);
    $pdf->Cell(20,7,$row['jumlah'],1);
    $pdf->Cell(30,7,date('d-m-Y', strtotime($row['tanggal'])),1);
    $pdf->Ln();
    $total_jumlah += $row['jumlah'];
}

$pdf->SetFont('Arial','B',10);
$pdf->Cell(100,10,'Total jumlah Retur',1);
$pdf->Cell(50,10,$total_jumlah,1);
$pdf->Ln();

$pdf->Output();
?>
