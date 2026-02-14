<?php
    require 'ceklogin.php';

    $h1 = mysqli_query($c,"SELECT * FROM produk");
    $h2 = mysqli_num_rows($h1);

    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin' && isset($_SESSION['username_admin'])) {
            $roleName = "Admin";
            $displayName = $_SESSION['username_admin'];
        } elseif ($_SESSION['role'] == 'kasir' && isset($_SESSION['username_kasir'])) {
            $roleName = "Kasir";
            $displayName = $_SESSION['username_kasir'];
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
                            
                            <!-- Logo -->
                            <div class="sb-sidenav-menu-heading" style="margin-bottom: 4px; ">
                                <img src="img/logo.png" alt="Logo" style="width: 160px;">
                            </div>

                            <!-- Kasir: Ibu Azis -->
                            <div class="sb-sidenav-menu-heading text-center" style="padding-top: 0; margin-top: 0;">
                                <strong style="font-size: 1.2em;"><?php echo $roleName . ": " . $displayName; ?></strong>
                            </div>

                            <?php
                            $current_page = basename($_SERVER['PHP_SELF']);
                            ?>

                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-shopping-basket"></i></div>
                                Penjualan
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'stock.php') ? 'active' : '' ?>" href="stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-archive"></i></div>
                                Stok Barang
                            </a>
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

                        <!-- Button To Open The Modal -->
                        <button type="button" class="btn btn-info mb-4" data-bs-toggle="modal" data-bs-target="#myModal">
                            Tambah Barang
                        </button>   
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
                                            <th>Nama Produk</th>
                                            <th>Stock (Pcs)</th>
                                            <th>Stock (Ctn)</th>
                                            <th>Total Keseluruhan Stock</th>
                                            <th>Harga Modal</th>
                                            <th>Harga Jual</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($c, $_GET['search']) : '';
                                        if ($search != '') {
                                            // Pastikan Anda memilih kolom pcs_per_dus_ctn dari tabel produk
                                            $get = mysqli_query($c, "SELECT * FROM produk WHERE namaproduk LIKE '%$search%' ORDER BY idproduk DESC");
                                        } else {
                                            // Pastikan Anda memilih kolom pcs_per_dus_ctn dari tabel produk
                                            $get = mysqli_query($c, "SELECT * FROM produk ORDER BY idproduk DESC");
                                        }
                                        
                                        

                                        $i = 1;
                                        while ($p = mysqli_fetch_array($get)) {
                                            $namaproduk = $p['namaproduk'];
                                            $hargamodal = $p['hargamodal'];
                                            $hargajual = $p['hargajual'];
                                            $stock = $p['stock']; // Total dalam Pcs
                                            $idproduk = $p['idproduk'];
                                            $pcs_per_dus_ctn = $p['pcs_per_dus_ctn'];

                                            // Hitung Ctn (Dus) dan sisa pcs
                                            if ($pcs_per_dus_ctn > 0) {
                                                $stock_dus_whole = floor($stock / $pcs_per_dus_ctn);
                                                $sisa_unit_stock = $stock % $pcs_per_dus_ctn;
                                                $stock_ctn_dus = "$stock_dus_whole Ctn";
                                            } else {
                                                $sisa_unit_stock = $stock;
                                                $stock_ctn_dus = "-";
                                            }

                                            $total_keseluruhan_stock_display = $stock . " Pcs";
                                        ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= $namaproduk; ?> (Isi per Ctn: <?= $pcs_per_dus_ctn ?>)</td>
                                                <td><?= $sisa_unit_stock; ?></td> <!-- hanya sisa pcs -->
                                                <td><?= $stock_ctn_dus; ?></td> <!-- hasil dus -->
                                                <td><?= $total_keseluruhan_stock_display; ?></td>
                                                <td>Rp.<?= number_format($hargamodal); ?></td>
                                                <td>Rp.<?= number_format($hargajual); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#edit<?= $idproduk; ?>">
                                                        Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?= $idproduk; ?>">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>

                                            
                                            <div class="modal fade" id="edit<?=$idproduk;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Ubah <?= $namaproduk; ?></h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                    <div class="modal-body">
                                                        <input type="text" name="namaproduk" class="form-control" placeholder="Nama produk" value="<?= $namaproduk; ?>">
                                                        <input type="number" name="stock" class="form-control mt-2" placeholder="Stok Produk (Pcs)" value="<?= $stock; ?>">
                                                        <input type="number" name="pcs_per_dus_ctn" class="form-control mt-2" placeholder="Pcs Per Dus/Ctn" value="<?= $pcs_per_dus_ctn; ?>"> <input type="number" name="hargamodal" class="form-control mt-2" placeholder="Harga Modal" value="<?= $hargamodal; ?>">
                                                        <input type="number" name="hargajual" class="form-control mt-2" placeholder="Harga Produk" value="<?= $hargajual; ?>">
                                                        <input type="hidden" name="idp" value="<?=$idproduk;?>">
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success" name="editbarang">Submit</button>
                                                    </div>

                                                    </form>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="delete<?=$idproduk;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Hapus <?= $namaproduk; ?></h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                    <div class="modal-body">
                                                        Apakah anda yakin ingin menghapus barang ini?
                                                        <input type="hidden" name="idp" value="<?=$idproduk;?>">
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success" name="hapusbarang">Ya</button>
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
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>

    <!-- The Modal Tambah Barang -->
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
                <input type="text" name="namaproduk" class="form-control" placeholder="Nama produk">
                <input type="number" name="pcs_per_dus_ctn" class="form-control mt-2" placeholder="Isi per Ctn">
                <input type="number" name="stock" class="form-control mt-2" placeholder="Pcs">
                <input type="number" name="jumlah_dus_ctn" class="form-control mt-2" placeholder="Ctn">                       
                <input type="number" name="hargamodal" class="form-control mt-2" placeholder="Harga Modal">
                <input type="number" name="hargajual" class="form-control mt-2" placeholder="Harga Jual">
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" name="tambahbarang">Submit</button>
            </div>

            </form>

            </div>
        </div>
    </div>

    

</html>
