<?php
include 'ceklogin.php'; // Ganti dengan file koneksi database Anda

// Pastikan koneksi ($c) sudah tersedia dari file koneksi.php
if (!$c) {
    die("Koneksi database gagal.");
}

$searchQuery = $_GET['q'] ?? ''; // Ambil query pencarian dari parameter URL 'q'

$data = [];

if (!empty($searchQuery)) {
    // Escape string untuk mencegah SQL Injection
    $searchQuery = mysqli_real_escape_string($c, $searchQuery);

    // Query untuk mencari produk berdasarkan nama produk atau deskripsi
    // Sesuaikan dengan kolom yang ingin Anda cari
    $getproduk = mysqli_query($c, "SELECT idproduk, namaproduk, stock FROM produk 
                                   WHERE namaproduk LIKE '%$searchQuery%' 
                                   OR deskripsi LIKE '%$searchQuery%'
                                   LIMIT 10"); // Batasi jumlah hasil

    if ($getproduk) {
        while ($pl = mysqli_fetch_array($getproduk)) {
            $data[] = [
                'id' => $pl['idproduk'],
                'name' => $pl['namaproduk'],
                'stock' => $pl['stock']
            ];
        }
    } else {
        // Handle error jika query gagal
        error_log("Error in search_produk.php: " . mysqli_error($c));
    }
}

// Set header untuk memberitahu bahwa respons adalah JSON
header('Content-Type: application/json');
// Kirim data dalam format JSON
echo json_encode($data);

mysqli_close($c); // Tutup koneksi database
?>