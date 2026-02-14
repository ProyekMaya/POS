<?php
    require '../ceklogin.php';
    $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
    $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
    $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
    $queryString = "bulan=$bulan&tahun=$tahun";

$displayName = "Guest";
$roleName = "";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin' && isset($_SESSION['username_admin'])) {
        $roleName = "Admin";
        $displayName = $_SESSION['username_admin'];
    } elseif ($_SESSION['role'] == 'kasir' && isset($_SESSION['namakasir_kasir'])) {
        $roleName = "Kasir";
        $displayName = $_SESSION['namakasir_kasir'];
    }
}
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav" >
                <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">

                            <!-- logo -->
                            <div class="sb-sidenav-menu-heading" style="margin-bottom: 4px; ">
                                <img src="../img/logo.png" alt="Logo" style="width: 160px;"></i>
                                </img>
                            </div>

                            <div class="sb-sidenav-menu-heading pt- text-center" style="padding-top: 0; margin-top: 0;">
                                <!-- <i class="fas fa-user-circle fa-2x mb-2" style="vertical-align: middle;"></i><br> -->
                                <strong style="font-size: 1.1em;"><?php echo $roleName . ": " . $displayName; ?></strong>
                            </div>

                            <?php
                            $current_page = basename($_SERVER['PHP_SELF']);
                            ?>

                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'index_admin.php') ? 'active' : '' ?>" href="index_admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-grip-horizontal"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link w-100 text-start ps-4<?= ($current_page == 'stock_admin.php') ? 'active' : '' ?>" href="stock_admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-archive"></i></div>
                                Stok Barang
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'manajemen_user.php') ? 'active' : '' ?>" href="manajemen_user.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                                Manajemen User
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'laporan_admin.php') ? 'active' : '' ?>" href="laporan_admin.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                                Laporan
                            </a>
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
                <div class="container">
                    <h1 class="mt-4 mb-4 text-center">Laporan</h1>

                    <div class="row">
                        <div class="col-md-4"> 
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Penjualan Harian</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/penjualan_harian.php" class="row g-3">
                                        <div class="col-md-12">
                                            <label for="tanggal" class="form-label">Tanggal:</label>
                                            <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Penjualan Bulanan</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/penjualan_bulanan.php" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="bulan" class="form-label">Bulan:</label>
                                            <select name="bulan" id="bulan" class="form-select">
                                                <?php 
                                                    $currentMonth = date('m');
                                                    for ($i = 1; $i <= 12; $i++): 
                                                        $val = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                        $label = date('F', mktime(0, 0, 0, $i, 10));
                                                        $selected = ($val === $currentMonth) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tahun" class="form-label">Tahun:</label>
                                            <select name="tahun" id="tahun" class="form-select">
                                                <?php
                                                $currentYear = date('Y');
                                                for ($i = $currentYear; $i >= $currentYear - 5; $i--): ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Penjualan Tahunan</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/penjualan_tahunan.php" class="row g-3">
                                        <div class="col-md-12">
                                            <label for="tahun" class="form-label">Tahun:</label>
                                            <select name="tahun" id="tahun" class="form-select">
                                                <?php
                                                $currentYear = date('Y');
                                                for ($i = $currentYear; $i >= $currentYear - 5; $i--): ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Keuntungan Harian</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/keuntungan_harian.php" class="row g-3">
                                        <div class="col-md-12">
                                            <label for="tanggal" class="form-label">Tanggal:</label>
                                            <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Keuntungan Bulanan</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/keuntungan_bulanan.php" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="bulan" class="form-label">Bulan:</label>
                                            <select name="bulan" id="bulan" class="form-select">
                                                <php 
                                                    $currentMonth = date('m');
                                                    for ($i = 1; $i <= 12; $i++): 
                                                        $val = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                        $label = date('F', mktime(0, 0, 0, $i, 10));
                                                        $selected = ($val === $currentMonth) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                                                <php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tahun" class="form-label">Tahun:</label>
                                            <select name="tahun" id="tahun" class="form-select">
                                                <php
                                                $currentYear = date('Y');
                                                for ($i = $currentYear; $i >= $currentYear - 5; $i--): ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> -->
                       
                    <div class="row">    
                        <div class="col-md-4"> 
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Produk Terlaris Harian</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/produk_terlaris_harian.php" class="row g-3">
                                        <div class="col-md-12">
                                            <label for="tanggal" class="form-label">Tanggal:</label>
                                            <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                     

                    <!-- <div class="row justify-content-center">  -->

                        <div class="col-md-4"> 
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Produk Terlaris Bulanan</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/produk_terlaris_bulanan.php" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="bulan" class="form-label">Bulan:</label>
                                            <select name="bulan" id="bulan" class="form-select">
                                                <?php 
                                                    $currentMonth = date('m');
                                                    for ($i = 1; $i <= 12; $i++): 
                                                        $val = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                        $label = date('F', mktime(0, 0, 0, $i, 10));
                                                        $selected = ($val === $currentMonth) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tahun" class="form-label">Tahun:</label>
                                            <select name="tahun" id="tahun" class="form-select">
                                                <?php
                                                $currentYear = date('Y');
                                                for ($i = $currentYear; $i >= $currentYear - 5; $i--): ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4"> 
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5>Laporan Produk Terlaris Tahunan</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="ekspor_laporan/produk_terlaris_tahunan.php" class="row g-3">
                                        <div class="col-md-12">
                                            <label for="tahun" class="form-label">Tahun:</label>
                                            <select name="tahun" id="tahun" class="form-select">
                                                <?php
                                                $currentYear = date('Y');
                                                for ($i = $currentYear - 10; $i <= $currentYear + 5; $i++): ?>
                                                    <option value="<?= $i ?>" <?= ($i == $currentYear) ? 'selected' : '' ?>><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" class="btn btn-success">Lihat Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
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

        <script src="../js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
