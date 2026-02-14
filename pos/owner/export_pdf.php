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
$judul = "Laporan Penjualan Toko Mamah Azis";
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

// --- Query data (Sama seperti di laporan.php) ---
$query = "
SELECT 
    p.idpenjualan AS id_penjualan, 
    p.tanggal,
    pr.namaproduk AS nama_produk,
    dp.jumlah,
    pr.hargajual  AS harga_jual,
    pr.hargamodal AS harga_modal
FROM penjualan p
INNER JOIN detailpenjualan dp ON p.idpenjualan = dp.idpenjualan
INNER JOIN produk pr ON dp.idproduk = pr.idproduk
";

// --- Tambahkan kondisi filter ---
if ($filter == 'harian') {
    $query .= " WHERE DATE(p.tanggal) = '$tanggal'";
} elseif ($filter == 'bulanan') {
    $query .= " WHERE MONTH(p.tanggal) = '$bulan' AND YEAR(p.tanggal) = '$tahun'";
} elseif ($filter == 'tahunan') {
    $query .= " WHERE YEAR(p.tanggal) = '$tahun'";
}

$query .= " ORDER BY p.tanggal DESC";

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
$w = array(8, 20, 20, 80, 20, 30, 30, 35, 35); 

// Teks header
$header = array('No', 'ID Penjualan', 'Tanggal', 'Nama Produk', 'Jumlah', 'Harga Jual', 'Harga Modal', 'Total Penjualan', 'Total Keuntungan');

// Cetak header
for($i=0; $i<count($header); $i++) {
    $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
}
$pdf->Ln();

// --- Isi Tabel dan Hitung Total ---
$pdf->SetFont('Arial','',8);
$no = 1;
$grand_total_penjualan = 0;
$grand_total_keuntungan = 0;

if (mysqli_num_rows($get) > 0) {
    while ($data = mysqli_fetch_assoc($get)) {
        // Data diambil dari query
        $harga_jual = (float)$data['harga_jual'];
        $harga_modal = (float)$data['harga_modal'];
        $jumlah = (int)$data['jumlah'];

        $total_penjualan = $harga_jual * $jumlah;
        $total_keuntungan = ($harga_jual - $harga_modal) * $jumlah;

        $grand_total_penjualan += $total_penjualan;
        $grand_total_keuntungan += $total_keuntungan;

        // Cetak baris data
        $pdf->Cell($w[0], 6, $no++, 1, 0, 'C');
        $pdf->Cell($w[1], 6, htmlspecialchars($data['id_penjualan']), 1, 0, 'C');
        $pdf->Cell($w[2], 6, date('d-m-Y', strtotime($data['tanggal'])), 1, 0, 'C');
        $pdf->Cell($w[3], 6, htmlspecialchars($data['nama_produk']), 1, 0, 'L');
        $pdf->Cell($w[4], 6, $jumlah, 1, 0, 'C');
        $pdf->Cell($w[5], 6, "Rp " . number_format($harga_jual, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($w[6], 6, "Rp " . number_format($harga_modal, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($w[7], 6, "Rp " . number_format($total_penjualan, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($w[8], 6, "Rp " . number_format($total_keuntungan, 0, ',', '.'), 1, 0, 'R');
        $pdf->Ln(); // Pindah baris
    }

    // Baris Total Keseluruhan
    $pdf->SetFont('Arial','B',8);
    // Gabungkan 7 kolom pertama
    $pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], 7, 'Total Keseluruhan', 1, 0, 'R'); 
    $pdf->Cell($w[7], 7, "Rp " . number_format($grand_total_penjualan, 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell($w[8], 7, "Rp " . number_format($grand_total_keuntungan, 0, ',', '.'), 1, 1, 'R'); // '1' agar pindah baris
} else {
    $pdf->Cell(280, 7, 'Tidak ada data penjualan', 1, 1, 'C');
}


// --- Output PDF ---
$filename = "Laporan_Penjualan_" . str_replace(" ", "_", strtolower($filter)) . "_" . date('YmdHis') . ".pdf";
$pdf->Output('I', $filename); // 'I' untuk tampil di browser, 'D' untuk langsung download
?>