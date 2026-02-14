<?php
    require 'function.php';
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
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'masuk.php') ? 'active' : '' ?>" href="masuk.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                                Barang Masuk
                            </a>
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
                        <h1 class="mt-4">Data Barang Masuk</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active"></li>
                        </ol>

                        <!-- Button To Open The Modal -->
                        <button type="button" class="btn btn-info mb-4" data-bs-toggle="modal" data-bs-target="#myModal">
                            Tambah Barang Masuk
                        </button>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Barang Masuk
                            </div>
                            <div class="card-body">

                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama produk</th>
                                            <th>Jumlah</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $search = isset($_GET['search']) ? $_GET['search'] : '';
                                            if ($search != '') {
                                                $get = mysqli_query($c, "SELECT * FROM masuk m, produk p WHERE m.idproduk=p.idproduk AND p.namaproduk LIKE '%$search%'");
                                            } else {
                                                $get = mysqli_query($c, "SELECT * FROM masuk m, produk p WHERE m.idproduk=p.idproduk");
                                            }
                                            
                                            $i = 1;

                                            while ($p = mysqli_fetch_array($get)) {
                                                $namaproduk = $p['namaproduk'];
                                                // $deskripsi = $p['deskripsi'];
                                                $jumlah = $p['jumlah'];
                                                $idmasuk = $p['idmasuk'];
                                                $idproduk = $p['idproduk'];
                                                $tanggal = $p['tanggalmasuk'];
                                            ?>
                                                <tr>
                                                    <td><?= $i++; ?></td>
                                                    <!-- <td><= $namaproduk; ?>: <= $deskripsi; ?></td> -->
                                                    <td><?= $namaproduk;  ?></td>
                                                    <td><?= $jumlah; ?></td>
                                                    <td><?= $tanggal; ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#edit<?=$idmasuk;?>">
                                                        Edit
                                                        </button>
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?=$idmasuk;?>">
                                                        Delete
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- The Modal Edit -->
                                                <div class="modal fade" id="edit<?=$idmasuk;?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Ubah Data Barang Masuk</h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            <!-- <input type="text" name="namaproduk" class="form-control" placeholder="Nama produk" value="<= $namaproduk; ?>: <= $deskripsi; ?>" disabled> -->
                                                            <input type="text" name="namaproduk" class="form-control" placeholder="Nama produk" value="<?= $namaproduk; ?> " disabled>
                                                            <input type="number" name="jumlah" class="form-control mt-2" placeholder="jumlah" value="<?= $jumlah; ?>">
                                                            <input type="hidden" name="idm" value="<?=$idmasuk;?>">
                                                            <input type="hidden" name="idp" value="<?=$idproduk;?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="editdatabarangmasuk">Submit</button>
                                                        </div>

                                                        </form>

                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- The Modal Delete -->
                                                <div class="modal fade" id="delete<?=$idmasuk;?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Hapus Data Barang Masuk</h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <form method="post">

                                                        <!-- Modal body -->
                                                        <div class="modal-body">
                                                            Apakah anda yakin ingin menghapus barang ini?
                                                            <input type="hidden" name="idp" value="<?=$idproduk;?>">
                                                            <input type="hidden" name="idm" value="<?=$idmasuk;?>">
                                                        </div>

                                                        <!-- Modal footer -->
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="hapusdatabarangmasuk">Ya</button>
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
                <h4 class="modal-title">Tambah Barang</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="post">

            <!-- Modal body -->
            <div class="modal-body">
            Pilih Barang Masuk
            <select name="idproduk" class="form-control">
                <?php
                $getproduk = mysqli_query($c, "SELECT * FROM produk");

                while($pl = mysqli_fetch_array($getproduk)) {
                    $namaproduk = $pl['namaproduk'];
                    $stock = $pl['stock'];
                    $deskripsi = $pl['deskripsi'];
                    $idproduk = $pl['idproduk'];
                ?>
                <option value="<?= $idproduk; ?>"><?= $namaproduk; ?> - <?= $deskripsi; ?> (Stock: <?=$stock;?>)</option>
                <?php
                }
                ?>
            </select>
            <input type="number" name="jumlah" class="form-control mt-4" placeholder="Jumlah" min="1" required>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" name="barangmasuk">Submit</button>
            </div>

            </form>

            </div>
        </div>
    </div>

</html>
