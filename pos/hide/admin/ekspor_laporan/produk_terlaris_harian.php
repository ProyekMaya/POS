<?php
require '../../ceklogin.php';

$tanggal = isset($_GET['tanggal']) && $_GET['tanggal'] !== '' ? $_GET['tanggal'] : date('Y-m-d');

$query = "SELECT
    dp.idproduk,
    p.namaproduk,
    SUM(dp.jumlah) as total_terjual,
    p.pcs_per_dus_ctn,
    GROUP_CONCAT(DISTINCT ps.idorder ORDER BY ps.idorder SEPARATOR ', ') AS daftar_id_penjualan
FROM detailpenjualan dp
JOIN produk p ON dp.idproduk = p.idproduk
JOIN penjualan ps ON dp.idpenjualan = ps.idorder
WHERE DATE(ps.tanggal) = '$tanggal'
GROUP BY dp.idproduk, p.namaproduk, p.pcs_per_dus_ctn
ORDER BY total_terjual DESC
";

$result = mysqli_query($c, $query);
$data = [];
$total_seluruh_produk_terjual_pcs = 0;
$total_seluruh_produk_terjual_dus_ctn_temp = 0;

// Fungsi untuk menghitung Jumlah (Dus/Ctn)
if (!function_exists('getJumlahCtnDus')) {
    function getJumlahCtnDus($jumlah_pcs, $pcs_per_dus_ctn) {
        if ($pcs_per_dus_ctn > 0) {
            $jumlah_dus_whole = floor($jumlah_pcs / $pcs_per_dus_ctn);
            $sisa_unit = $jumlah_pcs % $pcs_per_dus_ctn;

            if ($sisa_unit > 0) {
                return $jumlah_dus_whole . "";
            }
        } else {
            return $jumlah_pcs . " Pcs";
        }
    }
}

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
    $total_seluruh_produk_terjual_pcs += $row['total_terjual'];
    if ($row['pcs_per_dus_ctn'] > 0) {
        $total_seluruh_produk_terjual_dus_ctn_temp += floor($row['total_terjual'] / $row['pcs_per_dus_ctn']);
    }
}

// Ekspor ke PDF
if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
    require('../../fpdf/fpdf.php');

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Laporan Produk Terlaris Harian',0,1,'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Tanggal: ' . date('d F Y', strtotime($tanggal)), 0, 1,'C');

    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(10,10,'No',1,0,'C');
    $pdf->Cell(60,10,'Nama Produk',1,0,'C');
    $pdf->Cell(25,10,'Terjual (Pcs)',1,0,'C');
    $pdf->Cell(35,10,'Terjual (Dus/Ctn)',1,0,'C');
    $pdf->Cell(55,10,'Daftar ID penjualan',1,0,'C');
    $pdf->Ln();

    $pdf->SetFont('Arial','',8);
    $no = 1;
    foreach ($data as $row) {
        $total_terjual_pcs = $row['total_terjual'];
        $pcs_per_dus_ctn = $row['pcs_per_dus_ctn'];
        $total_terjual_ctn_dus = getJumlahCtnDus($total_terjual_pcs, $pcs_per_dus_ctn);
        $nama_produk_cell_width = 60;
        $daftar_id_penjualan_cell_width = 55;
        $nama_produk_lines = $pdf->GetStringWidth($row['namaproduk']) > $nama_produk_cell_width ? (ceil($pdf->GetStringWidth($row['namaproduk']) / $nama_produk_cell_width)) : 1;
        $daftar_penjualan_lines = $pdf->GetStringWidth($row['daftar_id_penjualan']) > $daftar_id_penjualan_cell_width ? (ceil($pdf->GetStringWidth($row['daftar_id_penjualan']) / $daftar_id_penjualan_cell_width)) : 1;
        $row_height = max($nama_produk_lines, $daftar_penjualan_lines, 1) * 5;

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->Cell(10, $row_height, $no++, 1, 0, 'C');
        $pdf->MultiCell($nama_produk_cell_width, 5, $row['namaproduk'], 1, 'L', false);
        $pdf->SetXY($x + 10 + $nama_produk_cell_width, $y);

        $pdf->Cell(25, $row_height, $total_terjual_pcs, 1, 0, 'C');
        $pdf->Cell(35, $row_height, $total_terjual_ctn_dus, 1, 0, 'C');
        $pdf->MultiCell($daftar_id_penjualan_cell_width, 5, $row['daftar_id_penjualan'], 1, 'L', false);
        $pdf->Ln();

    }

    if (empty($data)) {
        $pdf->Cell(0, 10, 'Tidak ada produk terlaris ditemukan untuk tanggal ini.', 1, 1, 'C');
    }
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(70, 10, 'Total Seluruh Produk Terjual (Pcs):', 1, 0, 'R');
    $pdf->Cell(25, 10, number_format($total_seluruh_produk_terjual_pcs), 1, 0, 'C');
    $pdf->Cell(35 + 55, 10, '', 1, 1, 'L');
    $pdf->Cell(95, 10, 'Total Seluruh Produk Terjual (Dus/Ctn):', 1, 0, 'R');
    $pdf->Cell(35, 10, number_format($total_seluruh_produk_terjual_dus_ctn_temp), 1, 0, 'C');
    $pdf->Cell(55, 10, '', 1, 1, 'L');
    
    $pdf->Output('I', 'Produk_Terlaris_Harian_'.$tanggal.'.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Produk Terlaris Harian</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>=
        table.table-bordered th,
        table.table-bordered td {
            white-space: nowrap;
            font-size: 0.85em;
        }
    </style>
</head>
<body class="container mt-4">
    <h2>Laporan Produk Terlaris Harian</h2>
    <h4>Tanggal: <?= date('d F Y', strtotime($tanggal)) ?></h4>
    <a href="produk_terlaris_harian.php?format=pdf&tanggal=<?= $tanggal ?>" class="btn btn-danger mt-3 mb-3">
        Ekspor ke PDF
    </a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Total Terjual (Pcs)</th>
                <th>Total Terjual (Ctn)</th>
                <th>Daftar ID penjualan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($data as $row):
                $total_terjual_pcs = $row['total_terjual'];
                $pcs_per_dus_ctn = $row['pcs_per_dus_ctn'];
                $total_terjual_ctn_dus = getJumlahCtnDus($total_terjual_pcs, $pcs_per_dus_ctn);
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['namaproduk']) ?></td>
                    <td><?= htmlspecialchars($total_terjual_pcs) ?></td>
                    <td><?= htmlspecialchars($total_terjual_ctn_dus) ?></td>
                    <td><?= htmlspecialchars($row['daftar_id_penjualan']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada produk terlaris ditemukan untuk tanggal ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end fw-bold">Total Seluruh Produk Terjual (Pcs)</td>
                <td class="fw-bold"><?= number_format($total_seluruh_produk_terjual_pcs) ?></td>
                <td colspan="2" class="fw-bold"></td> </tr>
            <tr>
                <td colspan="3" class="text-end fw-bold">Total Seluruh Produk Terjual (Dus/Ctn)</td>
                <td class="fw-bold"><?= number_format($total_seluruh_produk_terjual_dus_ctn_temp) ?></td>
                <td class="fw-bold"></td> </tr>
        </tfoot>
    </table>
</body>
</html>