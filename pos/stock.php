<?php
    require 'ceklogin.php';

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('location:index_admin.php');
        exit();
    }
    
    $displayName = "Guest";
    $roleName = "";
    
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin' && isset($_SESSION['username_admin'])) {
            $roleName = "Admin";
            $displayName = $_SESSION['username_admin'];
        } elseif ($_SESSION['role'] == 'kasir' && isset($_SESSION['username_kasir'])) {
            $roleName = "Kasir";
            $displayName = $_SESSION['username_kasir'];
        }
    }

    $h1 = mysqli_query($c,"SELECT * FROM produk");
    $h2 = mysqli_num_rows($h1);
?>

<!DOCTYPE html>
<html lang="en">
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
                        
                            <!-- logo -->
                            <div class="sb-sidenav-menu-heading" style="margin-bottom: 4px; ">
                                <img src="img/logo.png" alt="Logo" style="width: 160px;"></i>
                                </img>
                            </div>

                            <div class="sb-sidenav-menu-heading pt- text-center" style="padding-top: 0; margin-top: 0;">
                                <!-- <i class="fas fa-user-circle fa-2x mb-2" style="vertical-align: middle;"></i><br> -->
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
                        <h1 class="mt-4">Data Barang</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active"></li>
                        </ol>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">Jumlah Barang: <?=$h2;?></div>
                                </div>
                            </div>
                        </div>
                          
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Barang
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama produk</th>
                                            <th>Stok </th>
                                            <th>Harga Jual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($c, $_GET['search']) : '';
                                        if ($search != '') {
                                            // Pastikan Anda memilih kolom isi_pcs_per_ctn dari tabel produk
                                            $get = mysqli_query($c, "SELECT * FROM produk WHERE namaproduk LIKE '%$search%' ORDER BY idproduk DESC");
                                        } else {
                                            // Pastikan Anda memilih kolom isi_pcs_per_ctn dari tabel produk
                                            $get = mysqli_query($c, "SELECT * FROM produk ORDER BY idproduk DESC");
                                        }
                                        
                                        $i = 1;
                                        while ($p = mysqli_fetch_array($get)) {
                                            $namaproduk = $p['namaproduk'];
                                            $hargajual = $p['hargajual'];
                                            $stock = $p['stock']; // Total dalam Pcs
                                            $idproduk = $p['idproduk'];
                                            $isi_pcs_per_ctn = $p['isi_pcs_per_ctn'];
                                        ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= $namaproduk; ?> (Isi per Ctn: <?= $isi_pcs_per_ctn ?>)</td>
                                                <td><?= $stock; ?> pcs</td>
                                                <td>Rp.<?= number_format($hargajual); ?></td>
                                            </tr>

                                        <?php
                                        } // end of while
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>
