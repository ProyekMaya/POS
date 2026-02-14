<?php
    require '../ceklogin.php'; // Pastikan file koneksi database Anda terhubung di ceklogin.php atau terpisah
    $idpenjualan_dari_url = isset($_GET['idp']) ? mysqli_real_escape_string($c, $_GET['idp']) : ''; 
    $idkasir_terkait_penjualan = ''; // Akan diisi dari database (ini adalah idkasir dari tabel penjualan)
    $username_kasir = 'Tidak Diketahui'; // Default value
    if (!empty($idpenjualan_dari_url)) {
        $ambil_idkasir_q = mysqli_query($c, "SELECT idkasir FROM penjualan WHERE idpenjualan = '$idpenjualan_dari_url'");      
        if ($ambil_idkasir_q) { // Cek apakah query pertama berhasil dieksekusi
            if (mysqli_num_rows($ambil_idkasir_q) > 0) {
                $penjualan_data = mysqli_fetch_array($ambil_idkasir_q);
                $idkasir_terkait_penjualan = $penjualan_data['idkasir'];
                $ambilkasir_q = mysqli_query($c, "SELECT username FROM datakasir WHERE idkasir = '$idkasir_terkait_penjualan'");         
                if ($ambilkasir_q) { // Cek apakah query kasir berhasil dieksekusi
                    if (mysqli_num_rows($ambilkasir_q) > 0) {
                        $kasir_data = mysqli_fetch_array($ambilkasir_q);
                        $username_kasir = $kasir_data['username'];
                    } else {
                        echo "<script>alert('Data Kasir (dengan ID: " . $idkasir_terkait_penjualan . ") untuk penjualan ini tidak ditemukan atau tidak valid di tabel datakasir.'); window.location.href='penjualan.php';</script>";
                        exit();
                    }
                } else {
                    echo "<script>alert('Kesalahan Query Data Kasir: " . mysqli_error($c) . "'); window.location.href='index.php';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('ID penjualan tidak ditemukan atau data penjualan tidak valid.'); window.location.href='index.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Kesalahan Query penjualan: " . mysqli_error($c) . "'); window.location.href='index.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('ID penjualan tidak ditemukan di URL.'); window.location.href='index.php';</script>";
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
    // Tidak perlu memeriksa role 'admin' atau 'kasir' di halaman ini, karena sudah di-redirect di atas.
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Detail Penjualan - Toko Mamah Azis</title>
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
                            <!-- Kasir: Ibu Azis -->
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
                    <div class="container-fluid px-4 ">
                        <h1 class="mt-4">Detail Penjualan: <?=$idpenjualan_dari_url;?> <br>  
                        </h1> <br>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Item Penjualan                              
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Produk</th>
                                            <th>Harga Satuan</th>
                                            <th>Jumlah (Pcs)</th>
                                            <th>Jumlah (Ctn)</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Mengambil detail item untuk penjualan ini
                                        $get_detail_penjualan = mysqli_query($c, "SELECT dp.*, pr.namaproduk, pr.hargajual, pr.isi_pcs_per_ctn FROM detailpenjualan dp JOIN produk pr ON dp.idproduk = pr.idproduk WHERE dp.idpenjualan = '$idpenjualan_dari_url'");
                                        $j = 1;
                                        $grand_total_penjualan = 0;
                                        while ($detail = mysqli_fetch_array($get_detail_penjualan)) {
                                            $namaproduk = $detail['namaproduk'];
                                            $hargajual = $detail['hargajual'];
                                            $jumlah_item = $detail['jumlah'];
                                            $subtotal_item = $hargajual * $jumlah_item;
                                            $grand_total_penjualan += $subtotal_item;

                                            // Mengambil nilai isi_pcs_per_ctn dari hasil query
                                            $isi_pcs_per_ctn = $detail['isi_pcs_per_ctn'];
                                            $jumlah_ctn_dus_display = "0"; // Default value untuk jumlah dus

                                            // Perhitungan Jumlah (Ctn/Dus)
                                            if ($isi_pcs_per_ctn > 0) {
                                                $jumlah_ctn_dus_whole = floor($jumlah_item / $isi_pcs_per_ctn);
                                                // Hanya ambil bagian dus/karton utuh
                                                $jumlah_ctn_dus_display = $jumlah_ctn_dus_whole;
                                            } else {
                                                // Jika isi_pcs_per_ctn 0 atau tidak diset, tampilkan 0 untuk kolom dus
                                                // Anda bisa memilih untuk menampilkan $jumlah_item jika ingin menunjukkan total Pcs ketika tidak ada dus
                                                $jumlah_ctn_dus_display = "0";
                                            }
                                            // Ambil ID penting untuk aksi
                                            $iddp = $detail['iddetailpenjualan'];
                                            $idpr = $detail['idproduk'];
                                            $idp = $idpenjualan_dari_url;
                                        ?>
                                            <tr>
                                                <td><?= $j++; ?></td>
                                                <td><?= $namaproduk; ?> (isi per Ctn: <?=$isi_pcs_per_ctn?>)</td>
                                                <td>Rp.<?= number_format($hargajual); ?></td>
                                                <td><?= $jumlah_item; ?></td>
                                                <td><?= $jumlah_ctn_dus_display; ?></td> 
                                                <td>Rp.<?= number_format($subtotal_item); ?></td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="edit<?=$iddp;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Ubah Produk Penjualan</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                     <div class="modal-body">
                                                        <input type="text" name="namaproduk" class="form-control" placeholder="Nama produk" value="<?= $namaproduk; ?> " disabled>
                                                        <input type="number" name="jumlah" class="form-control mt-2" placeholder="Pcs" value="<?= $jumlah_item; ?>">
                                                        <input type="hidden" name="iddp" value="<?=$iddp;?>">
                                                        <input type="hidden" name="idp" value="<?=$idp;?>">
                                                        <input type="hidden" name="idpr" value="<?=$idpr;?>">
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success" name="editdetailpenjualan">Submit</button>
                                                    </div>
                                                </form>

                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Delete -->
                                            <div class="modal fade" id="delete<?=$iddp;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Hapus <?= $namaproduk; ?></h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <form method="post">

                                                    <div class="modal-body">
                                                    Apakah anda yakin ingin menghapus produk ini?
                                                    <input type="hidden" name="iddp" value="<?=$iddp;?>">
                                                    <input type="hidden" name="idpr" value="<?=$idpr;?>">
                                                    <input type="hidden" name="idpenjualan" value="<?=$idp;?>">
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success" name="hapusprodukpenjualan">Ya</button>
                                                    </div>

                                                    </form>

                                                    </div>
                                                </div>
                                            </div>

                                        <?php
                                        }
                                        ?>
                                        <tr>
                                        <td></td><td></td><td></td><td colspan="2"></td> <td><strong>Total Keseluruhan Penjualan:</strong></td>
                                            <td><strong>Rp.<?= number_format($grand_total_penjualan); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <?php
                                // Ambil data pembayaran untuk penjualan ini
                                $query_pembayaran = mysqli_query($c, "SELECT jumlah_bayar, kembalian FROM penjualan WHERE idpenjualan = '$idpenjualan_dari_url'");
                                $data_pembayaran = mysqli_fetch_array($query_pembayaran);
                                $jumlah_bayar_sebelumnya = $data_pembayaran['jumlah_bayar'] ?? 0;
                                $kembalian_sebelumnya = $data_pembayaran['kembalian'] ?? 0;

                                if ($jumlah_bayar_sebelumnya == 0) {
                                ?>
                                    <hr>
                                    <h4>Proses Pembayaran</h4>

                                    <form method="post" id="paymentForm">
                                        <input type="hidden" name="idpenjualan_bayar" value="<?= htmlspecialchars($idpenjualan_dari_url); ?>">
                                        <div class="mb-3">
                                            <label for="grand_total_display" class="form-label">Total Pembayaran:</label>
                                            <input type="text" class="form-control" id="grand_total_display" value="Rp. <?= number_format($grand_total_penjualan, 0, ',', '.'); ?>" readonly>
                                            <input type="hidden" id="grand_total_hidden" value="<?= htmlspecialchars($grand_total_penjualan); ?>">
                                        </div>
                                        <!-- <div class="mb-3">
                                            <label for="cash_input" class="form-label">Uang Tunai Diberikan (Cash):</label>
                                            <input type="number" class="form-control" id="cash_input" name="cash_input" min="<?= htmlspecialchars($grand_total_penjualan); ?>" required>
                                        </div> -->
                                        <div class="mb-3">
                                            <label for="kembalian_hitung" class="form-label">Kembalian:</label>
                                            <input type="text" class="form-control" id="kembalian_hitung" name="kembalian_hitung" readonly value="Rp. 0">
                                        </div>
                                        <input type="hidden" name="cetak_struk" id="cetak_struk_input" value="true"> 

                                        <!-- <button type="submit" name="proses_pembayaran" class="btn btn-success" id="btn_bayar">Bayar</button> -->
                                    </form>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const cashInput = document.getElementById('cash_input');
                                            const kembalianInput = document.getElementById('kembalian_hitung');
                                            const grandTotalHidden = document.getElementById('grand_total_hidden');
                                            const grandTotalPenjualan = parseFloat(grandTotalHidden.value);
                                            const btnBayar = document.getElementById('btn_bayar'); // Ubah id tombol
                                            const paymentForm = document.getElementById('paymentForm'); // Dapatkan form
                                            const cetakStrukInput = document.getElementById('cetak_struk_input'); // Input hidden untuk cetak struk
                                            const ctkStruk = document.getElementById('ctkStruk'); // Input hidden untuk cetak struk

                                            function calculateKembalian() {
                                                const cashGiven = parseFloat(cashInput.value);
                                                let kembalian = 0;

                                                if (isNaN(cashGiven) || cashGiven < grandTotalPenjualan) {
                                                    kembalianInput.value = 'Uang Kurang';
                                                    btnBayar.disabled = true; // Disable tombol jika uang kurang
                                                    ctkStruk.disabled = true; // Disable tombol jika uang kurang
                                                    cashInput.classList.add('is-invalid');
                                                    cashInput.classList.remove('is-valid');
                                                } else {
                                                    kembalian = cashGiven - grandTotalPenjualan;
                                                    kembalianInput.value = 'Rp. ' + kembalian.toLocaleString('id-ID');
                                                    btnBayar.disabled = false; // Enable tombol
                                                    ctkStruk.disabled = false; // Enable tombol
                                                    cashInput.classList.remove('is-invalid');
                                                    cashInput.classList.add('is-valid');
                                                }
                                            }

                                            cashInput.addEventListener('input', calculateKembalian);

                                            // Tambahkan event listener untuk tombol "Bayar"
                                            btnBayar.addEventListener('click', function(event) {
                                                // Cek kembali validasi sebelum menampilkan alert
                                                const cashGiven = parseFloat(cashInput.value);
                                                if (isNaN(cashGiven) || cashGiven < grandTotalPenjualan) {
                                                    alert('Jumlah uang tunai tidak valid atau kurang dari total pembayaran.');
                                                    event.preventDefault(); // Mencegah form submit
                                                    return;
                                                }

                                                const konfirmasiCetak = confirm('Ingin Cetak Struk?');
                                                if (konfirmasiCetak) {
                                                    cetakStrukInput.value = 'true'; // Set value ke 'true' jika OK
                                                } else {
                                                    cetakStrukInput.value = 'false'; // Set value ke 'false' jika Batal
                                                }
                                                // Biarkan form submit secara normal setelah konfirmasi
                                            });

                                            // Panggil sekali saat DOM siap untuk menginisialisasi jika ada nilai awal
                                            calculateKembalian();
                                            
                                        });
                                    </script>
                                <?php } else { ?>
                                    <hr>
                                    <p><strong>Status Pembayaran: Sudah Dibayar</strong></p>
                                    <p>Uang Diberikan: Rp.<?= number_format($jumlah_bayar_sebelumnya); ?></p>
                                    <p>Kembalian: Rp.<?= number_format($kembalian_sebelumnya); ?></p>
                                <?php } ?>

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
        <script>
        function printStruk(id_penjualan_untuk_struk) {
            const strukWindow = window.open('print_struk.php?idp=' + id_penjualan_untuk_struk, '_blank', 'width=400,height=600');
            strukWindow.onload = function() {
                strukWindow.print();
                strukWindow.onafterprint = function() {
                    strukWindow.close();
                };
            };
        }
        </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-HYwDMaLLN4Xv8h0Zg+q+KHlyqkQoA4a3n4vAxzvjE1FLn2JpB+Kz8N2cC9t5rME0" crossorigin="anonymous"></script>

</body>
</html>