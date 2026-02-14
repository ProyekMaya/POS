<?php
require '../ceklogin.php';

// --- START: Logika Akses Halaman ---
// Hanya izinkan Owner untuk mengakses halaman ini.
// Jika role bukan 'owner', redirect ke halaman yang sesuai.
if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'True' || $_SESSION['role'] !== 'owner') {
    // Jika belum login atau bukan Owner
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'kasir') {
            header('location:../index.php'); // Arahkan kasir ke dashboard kasir
        } elseif ($_SESSION['role'] === 'admin') {
            header('location:../admin/index_admin.php'); // Arahkan admin ke dashboard admin
        } else {
            // Role tidak dikenal atau tidak sesuai, arahkan ke login
            header('location:../login.php');
        }
    } else {
        // Belum ada role di session, arahkan ke login
        header('location:../login.php');
    }
    exit(); // Penting: Hentikan eksekusi setelah header
}
// --- END: Logika Akses Halaman ---

$displayName = "Guest";
$roleName = "";

// Mengambil display name dan role name untuk Owner
if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner' && isset($_SESSION['username_owner'])) {
    $roleName = "Owner"; // Perbaiki nama peran
    $displayName = $_SESSION['username_owner']; // Ambil dari sesi Owner
}
// Tidak perlu memeriksa role 'admin' atau 'kasir' di halaman ini, karena sudah di-redirect di atas.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Owner - Toko Mamah Azis </title> <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body>
<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">

                    <div class="sb-sidenav-menu-heading" style="margin-bottom: 4px; ">
                        <img src="../img/logo.png" alt="Logo" style="width: 160px;"></i>
                        </img>
                    </div>

                    <div class="sb-sidenav-menu-heading pt- text-center" style="padding-top: 0; margin-top: 0;">
                        <strong style="font-size: 1.2em;"><?php echo $roleName ?></strong>
                    </div>

                        <?php
                            $current_page = basename($_SERVER['PHP_SELF']);
                            
                            // Tentukan halaman mana saja yang termasuk dalam grup 'Laporan'
                            $report_pages = ['laporan_owner.php', 'laporan_produk_owner.php'];
                            $is_report_active = in_array($current_page, $report_pages);

                            // Tentukan apakah menu dropdown Laporan harus terbuka (collapse 'show')
                            $collapse_class = $is_report_active ? 'show' : '';
                        ?>

                        <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'index_owner.php') ? 'active' : '' ?>" href="index_owner.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-grip-horizontal"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'penjualan_owner.php') ? 'active' : '' ?>" href="penjualan_owner.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-basket"></i></div>
                            Penjualan
                        </a>
                        <a class="nav-link w-100 text-start ps-4<?= ($current_page == 'stock_owner.php') ? 'active' : '' ?>" href="stock_owner.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-archive"></i></div>
                            Stok Barang
                        </a>
                        <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'pembelian_owner.php') ? 'active' : '' ?>" href="pembelian_owner.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-truck-loading"></i></div>
                            Pembelian
                        </a>
                        <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'supplier_owner.php') ? 'active' : '' ?>" href="supplier_owner.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-truck-loading"></i></div>
                            Supplier
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
                                    <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'laporan_owner.php') ? 'active' : '' ?>" href="laporan_owner.php">
                                        Laporan Penjualan
                                    </a>
                                    <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'laporan_produk_owner.php') ? 'active' : '' ?>" href="laporan_produk_owner.php">
                                        Laporan Produk Terjual
                                    </a>
                                </nav>
                            </div>
                        <a class="nav-link w-100 text-start ps-4 text-danger" href="../logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt text-danger"></i></div>
                            Logout
                        </a>
                </div>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4 mb-5">Dashboard Owner</h1> <?php

                $tanggalHariIni = date('Y-m-d');

                // Total Penjualan Hari Ini
                $sql = "
                    SELECT SUM(dp.jumlah * p.hargajual) AS total_hari_ini
                    FROM penjualan ps
                    JOIN detailpenjualan dp ON ps.idpenjualan = dp.idpenjualan
                    JOIN produk p ON dp.idproduk = p.idproduk
                    WHERE DATE(ps.tanggal) = ?
                ";
                $stmt = $c->prepare($sql);
                if (!$stmt) die("Query error (penjualan): " . $c->error);
                $stmt->bind_param("s", $tanggalHariIni);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $totalHariIni = $row['total_hari_ini'] ?? 0;
                $h1 = "Rp " . number_format($totalHariIni, 0, ',', '.');
                $stmt->close();
                ?>

                <?php
                // Total Transaksi Hari Ini
                $sql = "SELECT COUNT(*) AS total_transaksi FROM penjualan WHERE DATE(tanggal) = ?";
                $stmt = $c->prepare($sql);
                if (!$stmt) die("Query error (transaksi): " . $c->error);
                $stmt->bind_param("s", $tanggalHariIni);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $h2 = $row['total_transaksi'] ?? 0;
                $stmt->close();
                ?>

                <?php
                // Total Keuntungan Hari Ini (menggunakan prepared statement)
                $sql = "
                    SELECT
                        p.hargajual,
                        p.hargamodal,
                        SUM(dp.jumlah) AS total_terjual
                    FROM detailpenjualan dp
                    JOIN produk p ON dp.idproduk = p.idproduk
                    JOIN penjualan ps ON dp.idpenjualan = ps.idpenjualan
                    WHERE DATE(ps.tanggal) = ?
                    GROUP BY dp.idproduk
                ";
                $stmt = $c->prepare($sql);
                if (!$stmt) die("Query error (keuntungan hari ini): " . $c->error);
                $stmt->bind_param("s", $tanggalHariIni);
                $stmt->execute();
                $result = $stmt->get_result();

                $totalKeuntungan = 0;
                while ($row = $result->fetch_assoc()) {
                    $keuntungan = ($row['hargajual'] - $row['hargamodal']) * $row['total_terjual'];
                    $totalKeuntungan += $keuntungan;
                }
                $h3 = "Rp " . number_format($totalKeuntungan, 0, ',', '.');
                $stmt->close();
                ?>

                <?php
                // Total Keuntungan Seluruhnya (menggunakan prepared statement)
                $sql = "
                    SELECT p.hargajual, p.hargamodal, SUM(dp.jumlah) AS total_terjual
                    FROM detailpenjualan dp
                    JOIN produk p ON dp.idproduk = p.idproduk
                    GROUP BY dp.idproduk
                ";
                $result = $c->query($sql); // Query ini tidak menggunakan WHERE, jadi tidak perlu prepared statement jika tidak ada parameter.
                if (!$result) die("Query error (keuntungan total): " . $c->error);
                $totalKeuntunganSeluruh = 0; // Ubah nama variabel agar tidak menimpa $totalKeuntungan
                while ($row = $result->fetch_assoc()) {
                    $keuntungan = ($row['hargajual'] - $row['hargamodal']) * $row['total_terjual'];
                    $totalKeuntunganSeluruh += $keuntungan;
                }
                $h4 = "Rp " . number_format($totalKeuntunganSeluruh, 0, ',', '.'); // Ubah nama variabel
                ?>

                <div class="d-flex flex-wrap gap-5">
                    <div class="card bg-primary text-white" style="min-width: 340px;">
                        <i class="fas fa-shopping-cart mt-4" style="font-size: 4rem;"></i>
                        <div class="card-body" style="font-size: 1.5rem;">Total Penjualan Hari Ini:  <strong><?= $h1; ?></strong></div>
                    </div>
                    <div class="card bg-success text-white" style="min-width: 300px;">
                        <i class="fas fa-chart-line mt-4" style="font-size: 4rem;"></i>
                        <div class="card-body" style="font-size: 1.5rem;">Total Transaksi Hari Ini: <strong><?= $h2; ?></strong></div>
                    </div>
                    <div class="card bg-warning text-white" style="min-width: 340px;">
                        <i class="fas fa-coins mt-4" style="font-size: 4rem;"></i>
                        <div class="card-body" style="font-size: 1.5rem;">Total Keuntungan Hari Ini: <strong><?= $h3; ?></strong></div>
                    </div>
                </div>
                <br> <br>
                <?php
                $batas_stok_minimum = 10;
                $sql_stok_habis = "SELECT namaproduk, stock, isi_pcs_per_ctn FROM produk WHERE stock <= ? ORDER BY stock ASC";
                $stmt_stok_habis = $c->prepare($sql_stok_habis);

                if (!$stmt_stok_habis) {
                    die("Query error (stok habis): " . $c->error);
                }
                $stmt_stok_habis->bind_param("i", $batas_stok_minimum);
                $stmt_stok_habis->execute();
                $result_stok_habis = $stmt_stok_habis->get_result();

                if ($result_stok_habis->num_rows > 0) {
                ?>
                    <div class="card mb-4  text-white" style="max-width: 1100px;">
                        <div class="card-header bg-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Stok Barang Hampir Habis!
                            
                            <p>Perhatian! Beberapa produk memiliki stok di bawah atau sama dengan <strong><?= $batas_stok_minimum; ?></strong> unit. Segera lakukan restock untuk menghindari kehabisan barang.</p>
                            <a href="ekspor_stok_owner.php" target="_blank" class="btn btn-sm btn-light mt-2 mb-2">
                                <i class="fas fa-file-pdf me-1"></i> Ekspor ke PDF
                            </a>
                        </div>
                        <div class="card-body bg-light">
                            <div class="table-responsive">
                                <table id="datatablesSimple" class="text-dark">
                                    <thead>
                                        <tr>
                                            <th>Nama Produk</th>
                                            <th> Stock Tersisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row_stok = $result_stok_habis->fetch_assoc()) {
                                            $stock_in_pcs = $row_stok['stock'];
                                            $isi_pcs_per_ctn = $row_stok['isi_pcs_per_ctn'];

                                            $stock_in_cartons_dus = 0;
                                            $remaining_pcs = $stock_in_pcs;

                                            if ($isi_pcs_per_ctn > 0) {
                                                $stock_in_cartons_dus = floor($stock_in_pcs / $isi_pcs_per_ctn);
                                                $remaining_pcs = $stock_in_pcs % $isi_pcs_per_ctn;
                                            }

                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row_stok['namaproduk']) . "</td>";
                                            echo "<td>" . $remaining_pcs . " Pcs</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php
                }
                $stmt_stok_habis->close();
                ?>
            </div>
        </main>

        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; Maya 2025</div>
                </div>
            </div>
        </footer>
    </div>
</div>
<script src="../js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="../js/datatables-simple-demo.js"></script>
<?php $c->close();?>
</body>
</html>