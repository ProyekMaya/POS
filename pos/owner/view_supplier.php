<?php
    require '../ceklogin.php'; // Include login check

    // Query to get the total number of purchases
    $h1 = mysqli_query($c,"SELECT * FROM supplier");
    $h2 = mysqli_num_rows($h1);

    // --- START: Page Access Logic ---
    // Only allow 'owner' role to access this page.
    // If the role is not 'owner', redirect to the appropriate page.
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== 'True' || $_SESSION['role'] !== 'owner') {
        // If not logged in or not an Owner
        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] === 'kasir') {
                header('location:../index.php'); // Redirect cashier to cashier dashboard
            } elseif ($_SESSION['role'] === 'admin') {
                header('location:../admin/index_admin.php'); // Redirect admin to admin dashboard
            } else {
                // Unknown or inappropriate role, redirect to login
                header('location:../login.php');
            }
        } else {
            // No role in session, redirect to login
            header('location:../login.php');
        }
        exit(); // Important: Stop execution after header redirect
    }
    // --- END: Page Access Logic ---

    $displayName = "Guest";
    $roleName = "";

    // Get display name and role name for Owner
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner' && isset($_SESSION['username_owner'])) {
        $roleName = "Owner"; // Correct role name
        $displayName = $_SESSION['username_owner']; // Get from Owner session
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
        <title>Pembelian - Toko Mamah Azis</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
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

                            <!-- Logo -->
                            <div class="sb-sidenav-menu-heading" style="margin-bottom: 4px; ">
                                <img src="../img/logo.png" alt="Logo" style="width: 160px;">
                            </div>

                            <!-- Owner: [Display Name] -->
                            <div class="sb-sidenav-menu-heading text-center" style="padding-top: 0; margin-top: 0;">
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
                        <h1 class="mt-4">Data Supplier</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active"></li>
                        </ol>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4"> <!-- Changed color for purchase -->
                                    <div class="card-body">Jumlah Supplier: <?=$h2;?></div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-info mb-4" data-bs-toggle="modal" data-bs-target="#myModal">
                            Tambah Supplier
                        </button>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Supplier
                                
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>ID Supplier</th>
                                            <th>Tanggal</th>
                                            <th>Nama Supplier</th>
                                            <th>No. Telepon</th>
                                            <th>Alamat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($c, $_GET['search']) : '';

                                        if ($search != '') {
                                            $get = mysqli_query($c, "
                                                SELECT * FROM supplier p
                                                WHERE namasupplier LIKE '%$search%' OR p.alamatsupplier LIKE '%$search%'
                                                ORDER BY tanggal DESC
                                            ");

                                        } else {
                                            $get = mysqli_query($c, "
                                                SELECT * FROM supplier p
                                                ORDER BY p.tanggal DESC
                                            ");

                                        }

                                        while ($p = mysqli_fetch_array($get)) {
                                            $idpembelian = $p['idsupplier'];
                                            $tanggal = $p['tanggal'];
                                            $namasupplier = $p['namasupplier'];
                                            $notelp = $s['notelp'];
                                            $alamatsupplier = $p['alamatsupplier'];
                                        ?>
                                            <tr>
                                                <td><?= $idsupplier; ?></td>
                                                <td><?= $tanggal; ?></td>
                                                <td><?= $namasupplier; ?></td>
                                                <td><?= $notelp; ?></td>
                                                <td><?= $alamat; ?></td>
                                                <td>
                                                    <a href="view_supplier.php?idp=<?=$idpembelian;?>" class="btn btn-info me-2">
                                                        Tampilkan
                                                    </a>
                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?=$idpembelian;?>">
                                                        Hapus
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- The Modal Delete -->
                                            <div class="modal fade" id="delete<?=$idpembelian;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Hapus Data Pembelian</h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            Apakah anda yakin ingin menghapus Supplier ini?
                                                            <input type="hidden" name="idpembelian_del" value="<?=$idpembelian;?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="hapuspembelian">Ya</button>
                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                                                        </div>

                                                        </form>

                                                    </div>
                                                </div>
                                            </div>

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

        <div id="faktur-container" style="display:none;"></div>

        <script src="../js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>

    </body>

    <!-- The Modal Add New Purchase -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Pembelian Baru</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="post">

            <!-- Modal body -->
            <div class="modal-body">
                <div class="mb-3">
                    <label for="namasupplier" class="form-label">Nama Supplier:</label>
                    <input type="text" class="form-control" id="namasupplier" name="namasupplier" required>
                </div>
                <div class="mb-3">
                    <label for="alamatsupplier" class="form-label">Alamat Supplier:</label>
                    <textarea class="form-control" id="alamatsupplier" name="alamatsupplier" rows="3" required></textarea>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" name="tambahpembelian">Submit</button>
            </div>

            </form>

            </div>
        </div>
    </div>

</html>
