<?php
    require 'ceklogin.php';

    $h1 = mysqli_query($c,"SELECT * FROM pelanggan");
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

                            <!-- Heading Logo -->
                            <div class="sb-sidenav-menu-heading mb-2"><img src="img/logo.png" alt="Logo" style="width: 180px;"></i></div>

                            <?php
                            $current_page = basename($_SERVER['PHP_SELF']);
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
                            <!-- <a class="nav-link w-100 text-start ps-4 <= ($current_page == 'masuk.php') ? 'active' : '' ?>" href="masuk.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                                Barang Masuk
                            </a> -->
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'pelanggan.php') ? 'active' : '' ?>" href="pelanggan.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                                Kelola Pelanggan
                            </a>
                            <!-- <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'kasir.php') ? 'active' : '' ?>" href="kasir.php">
                                 <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                                Kelola Kasir
                            </a> -->
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'laporan.php') ? 'active' : '' ?>" href="laporan.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                                Laporan
                            </a>
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
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Data Pelanggan</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active"></li>
                        </ol>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">Jumlah Pelanggan: <?=$h2;?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Button To Open The Modal -->
                        <button type="button" class="btn btn-info mb-4" data-bs-toggle="modal" data-bs-target="#myModal">
                            Tambah Pelanggan
                        </button>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Pelanggan
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pelanggan</th>
                                            <th>No Telepon</th>
                                            <th>Alamat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $get = mysqli_query($c, "SELECT * FROM pelanggan ORDER BY idpelanggan DESC");

                                            $i = 1;

                                            while ($p = mysqli_fetch_array($get)) {
                                                $namapelanggan = $p['namapelanggan'];
                                                $notelp = $p['notelp'];
                                                $alamat = $p['alamat'];
                                                $idpl = $p['idpelanggan'];
                                            ?>
                                                <tr>
                                                    <td><?= $i++; ?></td>
                                                    <td><?= $namapelanggan; ?></td>
                                                    <td><?= $notelp; ?></td>
                                                    <td><?= $alamat; ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#edit<?=$idpl;?>">
                                                        Edit
                                                        </button>
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?=$idpl;?>">
                                                        Delete
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- The Modal Edit -->
                                                <div class="modal fade" id="edit<?=$idpl;?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Ubah <?= $namapelanggan; ?></h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            <input type="text" name="namapelanggan" class="form-control" placeholder="Nama Pelanggan" value="<?= $namapelanggan; ?>">
                                                            <input type="text" name="notelp" class="form-control mt-2" placeholder="No Telepon" value="<?= $notelp; ?>">
                                                            <input type="text" name="alamat" class="form-control mt-2" placeholder="Alamat" value="<?= $alamat; ?>">
                                                            <input type="hidden" name="idpl" value="<?=$idpl;?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="editpelanggan">Submit</button>
                                                        </div>

                                                        </form>

                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- The Modal Delete -->
                                                <div class="modal fade" id="delete<?=$idpl;?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Hapus <?= $namapelanggan; ?></h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            Apakah anda yakin ingin menghapus pelanggan ini?
                                                            <input type="hidden" name="idpl" value="<?=$idpl;?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="hapuspelanggan">Ya</button>
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
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>

    <!-- The Modal -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Data Pelanggan</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="post">

            <!-- Modal body -->
            <div class="modal-body">
                <input type="text" name="namapelanggan" class="form-control" placeholder="Nama Pelanggan">
                <input type="text" name="notelp" class="form-control mt-2" placeholder="No Telepon">
                <input type="text" name="alamat" class="form-control mt-2" placeholder="Alamat">
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" name="tambahpelanggan">Submit</button>
            </div>

            </form>

            </div>
        </div>
    </div>

</html>
