<?php
// Pastikan pustaka FPDF sudah didownload dan diletakkan di folder yang sesuai
require '../fpdf/fpdf.php'; 
require '../ceklogin.php'; // koneksi $c di sini

// --- Ambil Filter dari URL ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// --- Tentukan Judul Laporan ---
$judul = "Laporan Produk Terjual Toko Mamah Azis";
if ($filter == 'harian') {
    $judul .= " - Tanggal: " . date('d-m-Y', strtotime($tanggal));
} elseif ($filter == 'bulanan') {
    $bulanArr = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
    $namaBulan = $bulanArr[(int)$bulan];
    $judul .= " - Bulan: $namaBulan Tahun: $tahun";
} elseif ($filter == 'tahunan') {
    $judul .= " - Tahun: $tahun";
} else {
    $judul .= " - Semua Data";
}

// --- Query Ringkasan Produk Terjual (Ganti query detail dengan query GROUP BY) ---
$query = "
SELECT 
    pr.namaproduk AS nama_produk,
    SUM(dp.jumlah) AS total_jumlah_terjual,
    pr.hargajual AS harga_jual_satuan,
    pr.hargamodal AS harga_modal_satuan,
    SUM(pr.hargajual * dp.jumlah) AS total_pendapatan,
    SUM((pr.hargajual - pr.hargamodal) * dp.jumlah) AS total_keuntungan
FROM penjualan p
INNER JOIN detailpenjualan dp ON p.idpenjualan = dp.idpenjualan
INNER JOIN produk pr ON dp.idproduk = pr.idproduk
";

// --- Tambahkan kondisi filter yang sama ---
if ($filter == 'harian') {
    $query .= " WHERE DATE(p.tanggal) = '$tanggal'";
} elseif ($filter == 'bulanan') {
    $query .= " WHERE MONTH(p.tanggal) = '$bulan' AND YEAR(p.tanggal) = '$tahun'";
} elseif ($filter == 'tahunan') {
    $query .= " WHERE YEAR(p.tanggal) = '$tahun'";
}

$query .= " 
GROUP BY pr.idproduk, pr.namaproduk, pr.hargajual, pr.hargamodal 
ORDER BY total_jumlah_terjual DESC
";

$get = mysqli_query($c, $query);
if (!$get) {
    die("Query gagal: " . mysqli_error($c));
}


// --- Inisialisasi FPDF (Landscape agar kolom cukup) ---
$pdf = new FPDF('L','mm','A4'); // 'L' untuk Landscape
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);

// Judul
$pdf->Cell(280, 7, $judul, 0, 1, 'C');
$pdf->Ln(5);


// --- Header Tabel ---
$pdf->SetFont('Arial','B',8);
// Lebar kolom (Total harus mendekati 280 untuk A4 Landscape)
$w = array(8, 70, 40, 40, 30, 45, 45); 

// Teks header
$header = array('No', 'Nama Produk', 'Harga Jual Satuan', 'Harga Modal Satuan', 'Qty Terjual', 'Total Pendapatan', 'Total Keuntungan');

// Cetak header
for($i=0; $i<count($header); $i++) {
    $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
}
$pdf->Ln();

// --- Isi Tabel dan Hitung Total ---
$pdf->SetFont('Arial','',8);
$no = 1;
$grand_total_qty = 0;
$grand_total_pendapatan = 0;
$grand_total_keuntungan = 0;

if (mysqli_num_rows($get) > 0) {
    while ($data = mysqli_fetch_assoc($get)) {
        // Data diambil dari query
        $harga_jual = (float)$data['harga_jual_satuan'];
        $harga_modal = (float)$data['harga_modal_satuan'];
        $total_jumlah_terjual = (int)$data['total_jumlah_terjual'];
        $total_pendapatan = (float)$data['total_pendapatan'];
        $total_keuntungan = (float)$data['total_keuntungan'];

        $grand_total_qty += $total_jumlah_terjual;
        $grand_total_pendapatan += $total_pendapatan;
        $grand_total_keuntungan += $total_keuntungan;

        // Cetak baris data
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, htmlspecialchars($data['nama_produk']), 1, 0, 'L');
        $pdf->Cell($w[2], 6, "Rp " . number_format($harga_jual, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($w[3], 6, "Rp " . number_format($harga_modal, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($w[4], 6, $total_jumlah_terjual . " pcs", 1, 0, 'C');
        $pdf->Cell($w[5], 6, "Rp " . number_format($total_pendapatan, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($w[6], 6, "Rp " . number_format($total_keuntungan, 0, ',', '.'), 1, 0, 'R');
        $pdf->Ln(); // Pindah baris
    }

    // Baris Total Keseluruhan
    $pdf->SetFont('Arial','B',8);
    // Gabungkan 4 kolom pertama
    $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3], 7, 'Total Keseluruhan', 1, 0, 'R'); 
    $pdf->Cell($w[4], 7, $grand_total_qty . " pcs", 1, 0, 'C');
    $pdf->Cell($w[5], 7, "Rp " . number_format($grand_total_pendapatan, 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell($w[6], 7, "Rp " . number_format($grand_total_keuntungan, 0, ',', '.'), 1, 1, 'R'); 
} else {
    $pdf->Cell(280, 7, 'Tidak ada data produk terjual', 1, 1, 'C');
}


// --- Output PDF ---
$filename = "Laporan_Produk_Terjual_" . str_replace(" ", "_", strtolower($filter)) . "_" . date('YmdHis') . ".pdf";
$pdf->Output('I', $filename); // 'I' untuk tampil di browser, 'D' untuk langsung download
?>