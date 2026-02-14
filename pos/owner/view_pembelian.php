<?php
    require '../ceklogin.php'; // Pastikan file koneksi database Anda terhubung di ceklogin.php atau terpisah

    $idpembelian_dari_url = isset($_GET['idp']) ? mysqli_real_escape_string($c, $_GET['idp']) : '';
    $idowner_terkait_pembelian = ''; // Akan diisi dari database (ini adalah idowner dari tabel pembelian)
    $username_owner = 'Tidak Diketahui'; // Default value

    if (!empty($idpembelian_dari_url)) {
        // Ambil idowner dari tabel pembelian
        $ambil_idowner_q = mysqli_query($c, "SELECT idowner, namasupplier, alamatsupplier FROM pembelian WHERE idpembelian = '$idpembelian_dari_url'");
        if ($ambil_idowner_q) { // Cek apakah query pertama berhasil dieksekusi
            if (mysqli_num_rows($ambil_idowner_q) > 0) {
                $pembelian_data = mysqli_fetch_array($ambil_idowner_q);
                $idowner_terkait_pembelian = $pembelian_data['idowner'];
                $nama_supplier_pembelian = $pembelian_data['namasupplier'];
                $alamat_supplier_pembelian = $pembelian_data['alamatsupplier'];

                // Ambil username owner dari tabel dataowner
                $ambilowner_q = mysqli_query($c, "SELECT username FROM dataowner WHERE idowner = '$idowner_terkait_pembelian'");
                if ($ambilowner_q) { // Cek apakah query owner berhasil dieksekusi
                    if (mysqli_num_rows($ambilowner_q) > 0) {
                        $owner_data = mysqli_fetch_array($ambilowner_q);
                        $username_owner = $owner_data['username'];
                    } else {
                        echo "<script>alert('Data Owner (dengan ID: " . $idowner_terkait_pembelian . ") untuk pembelian ini tidak ditemukan atau tidak valid di tabel dataowner.'); window.location.href='pembelian_owner.php';</script>";
                        exit();
                    }
                } else {
                    echo "<script>alert('Kesalahan Query Data Owner: " . mysqli_error($c) . "'); window.location.href='pembelian_owner.php';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('ID pembelian tidak ditemukan atau data pembelian tidak valid.'); window.location.href='pembelian_owner.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Kesalahan Query pembelian: " . mysqli_error($c) . "'); window.location.href='pembelian_owner.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('ID pembelian tidak ditemukan di URL.'); window.location.href='pembelian_owner.php';</script>";
        exit();
    }

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
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Detail Pembelian - Toko Mamah Azis</title>
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
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'view_penjualan.php') ? 'active' : '' ?>" href="penjualan_owner.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-shopping-basket"></i></div>
                                Penjualan
                            </a>
                            <a class="nav-link w-100 text-start ps-4<?= ($current_page == 'stock_owner.php') ? 'active' : '' ?>" href="stock_owner.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-archive"></i></div>
                                Stok Barang
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'view_pembelian.php') ? 'active' : '' ?>" href="pembelian_owner.php">
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
                    <div class="container-fluid px-4 ">
                        <h1 class="mt-4">Detail Pembelian: <?=htmlspecialchars($idpembelian_dari_url);?> <br>
                            <span class="fs-5">Supplier: <?=htmlspecialchars($nama_supplier_pembelian);?>, <?=htmlspecialchars($alamat_supplier_pembelian);?></span>
                        </h1> <br>

                        <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#myModal">
                            Tambah Produk
                        </button>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Item Pembelian
                                
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>Harga Modal Satuan</th>
                                            <th>Jumlah (Pcs)</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Mengambil detail item untuk pembelian ini
                                        $get_detail_pembelian = mysqli_query($c, "SELECT dp.*, pr.namaproduk, pr.hargamodal, pr.isi_pcs_per_ctn FROM detailpembelian dp JOIN produk pr ON dp.idproduk = pr.idproduk WHERE dp.idpembelian = '$idpembelian_dari_url'");
                                        $j = 1;
                                        $grand_total_pembelian = 0;
                                        while ($detail = mysqli_fetch_array($get_detail_pembelian)) {
                                            
                                            // 1. Deklarasi dan inisialisasi Variabel (Dipindahkan ke sini)
                                            $namaproduk = $detail['namaproduk'];
                                            $hargamodal = $detail['hargamodal'];
                                            $jumlah_item = $detail['jumlah'];
                                            $isi_pcs_per_ctn = $detail['isi_pcs_per_ctn'];

                                            $subtotal_item = $hargamodal * $jumlah_item;
                                            $grand_total_pembelian += $subtotal_item;

                                            // 2. Perhitungan Awal untuk Modal Edit (Gunakan nilai yang sudah ada)
                                            $jumlah_ctn_dus_awal = 0;
                                            $jumlah_pcs_awal = $jumlah_item;
                                            
                                            if ($isi_pcs_per_ctn > 0) {
                                                $jumlah_ctn_dus_awal = floor($jumlah_item / $isi_pcs_per_ctn);
                                                $jumlah_pcs_awal = $jumlah_item % $isi_pcs_per_ctn;
                                            }

                                            // Ambil ID penting untuk aksi
                                            $iddp = $detail['iddetailpembelian'];
                                            $idpr = $detail['idproduk'];
                                            $idp = $idpembelian_dari_url; // ID Pembelian
                                            ?>
                                            <tr>
                                                <td><?= $j++; ?></td>
                                                <td><?= htmlspecialchars($namaproduk); ?> (isi per Ctn: <?=$isi_pcs_per_ctn?>)</td>
                                                <td>Rp.<?= number_format($hargamodal); ?></td>
                                                <td><?= $jumlah_item; ?></td>
                                                <td>Rp.<?= number_format($subtotal_item); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#edit<?=$iddp;?>">
                                                        Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete<?=$iddp;?>">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit Produk Pembelian -->
                                            <div class="modal fade" id="edit<?=$iddp;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Ubah <?= $namaproduk; ?></h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="post">
                                                            <div class="modal-body">
                                                                <label for="namaproduk">Nama Produk</label>
                                                                <input type="text" name="namaproduk" class="form-control" placeholder="Nama produk" value="<?= htmlspecialchars($namaproduk); ?> " disabled>
                                                                
                                                                <div class="form-text mt-2">
                                                                    Isi per Dus/Ctn: <span id="perDusCtnValueEdit_<?=$iddp?>"><?= $isi_pcs_per_ctn; ?></span> Pcs
                                                                </div>
                                                                
                                                                <label for="jumlah_pcs_edit" class="mt-2">Jumlah Pcs</label>
                                                                <input type="number" name="jumlah_pcs_edit" id="inputPcsEdit_<?=$iddp?>" class="form-control" placeholder="Jumlah Pcs" min="0" value="<?= $jumlah_pcs_awal; ?>">

                                                                <label for="jumlah_ctn_dus_edit" class="mt-2">Jumlah Ctn/Dus</label>
                                                                <input type="number" name="jumlah_ctn_dus_edit" id="inputCtnDusEdit_<?=$iddp?>" class="form-control" placeholder="Jumlah Ctn/Dus" min="0" value="<?= $jumlah_ctn_dus_awal; ?>">
                                                                
                                                                <label for="hargamodal_edit" class="mt-2">Harga Modal Satuan</label>
                                                                <input type="number" name="hargamodal_edit" class="form-control" placeholder="Harga Modal Satuan (Rp)" min="1" value="<?= $hargamodal; ?>" required>

                                                                <input type="hidden" name="iddp" value="<?=$iddp;?>">
                                                                <input type="hidden" name="idp" value="<?=$idp;?>">
                                                                <input type="hidden" name="idpr" value="<?=$idpr;?>">
                                                                <input type="hidden" name="isi_pcs_per_ctn" value="<?=$isi_pcs_per_ctn;?>"> 
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-success" name="ubahprodukpembelian">Submit</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Hapus Produk Pembelian -->
                                            <div class="modal fade" id="delete<?=$iddp;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Hapus <?= htmlspecialchars($namaproduk); ?></h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="post">
                                                            <div class="modal-body">
                                                                Apakah Anda yakin ingin menghapus produk ini dari pembelian?
                                                                <input type="hidden" name="iddp" value="<?=$iddp;?>">
                                                                <input type="hidden" name="idpr" value="<?=$idpr;?>">
                                                                <input type="hidden" name="idpembelian" value="<?=$idp;?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-success" name="hapusprodukpembelian">Ya</button>
                                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php
                                        } // end of while
                                        ?>
                                        <tr>
                                            <td></td><td></td><td colspan="2"></td> <td><strong>Total Keseluruhan Pembelian:</strong></td>
                                            <td><strong>Rp.<?= number_format($grand_total_pembelian); ?></strong></td>
                                            <td></td> <!-- Kolom aksi untuk total -->
                                        </tr>
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

        <!-- Modal Tambah Produk ke Pembelian -->
        <div class="modal fade" id="myModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Produk ke Pembelian</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post">
                        <div class="modal-body">
                            Pencarian Barang
                            <div style="position: relative;">
                                <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan nama produk...">
                                <input type="hidden" name="idproduk" id="selectedProductId">

                                <div id="searchResults" class="list-group" 
                                    style="position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; 
                                            border: 1px solid #ced4da; border-top: none; background-color: #fff; display: none;">
                                </div>
                            </div>
                            <div id="perDusCtnInfo" class="form-text mt-2">
                                Isi per Dus/Ctn: <span id="perDusCtnValue"></span>
                            </div>
                            
                            <!-- Input Harga Supplier -->
                            <input type="number" name="harga_supplier" class="form-control mt-3" placeholder="Harga dari Supplier" min="0" required>

                            <input type="number" name="stock" id="inputPcs" class="form-control mt-3" placeholder="Jumlah Pcs" min="0">
                            <input type="number" name="jumlah_ctn_dus" id="inputCtnDus" class="form-control mt-2" placeholder="Jumlah Ctn" min="0">
                            <input type="hidden" name="idp" value="<?=htmlspecialchars($idpembelian_dari_url);?>">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success" name="tambahprodukpembelian">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="../js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const searchResultsDiv = document.getElementById('searchResults');
                const selectedProductIdInput = document.getElementById('selectedProductId');
                const perDusCtnValueSpan = document.getElementById('perDusCtnValue');
                const inputPcsElement = document.getElementById('inputPcs');
                const inputCtnDusElement = document.getElementById('inputCtnDus');

                searchInput.addEventListener('keyup', function() {
                    let searchValue = this.value.toLowerCase();
                    searchResultsDiv.innerHTML = '';
                    perDusCtnValueSpan.textContent = '';
                    selectedProductIdInput.value = ''; // Clear selected product ID on new search
                    inputPcsElement.value = ''; // Clear Pcs input
                    inputCtnDusElement.value = ''; // Clear Ctn input
                    inputPcsElement.removeAttribute('max'); // Remove max attribute

                    if (searchValue.length > 0) {
                        searchResultsDiv.style.display = 'block';
                        <?php
                        // Fetch all products not already in this specific purchase
                        $getproduk_all = mysqli_query($c, "SELECT idproduk, namaproduk, isi_pcs_per_ctn, stock FROM produk WHERE idproduk NOT IN (SELECT idproduk FROM detailpembelian WHERE idpembelian='$idpembelian_dari_url')");
                        $products_all = [];
                        if ($getproduk_all) {
                            while($pl = mysqli_fetch_array($getproduk_all)) {
                                $products_all[] = [
                                    'idproduk' => $pl['idproduk'],
                                    'namaproduk' => $pl['namaproduk'],
                                    'isi_pcs_per_ctn' => $pl['isi_pcs_per_ctn'] ?? 0, // Default to 0 if not set
                                    'stock' => $pl['stock']
                                ];
                            }
                        }
                        echo "let allProducts = " . json_encode($products_all) . ";\n";
                        ?>
                        let filteredProducts = allProducts.filter(product => {
                            return product.namaproduk.toLowerCase().includes(searchValue);
                        });

                        if (filteredProducts.length > 0) {
                            filteredProducts.forEach(product => {
                                let productItem = document.createElement('div');
                                productItem.classList.add('list-group-item', 'list-group-item-action');
                                productItem.innerHTML = `${product.namaproduk} (Stok: ${product.stock})`;
                                productItem.style.cursor = 'pointer';
                                productItem.setAttribute('data-id', product.idproduk);
                                productItem.setAttribute('data-name', product.namaproduk);
                                productItem.setAttribute('data-isipcs', product.isi_pcs_per_ctn);
                                productItem.setAttribute('data-stock', product.stock);

                                productItem.addEventListener('click', function() {
                                    searchInput.value = this.getAttribute('data-name');
                                    selectedProductIdInput.value = this.getAttribute('data-id');
                                    perDusCtnValueSpan.textContent = this.getAttribute('data-isipcs');

                                    // For purchases, there's no max based on current stock for adding.
                                    // You might want to set a min of 1 or no min/max here.
                                    inputPcsElement.min = 0; // Or 1 if you want at least 1 Pcs
                                    inputCtnDusElement.min = 0; // Or 1 if you want at least 1 Ctn

                                    searchResultsDiv.style.display = 'none';
                                });
                                searchResultsDiv.appendChild(productItem);
                            });
                        } else {
                            searchResultsDiv.innerHTML = '<div class="list-group-item text-muted">Tidak ada hasil ditemukan.</div>';
                        }
                    } else {
                        searchResultsDiv.style.display = 'none';
                        selectedProductIdInput.value = '';
                        perDusCtnValueSpan.textContent = '';
                        inputPcsElement.value = '';
                        inputCtnDusElement.value = '';
                        inputPcsElement.removeAttribute('max');
                    }
                });

                document.addEventListener('click', function(event) {
                    if (!searchInput.contains(event.target) && !searchResultsDiv.contains(event.target)) {
                        searchResultsDiv.style.display = 'none';
                    }
                });

                // Calculate total Pcs from Pcs and Ctn inputs
                function calculateTotalPcs() {
                    const pcsInput = parseFloat(inputPcsElement.value) || 0;
                    const ctnInput = parseFloat(inputCtnDusElement.value) || 0;
                    const pcsPerCtn = parseFloat(perDusCtnValueSpan.textContent) || 0;

                    const totalPcs = pcsInput + (ctnInput * pcsPerCtn);
                    // You might want to display this total somewhere, or just use it for submission
                    // console.log("Total Pcs for submission:", totalPcs);
                }

                inputPcsElement.addEventListener('input', calculateTotalPcs);
                inputCtnDusElement.addEventListener('input', calculateTotalPcs);
            });
        </script>
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-HYwDMaLLN4Xv8h0Zg+q+KHlyqkQoA4a3n4vAxzvjE1FLn2JpB+Kz8N2cC9t5rME0" crossorigin="anonymous"></script>

    </body>
</html>
