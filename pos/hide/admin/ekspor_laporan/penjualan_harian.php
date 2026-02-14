<?php
require '../../ceklogin.php';

$tanggal = isset($_GET['tanggal']) && $_GET['tanggal'] !== '' ? $_GET['tanggal'] : date('Y-m-d');

$query = "SELECT
            p.idorder AS id_penjualan,
            p.tanggal,
            pr.namaproduk,
            dp.jumlah,
            pr.hargajual,
            pr.hargamodal,
            pr.pcs_per_dus_ctn,
            (dp.jumlah * pr.hargajual) AS total_penjualan_produk,
            (dp.jumlah * (pr.hargajual - pr.hargamodal)) AS total_keuntungan
          FROM penjualan p
          JOIN detailpenjualan dp ON p.idorder = dp.idpenjualan
          JOIN produk pr ON dp.idproduk = pr.idproduk
          WHERE DATE(p.tanggal) = '$tanggal'
          ORDER BY p.tanggal DESC";


$result = mysqli_query($c, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Fungsi untuk menghitung Jumlah (Dus/Ctn)
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


// Ekspor ke PDF
if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
    require('../../fpdf/fpdf.php');

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(190,10,'Laporan Penjualan & Keuntungan Harian',0,1,'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Tanggal: ' . date('d-m-Y', strtotime($tanggal)), 0, 1,'C');

    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(8,10,'No',1,0,'C');
    $pdf->Cell(20,10,'ID penjualan',1,0,'C');
    $pdf->Cell(35,10,'Nama Produk',1,0,'C');
    $pdf->Cell(15,10,'Jml Pcs',1,0,'C');
    $pdf->Cell(25,10,'Jml Dus/Ctn',1,0,'C');
    $pdf->Cell(20,10,'Hrg Jual',1,0,'C');
    $pdf->Cell(20,10,'Hrg Modal',1,0,'C');
    $pdf->Cell(20,10,'Sub Total',1,0,'C');
    $pdf->Cell(20,10,'Total Untung',1,0,'C');
    $pdf->Ln();

    $pdf->SetFont('Arial','',8);
    $no = 1;
    $totalProdukTerjual = 0;
    $grandTotalPenjualan = 0;
    $grandTotalKeuntungan = 0;

    foreach ($data as $row) {
        $jumlah_pcs = $row['jumlah'];
        $pcs_per_dus_ctn = $row['pcs_per_dus_ctn'];
        $jumlah_ctn_dus = getJumlahCtnDus($jumlah_pcs, $pcs_per_dus_ctn);

        $pdf->Cell(8,10,$no++,1,0,'C');
        $pdf->Cell(20,10,$row['id_penjualan'],1,0,'C');
        $pdf->Cell(35,10,$row['namaproduk'],1);
        $pdf->Cell(15,10,$jumlah_pcs,1,0,'C');
        $pdf->Cell(25,10,$jumlah_ctn_dus,1);
        $pdf->Cell(20,10,number_format($row['hargajual']),1,0,'R');
        $pdf->Cell(20,10,number_format($row['hargamodal']),1,0,'R');
        $pdf->Cell(20,10,number_format($row['total_penjualan_produk']),1,0,'R');
        $pdf->Cell(20,10,number_format($row['total_keuntungan']),1,0,'R');
        $pdf->Ln();
        $totalProdukTerjual += $jumlah_pcs;
        $grandTotalPenjualan += $row['total_penjualan_produk'];
        $grandTotalKeuntungan += $row['total_keuntungan'];
    }

    if (empty($data)) {
        $pdf->Cell(0, 10, 'Tidak ada penjualan ditemukan untuk tanggal ini.', 1, 1, 'C');
    }
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(8 + 20 + 35,10,'Total Produk Terjual (Pcs)',1,0,'R');
    $pdf->Cell(15,10,number_format($totalProdukTerjual),1,0,'C');
    $pdf->Cell(25,10,'',1);
    $pdf->Cell(20 + 20 + 20 + 20,10,'',1);
    $pdf->Ln();
    $pdf->Cell(143,10,'Total Penjualan',1,0,'R');
    $pdf->Cell(20,10,number_format($grandTotalPenjualan),1,0,'R');
    $pdf->Cell(20,10,'',1);
    $pdf->Ln();

    $pdf->Cell(143,10,'Total Keuntungan',1,0,'R');
    $pdf->Cell(20,10,'',1);
    $pdf->Cell(20,10,number_format($grandTotalKeuntungan),1,0,'R');
    $pdf->Ln();

    $pdf->Output('I', 'Laporan_Penjualan_Harian.pdf');
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan Harian</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        table.table-bordered th,
        table.table-bordered td {
            white-space: nowrap;
            font-size: 0.85em;
        }
    </style>
</head>
<body class="container mt-4">
    <h2>Laporan Penjualan Harian</h2>
    <h4>Tanggal: <?= date('d-m-Y', strtotime($tanggal)) ?></h4>
    <a href="penjualan_harian.php?tanggal=<?= $tanggal ?>&format=pdf" class="btn btn-danger mt-3 mb-3">
        Ekspor ke PDF
    </a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>ID penjualan</th>
                <th>Nama Produk</th>
                <th>Jumlah (Pcs)</th>
                <th>Jumlah (Ctn)</th>
                <th>Harga Jual</th>
                <th>Harga Modal</th>
                <th>Total Penjualan</th>
                <th>Total Keuntungan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $totalProdukTerjual = 0;
            $grandTotalPenjualan = 0;
            $grandTotalKeuntungan = 0;

            foreach ($data as $row):
                $jumlah_pcs = $row['jumlah'];
                $pcs_per_dus_ctn = $row['pcs_per_dus_ctn'];
                $jumlah_ctn_dus = getJumlahCtnDus($jumlah_pcs, $pcs_per_dus_ctn);

                $totalProdukTerjual += $jumlah_pcs;
                $grandTotalPenjualan += $row['total_penjualan_produk'];
                $grandTotalKeuntungan += $row['total_keuntungan'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['id_penjualan'] ?></td>
                    <td><?= $row['namaproduk'] ?></td>
                    <td><?= $jumlah_pcs ?></td>
                    <td><?= $jumlah_ctn_dus ?></td>
                    <td><?= number_format($row['hargajual']) ?></td>
                    <td><?= number_format($row['hargamodal']) ?></td>
                    <td><?= number_format($row['total_penjualan_produk']) ?></td>
                    <td><?= number_format($row['total_keuntungan']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="9" class="text-center">Tidak ada penjualan ditemukan untuk tanggal ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total Produk Terjual (Pcs)</th>
                <th class="text-center"><?= number_format($totalProdukTerjual) ?></th>
                <th colspan="5"></th> </tr>
            <tr>
                <th colspan="7" class="text-end">Sub Total Penjualan</th>
                <th colspan="1" class="text-center"><?= number_format($grandTotalPenjualan) ?></th>
                <th></th> </tr>
            <tr>
                <th colspan="8" class="text-end">Sub Total Keuntungan</th>
                <th class="text-center"><?= number_format($grandTotalKeuntungan) ?></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>