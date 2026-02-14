<?php
// Pastikan file koneksi database Anda terhubung.
// Jika ceklogin.php sudah memuat koneksi $c, Anda bisa menggunakannya.
// Jika tidak, pastikan koneksi ke database diinisialisasi di sini.
require 'ceklogin.php'; // Ini seharusnya sudah memiliki $c (koneksi mysqli)

// Set header untuk memberitahu browser bahwa respons adalah JSON
header('Content-Type: application/json');

$products = []; // Inisialisasi array untuk menampung hasil produk

// Ambil ID penjualan dari parameter URL
$idpenjualan_dari_url = isset($_GET['idpenjualan']) ? $_GET['idpenjualan'] : '';
// Ambil keyword pencarian (opsional, jika Anda ingin menambahkan filter pencarian berdasarkan nama produk)
$search_keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

if (!empty($idpenjualan_dari_url)) {
    // Bangun query SQL dasar
    $sql = "SELECT idproduk, namaproduk, isi_pcs_per_ctn, stock FROM produk WHERE idproduk NOT IN (SELECT idproduk FROM detailpenjualan WHERE idpenjualan=?)";
    $params_type = "s"; // Tipe parameter untuk idpenjualan (string)
    $params = [$idpenjualan_dari_url];

    // Jika ada keyword pencarian, tambahkan kondisi LIKE
    if (!empty($search_keyword)) {
        $sql .= " AND namaproduk LIKE ?";
        $params_type .= "s"; // Tambahkan tipe parameter untuk keyword (string)
        $params[] = '%' . $search_keyword . '%';
    }

    $sql .= " LIMIT 10"; // Batasi hasil untuk performa

    // Siapkan prepared statement
    $stmt = $c->prepare($sql);

    if ($stmt) {
        // Bind parameter secara dinamis
        mysqli_stmt_bind_param($stmt, $params_type, ...$params);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = [
                    'idproduk' => $row['idproduk'],
                    'namaproduk' => $row['namaproduk'],
                    'isi_pcs_per_ctn' => $row['isi_pcs_per_ctn'] ?? 0, // Default ke 0 jika null
                    'stock' => $row['stock'] ?? 0 // Default ke 0 jika null
                ];
            }
        }
        $stmt->close();
    } else {
        // Log error jika prepared statement gagal
        error_log("Gagal menyiapkan statement: " . $c->error);
        echo json_encode(['error' => 'Gagal mengambil data produk.']);
        exit(); // Penting untuk menghentikan eksekusi
    }
} else {
    // Jika idpenjualan tidak disediakan, Anda bisa mengembalikan array kosong
    // atau mengembalikan semua produk tanpa filter jika itu adalah perilaku yang diinginkan
    // Untuk kasus ini, saya akan mengembalikan array kosong atau error agar lebih aman.
    echo json_encode(['error' => 'ID Penjualan tidak ditemukan di URL.']);
    exit();
}

// Kirimkan data produk sebagai JSON
echo json_encode($products);

// Tidak perlu menutup koneksi $c di sini jika $c berasal dari ceklogin.php
// dan akan digunakan oleh skrip utama (view.php). Biarkan ceklogin.php/skrip utama yang menanganinya.
// $c->close(); // Hapus baris ini jika $c dikelola oleh ceklogin.php atau script utama
exit(); // Pastikan tidak ada output lain setelah JSON
?>