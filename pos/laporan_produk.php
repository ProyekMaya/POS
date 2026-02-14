<?php
require 'ceklogin.php';
require 'function.php'; // koneksi $c di sini

if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin' && isset($_SESSION['username_admin'])) {
            $roleName = "Admin";
            $displayName = $_SESSION['username_admin'];
        } elseif ($_SESSION['role'] == 'kasir' && isset($_SESSION['username_kasir'])) {
            $roleName = "Kasir";
            $displayName = $_SESSION['username_kasir'];
        }
    }

// --- Filter laporan ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// --- Query untuk Laporan Ringkasan Produk Terjual (Menggunakan GROUP BY) ---
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

// Jalankan query dan cek error
$get = mysqli_query($c, $query);
if (!$get) {
    die("Query gagal: " . mysqli_error($c) . "<br>SQL: " . $query);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Kasir - Toko Mamah Azis</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">

                            <div class="sb-sidenav-menu-heading" style="margin-bottom: 4px; ">
                                <img src="img/logo.png" alt="Logo" style="width: 160px;">
                            </div>

                            <div class="sb-sidenav-menu-heading text-center" style="padding-top: 0; margin-top: 0;">
                                <strong style="font-size: 1.2em;"><?php echo $roleName . ": " . $displayName; ?></strong>
                            </div>

                            <?php
                            $current_page = basename($_SERVER['PHP_SELF']);
                            
                            // Tentukan halaman mana saja yang termasuk dalam grup 'Laporan'
                            $report_pages = ['laporan.php', 'laporan_produk.php'];
                            $is_report_active = in_array($current_page, $report_pages);

                            // Tentukan apakah menu dropdown Laporan harus terbuka (collapse 'show')
                            $collapse_class = $is_report_active ? 'show' : '';
                            ?>

                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-grip-horizontal"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'penjualan.php') ? 'active' : '' ?>" href="penjualan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-shopping-basket"></i></div>
                                Penjualan
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'stock.php') ? 'active' : '' ?>" href="stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-archive"></i></div>
                                Stok Barang
                            </a>

                            <a class="nav-link collapsed w-100 text-start ps-4 <?= $is_report_active ? 'active' : '' ?>" 
                                href="#" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapseLaporan" 
                                aria-expanded="<?= $is_report_active ? 'true' : 'false' ?>" 
                                aria-controls="collapseLaporan">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                                Laporan
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            
                            <div class="collapse <?= $collapse_class ?>" id="collapseLaporan" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'laporan.php') ? 'active' : '' ?>" href="laporan.php">
                                        Laporan Penjualan
                                    </a>
                                    <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'laporan_produk.php') ? 'active' : '' ?>" href="laporan_produk.php">
                                        Laporan Produk Terjual
                                    </a>
                                </nav>
                            </div>

                            <a class="nav-link w-100 text-start ps-4 text-danger" href="logout.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt text-danger"></i></div>
                                Logout
                            </a>

                        </div>
                    </div>
                </nav>
            </div>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container mt-4 mb-5">
                        <h2 class="text-center mb-4">Laporan Produk Terjual</h2>

                        <form method="GET" class="row g-3 mb-4 justify-content-center">
                            <div class="col-md-3">
                                <select name="filter" id="filter" class="form-select" onchange="tampilkanInput()">
                                    <option value="semua" <?= $filter == 'semua' ? 'selected' : '' ?>>Semua Data</option>
                                    <option value="harian" <?= $filter == 'harian' ? 'selected' : '' ?>>Harian</option>
                                    <option value="bulanan" <?= $filter == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                                    <option value="tahunan" <?= $filter == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                                </select>
                            </div>

                            <div class="col-md-3" id="inputHarian" style="display:none;">
                                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
                            </div>

                            <div class="col-md-2" id="inputBulan" style="display:none;">
                                <select name="bulan" class="form-select">
                                    <?php
                                    $bulanArr = [
                                        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                                        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                                        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
                                    ];
                                    foreach ($bulanArr as $key => $namaBulan) {
                                        $sel = ($bulan == $key) ? ' selected' : '';
                                        echo "<option value='$key'$sel>$namaBulan</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-2" id="inputTahun" style="display:none;">
                                <select name="tahun" class="form-select">
                                    <?php
                                    for ($i = date('Y'); $i >= 2020; $i--) {
                                        $sel = ($tahun == $i) ? ' selected' : '';
                                        echo "<option value='$i'$sel>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-2 text-center">
                                <button type="submit" class="btn btn-primary w-100">Lihat Laporan</button>
                            </div>
                            <div class="col-md-2 text-center">
                                <a href="export_produk_pdf.php?filter=<?= htmlspecialchars($filter) ?>&tanggal=<?= htmlspecialchars($tanggal) ?>&bulan=<?= htmlspecialchars($bulan) ?>&tahun=<?= htmlspecialchars($tahun) ?>" class="btn btn-danger w-100" target="_blank">
                                    <i class="fa-solid fa-file-pdf"></i> Ekspor PDF
                                </a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Harga Jual Satuan</th>
                                        <th>Harga Modal Satuan</th>
                                        <th>Total Pcs Terjual</th>
                                        <th>Total Pendapatan</th>
                                        <th>Total Keuntungan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $grand_total_qty = 0;
                                    $grand_total_pendapatan = 0;
                                    $grand_total_keuntungan = 0;

                                    if (mysqli_num_rows($get) > 0) {
                                        while ($data = mysqli_fetch_assoc($get)) {
                                            $harga_jual = isset($data['harga_jual_satuan']) ? (float)$data['harga_jual_satuan'] : 0;
                                            $harga_modal = isset($data['harga_modal_satuan']) ? (float)$data['harga_modal_satuan'] : 0;
                                            $total_jumlah_terjual = isset($data['total_jumlah_terjual']) ? (int)$data['total_jumlah_terjual'] : 0;
                                            $total_pendapatan = isset($data['total_pendapatan']) ? (float)$data['total_pendapatan'] : 0;
                                            $total_keuntungan = isset($data['total_keuntungan']) ? (float)$data['total_keuntungan'] : 0;

                                            $grand_total_qty += $total_jumlah_terjual;
                                            $grand_total_pendapatan += $total_pendapatan;
                                            $grand_total_keuntungan += $total_keuntungan;

                                            echo "<tr>
                                                <td>{$no}</td>
                                                <td>" . htmlspecialchars($data['nama_produk']) . "</td>
                                                <td class='text-end'>Rp " . number_format($harga_jual, 0, ',', '.') . "</td>
                                                <td class='text-end'>Rp " . number_format($harga_modal, 0, ',', '.') . "</td>
                                                <td class='text-center'>{$total_jumlah_terjual} pcs</td>
                                                <td class='text-end'>Rp " . number_format($total_pendapatan, 0, ',', '.') . "</td>
                                                <td class='text-end'>Rp " . number_format($total_keuntungan, 0, ',', '.') . "</td>
                                            </tr>";
                                            $no++;
                                        }

                                        // Baris total keseluruhan
                                        echo "<tr class='table-secondary fw-bold'>
                                            <td colspan='4' class='text-end'>Total Keseluruhan</td>
                                            <td class='text-center'>{$grand_total_qty} pcs</td>
                                            <td class='text-end'>Rp " . number_format($grand_total_pendapatan, 0, ',', '.') . "</td>
                                            <td class='text-end'>Rp " . number_format($grand_total_keuntungan, 0, ',', '.') . "</td>
                                        </tr>";
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>Tidak ada data produk terjual</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>  
                </main>
            </div>

<script>
function tampilkanInput() {
    const filter = document.getElementById('filter').value;

    const inputHarian = document.getElementById('inputHarian');
    const inputBulan = document.getElementById('inputBulan');
    const inputTahun = document.getElementById('inputTahun');

    inputHarian.style.display = 'none';
    inputBulan.style.display = 'none';
    inputTahun.style.display = 'none';

    if (filter === 'harian') {
        inputHarian.style.display = 'block';
    } else if (filter === 'bulanan') {
        inputBulan.style.display = 'block';
        inputTahun.style.display = 'block';
    } else if (filter === 'tahunan') {
        inputTahun.style.display = 'block';
    }
}
window.onload = tampilkanInput;
</script>

</body>
</html>