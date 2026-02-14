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

$query = "SELECT * FROM produk 
          WHERE MONTH(updated_at) = '$bulan' AND YEAR(updated_at) = '$tahun'
          ORDER BY namaproduk ASC";
$result = mysqli_query($c, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Ekspor ke PDF
if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
    require('../fpdf/fpdf.php');

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(190, 10, 'Laporan Stok Barang', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Bulan: ' . $bulanNama, 0, 1);

    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(10, 10, 'No', 1);
    $pdf->Cell(50, 10, 'Nama Produk', 1);
    $pdf->Cell(50, 10, 'Deskripsi', 1);
    $pdf->Cell(30, 10, 'Harga', 1);
    $pdf->Cell(20, 10, 'Stok', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    $no = 1;
    foreach ($data as $row) {
        $pdf->Cell(10, 10, $no++, 1);
        $pdf->Cell(50, 10, $row['namaproduk'], 1);
        $pdf->Cell(50, 10, substr($row['deskripsi'], 0, 45), 1);
        $pdf->Cell(30, 10, 'Rp ' . number_format($row['hargajual'], 0, ',', '.'), 1);
        $pdf->Cell(20, 10, $row['stock'], 1);
        $pdf->Ln();
    }

    $pdf->Output('I', 'Laporan_Stok_' . $bulan . '_' . $tahun . '.pdf');
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stok Barang Bulanan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Laporan Stok Barang Bulanan</h2>
    <h4>Bulan: <?= $bulanNama ?></h4>

    <a href="stok_saat_ini.php?format=pdf&bulan=<?= $bulan ?>" class="btn btn-danger mt-3 mb-3">
        Ekspor ke PDF
    </a>

    <!-- Tabel -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <!-- <th>Deskripsi</th> -->
                <th>Harga Jual</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($data as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['namaproduk'] ?></td>
                    <!-- <td><= $row['deskripsi'] ?></td> -->
                    <td>Rp <?= number_format($row['hargajual'], 0, ',', '.') ?></td>
                    <td><?= $row['stock'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
