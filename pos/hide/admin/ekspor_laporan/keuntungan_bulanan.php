<?php
require '../../ceklogin.php';

$bulan = isset($_GET['bulan']) && $_GET['bulan'] !== '' ? $_GET['bulan'] : date('m');
$namaBulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];
$bulanNama = $namaBulan[$bulan];

$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$query = "SELECT 
            ps.idorder AS id_penjualan,
            pr.namaproduk, 
            dp.jumlah, 
            pr.hargajual, 
            pr.hargamodal,
            (pr.hargajual - pr.hargamodal) AS keuntungan_per_item,
            (dp.jumlah * (pr.hargajual - pr.hargamodal)) AS total_keuntungan
          FROM detailpenjualan dp
          JOIN produk pr ON dp.idproduk = pr.idproduk
          JOIN penjualan ps ON dp.idpenjualan = ps.idorder
          WHERE MONTH(ps.tanggal) = '$bulan' AND YEAR(ps.tanggal) = '$tahun'
          ORDER BY ps.tanggal DESC";


$result = mysqli_query($c, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Export PDF
if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
    require('../fpdf/fpdf.php');

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(190, 10, 'Laporan Keuntungan Bulanan', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Bulan: ' . $bulanNama, 0, 1);

    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,10,'No',1);
    $pdf->Cell(25,10,'ID penjualan',1);
    $pdf->Cell(30,10,'Nama Produk',1);
    $pdf->Cell(30,10,'Harga Jual',1);
    $pdf->Cell(30,10,'Harga Modal',1);
    $pdf->Cell(30,10,'Untung per Item',1);
    $pdf->Cell(30,10,'Jumlah Terjual',1);
    $pdf->Cell(30,10,'Total Untung',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',10);
    $no = 1;
    $grandTotal = 0;
    foreach ($data as $row) {
        $pdf->Cell(10,10,$no++,1);
        $pdf->Cell(25,10,$row['id_penjualan'],1);
        $pdf->Cell(30,10,$row['namaproduk'],1);
        $pdf->Cell(30,10,number_format($row['hargajual']),1);
        $pdf->Cell(30,10,number_format($row['hargamodal']),1);
        $pdf->Cell(30,10,number_format($row['keuntungan_per_item']),1);
        $pdf->Cell(30,10,$row['jumlah'],1);
        $pdf->Cell(30,10,number_format($row['total_keuntungan']),1);
        $pdf->Ln();
        $grandTotal += $row['total_keuntungan'];
    }

    $pdf->Cell(160,10,'Total Keuntungan',1);
    $pdf->Cell(30,10,number_format($grandTotal),1);
    $pdf->Output('I', 'Laporan_Keuntungan_'.$bulan.'_'.$tahun.'.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuntungan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Laporan Keuntungan Bulanan</h2>
    <h4>Bulan: <?= $bulanNama ?></h4>

    <a href="keuntungan_bulanan.php?format=pdf&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-danger mt-3 mb-3">
        Ekspor ke PDF
    </a>

    <!-- Tabel Data -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>ID penjualan</th>
                <th>Nama Produk</th>
                <th>Harga Jual</th>
                <th>Harga Modal</th>
                <th>Untung per Item</th>
                <th>Jumlah Terjual</th>
                <th>Total Untung</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $grandTotal = 0;
            foreach ($data as $row):
                $grandTotal += $row['total_keuntungan'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['id_penjualan'] ?></td>
                    <td><?= $row['namaproduk'] ?></td>
                    <td><?= number_format($row['hargajual']) ?></td>
                    <td><?= number_format($row['hargamodal']) ?></td>
                    <td><?= number_format($row['keuntungan_per_item']) ?></td>
                    <td><?= $row['jumlah'] ?></td>
                    <td><?= number_format($row['total_keuntungan']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6">Total Keuntungan</th>
                <th><?= number_format($grandTotal) ?></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
