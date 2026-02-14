<?php
// Pastikan path ke file FPDF.php sudah benar
require '../fpdf/fpdf.php'; 
require '../ceklogin.php'; 

// --- START: Logika Akses Halaman ---
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'True' || $_SESSION['role'] !== 'owner') {
    header('location:../login.php');
    exit();
}
// --- END: Logika Akses Halaman ---

class PDF extends FPDF
{
    // Header halaman
    function Header()
    {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,7,'LAPORAN STOK BARANG HAMPIR HABIS',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'Toko Mamah Azis',0,1,'C');
        $this->Cell(0,5,'Dicetak pada ' . date('d-m-Y H:i:s'),0,1,'C');
        $this->Ln(10);
    }

    // Footer halaman
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Fungsi untuk membuat tabel
    function BuatTabel($header, $data)
    {
        // Lebar kolom
        $w = array(15, 120, 55);

        // Header
        $this->SetFillColor(200,220,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetFont('Arial','B',10);
        
        // Perubahan 1: Mengatur border header menjadi 1 (semua sisi)
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true); // <-- Diubah dari 1 menjadi 1
        $this->Ln();

        // Data
        $this->SetFont('Arial','',10);
        $this->SetFillColor(255,255,255);
        $fill = false;
        $no = 1;

        foreach($data as $row)
        {
            // Perubahan 2: Mengatur border data menjadi 1 (semua sisi), bukan 'LR'
            $this->Cell($w[0],6,$no++,1,0,'C',$fill);
            $this->Cell($w[1],6,htmlspecialchars($row['namaproduk']),1,0,'L',$fill);
            $this->Cell($w[2],6,$row['stock_display'],1,0,'C',$fill);
            $this->Ln();
            $fill = !$fill;
        }

        // Garis penutup (dihapus karena setiap baris sudah punya border)
        // $this->Cell(array_sum($w),0,'','T'); 
    }
}

// ----------------------------------------------------
// 1. PENGAMBILAN DATA
// ----------------------------------------------------

$batas_stok_minimum = 10;
$data_report = [];

// Ambil data stok yang hampir habis (Query sama dengan di index_owner.php)
$sql_stok_habis = "SELECT namaproduk, stock, isi_pcs_per_ctn FROM produk WHERE stock <= ? ORDER BY stock ASC";
$stmt_stok_habis = $c->prepare($sql_stok_habis);

if (!$stmt_stok_habis) {
    die("Query error (stok habis): " . $c->error);
}
$stmt_stok_habis->bind_param("i", $batas_stok_minimum);
$stmt_stok_habis->execute();
$result_stok_habis = $stmt_stok_habis->get_result();

while ($row_stok = $result_stok_habis->fetch_assoc()) {
    $stock_in_pcs = $row_stok['stock'];
    $isi_pcs_per_ctn = $row_stok['isi_pcs_per_ctn'];
    
    // Logika display stock (Pcs tersisa)
    $remaining_pcs = $stock_in_pcs;

    if ($isi_pcs_per_ctn > 0) {
        $remaining_pcs = $stock_in_pcs % $isi_pcs_per_ctn;
    }

    $data_report[] = [
        'namaproduk' => $row_stok['namaproduk'],
        'stock_display' => $remaining_pcs . ' Pcs'
    ];
}

$stmt_stok_habis->close();

// ----------------------------------------------------
// 2. GENERASI PDF
// ----------------------------------------------------

// Inisialisasi PDF
$pdf = new PDF('P','mm','A4'); // A4, Portrait
$pdf->AliasNbPages(); // Untuk menghitung total halaman
$pdf->AddPage();

// Header Tabel
$header = array('No.', 'Nama Produk', 'Stock Tersisa');

// Buat Tabel
$pdf->BuatTabel($header, $data_report);

// Output PDF
$pdf->Output('I', "Laporan_Stok_Habis_" . date('Ymd_His') . ".pdf");

$c->close(); // Tutup koneksi database
?>