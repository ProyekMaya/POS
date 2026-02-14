<?php
require 'ceklogin.php'; // Pastikan file koneksi database Anda terhubung di ceklogin.php atau terpisah

$idpenjualan_dari_url = isset($_GET['idp']) ? $_GET['idp'] : ''; // mysqli_real_escape_string akan dilakukan di prepared statement
$idkasir_terkait_penjualan = ''; // Akan diisi dari database (ini adalah idkasir dari tabel penjualan)
$username_kasir = 'Tidak Diketahui'; // Default value

if (!empty($idpenjualan_dari_url)) {
    // Menggunakan prepared statement untuk ambil idkasir dari penjualan
    $stmt_penjualan = $c->prepare("SELECT idkasir, jumlah_bayar, kembalian FROM penjualan WHERE idpenjualan = ?");
    if ($stmt_penjualan) {
        $stmt_penjualan->bind_param("s", $idpenjualan_dari_url);
        $stmt_penjualan->execute();
        $ambil_idkasir_q = $stmt_penjualan->get_result();

        if ($ambil_idkasir_q && mysqli_num_rows($ambil_idkasir_q) > 0) {
            $penjualan_data = mysqli_fetch_array($ambil_idkasir_q);
            $idkasir_terkait_penjualan = $penjualan_data['idkasir'];
            $jumlah_bayar_sebelumnya = $penjualan_data['jumlah_bayar'] ?? 0; // Get payment status
            $kembalian_sebelumnya = $penjualan_data['kembalian'] ?? 0; // Get change

            $stmt_datakasir = $c->prepare("SELECT username FROM datakasir WHERE idkasir = ?");
            if ($stmt_datakasir) {
                $stmt_datakasir->bind_param("i", $idkasir_terkait_penjualan); // Asumsi idkasir adalah integer
                $stmt_datakasir->execute();
                $ambilkasir_q = $stmt_datakasir->get_result();

                if ($ambilkasir_q && mysqli_num_rows($ambilkasir_q) > 0) {
                    $kasir_data = mysqli_fetch_array($ambilkasir_q);
                    $username_kasir = $kasir_data['username'];
                } else {
                    echo "<script>alert('Data Kasir (dengan ID: " . htmlspecialchars($idkasir_terkait_penjualan) . ") untuk penjualan ini tidak ditemukan atau tidak valid di tabel datakasir.'); window.location.href='penjualan.php';</script>";
                    exit();
                }
                $stmt_datakasir->close();
            } else {
                echo "<script>alert('Kesalahan Prepared Statement Data Kasir: " . $c->error . "'); window.location.href='index.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('ID penjualan tidak ditemukan atau data penjualan tidak valid.'); window.location.href='index.php';</script>";
            exit();
        }
        $stmt_penjualan->close();
    } else {
        echo "<script>alert('Kesalahan Prepared Statement Penjualan: " . $c->error . "'); window.location.href='index.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('ID penjualan tidak ditemukan di URL.'); window.location.href='index.php';</script>";
    exit();
}

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
        <title>Detail Penjualan - Toko Mamah Azis</title>
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
                            ?>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-grip-horizontal"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'view.php' || $current_page == 'penjualan.php') ? 'active' : '' ?>" href="penjualan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-shopping-basket"></i></div>
                                Penjualan
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
                    <div class="container-fluid px-4 ">
                        <h1 class="mt-4">Detail Penjualan: <?=htmlspecialchars($idpenjualan_dari_url);?> <br>
                        </h1> <br>
                        <?php if ($jumlah_bayar_sebelumnya == 0) { ?>
                            <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#myModal">
                                Tambah Produk
                            </button>
                        <?php } ?>
                        <?php
                        // Only show the print receipt button if it has been paid
                        if ($jumlah_bayar_sebelumnya > 0) { ?>
                            <button type="button" class="btn btn-success mb-4" id="ctkStruk" onclick="printStruk('<?=htmlspecialchars($idpenjualan_dari_url);?>')">
                                <i class="fas fa-print"></i> Cetak Struk
                            </button>
                        <?php } ?>

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
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Mengambil detail item untuk penjualan ini
                                        $stmt_detail_penjualan = $c->prepare("SELECT dp.*, pr.namaproduk, pr.hargajual, pr.isi_pcs_per_ctn, pr.stock AS product_stock FROM detailpenjualan dp JOIN produk pr ON dp.idproduk = pr.idproduk WHERE dp.idpenjualan = ?");
                                        if (!$stmt_detail_penjualan) { error_log("Prepare failed: " . $c->error); die("Database error."); }
                                        $stmt_detail_penjualan->bind_param("s", $idpenjualan_dari_url);
                                        $stmt_detail_penjualan->execute();
                                        $get_detail_penjualan = $stmt_detail_penjualan->get_result();

                                        $j = 1;
                                        $grand_total_penjualan = 0;
                                        while ($detail = mysqli_fetch_array($get_detail_penjualan)) {
                                            $namaproduk = $detail['namaproduk'];
                                            $hargajual = $detail['hargajual'];
                                            $jumlah_item = $detail['jumlah'];
                                            $isi_pcs_per_ctn = $detail['isi_pcs_per_ctn'] ?? 0; // Pastikan default 0 jika null
                                            $subtotal_item = $hargajual * $jumlah_item;
                                            $grand_total_penjualan += $subtotal_item;
                                            $product_stock = $detail['product_stock']; // Stok total produk di database

                                            // Ambil ID penting untuk aksi
                                            $iddp = $detail['iddetailpenjualan'];
                                            $idpr = $detail['idproduk'];
                                            $idp = $idpenjualan_dari_url; // Sudah ada di awal skrip
                                        ?>
                                            <tr>
                                                <td><?= $j++; ?></td>
                                                <td><?= htmlspecialchars($namaproduk); ?></td> 
                                                <!-- (isi per Ctn: <=htmlspecialchars($isi_pcs_per_ctn)?>) -->
                                                <td>Rp.<?= number_format($hargajual); ?></td>
                                                <td><?= htmlspecialchars($jumlah_item); ?></td>
                                                <td>Rp.<?= number_format($subtotal_item); ?></td>
                                                <td>
                                                    <?php if ($jumlah_bayar_sebelumnya == 0) { // Only show buttons if not paid ?>
                                                        <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#edit<?=$iddp;?>">
                                                            Edit
                                                        </button>
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?=$iddp;?>">
                                                            Delete
                                                        </button>
                                                    <?php } else { ?>
                                                        <span class="text-muted">Sudah Dibayar</span>
                                                    <?php } ?>
                                                </td>
                                            </tr>

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
                                                   
                                                                <div class="form-text mt-2 text-danger">
                                                                    Stok Tersedia: <strong><?= htmlspecialchars($product_stock + $jumlah_item) ?></strong> (Stok saat ini di gudang + Jumlah item yang ada di penjualan ini)
                                                                </div>
                                                                <label for="jumlah_pcs" class="mt-2">Jumlah (Pcs)</label>
                                                                <input type="number" name="jumlah_pcs" class="form-control mb-2" placeholder="Pcs" min="0"
                                                                    value="<?= htmlspecialchars($jumlah_item % $isi_pcs_per_ctn) ?>"
                                                                    max="<?= htmlspecialchars(($product_stock + $jumlah_item) - floor($jumlah_item / $isi_pcs_per_ctn) * $isi_pcs_per_ctn) ?>">
                                                                <input type="hidden" name="idp" value="<?=htmlspecialchars($idpenjualan_dari_url);?>">
                                                                <input type="hidden" name="iddp" value="<?=htmlspecialchars($iddp);?>">
                                                                <input type="hidden" name="idpr" value="<?=htmlspecialchars($idpr);?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-success" name="editdetailpenjualan">Submit</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="delete<?=$iddp;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Hapus <?= htmlspecialchars($namaproduk); ?></h4>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="post">
                                                            <div class="modal-body">
                                                            Apakah anda yakin ingin menghapus produk ini?
                                                            <input type="hidden" name="iddp" value="<?=htmlspecialchars($iddp);?>">
                                                            <input type="hidden" name="idpr" value="<?=htmlspecialchars($idpr);?>">
                                                            <input type="hidden" name="idpenjualan" value="<?=htmlspecialchars($idp);?>">
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
                                        $stmt_detail_penjualan->close(); // Tutup statement setelah loop
                                        ?>
                                        <tr>
                                            <td></td><td></td><td></td><td colspan="2"></td> <td><strong>Total Keseluruhan Penjualan:</strong></td>
                                            <td><strong>Rp.<?= number_format($grand_total_penjualan); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <?php
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
                                        <div class="mb-3">
                                            <label for="cash_input" class="form-label">Uang Tunai Diberikan (Cash):</label>
                                            <input type="number" class="form-control" id="cash_input" name="cash_input" min="<?= htmlspecialchars($grand_total_penjualan); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="kembalian_hitung" class="form-label">Kembalian:</label>
                                            <input type="text" class="form-control" id="kembalian_hitung" name="kembalian_hitung" readonly value="Rp. 0">
                                        </div>
                                        <input type="hidden" name="cetak_struk" id="cetak_struk_input" value="true">
                                        <button type="submit" name="proses_pembayaran" class="btn btn-success" id="btn_bayar">Bayar</button>
                                    </form>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const cashInput = document.getElementById('cash_input');
                                            const kembalianInput = document.getElementById('kembalian_hitung');
                                            const grandTotalHidden = document.getElementById('grand_total_hidden');
                                            const grandTotalPenjualan = parseFloat(grandTotalHidden.value);
                                            const btnBayar = document.getElementById('btn_bayar');
                                            const cetakStrukInput = document.getElementById('cetak_struk_input');

                                            function calculateKembalian() {
                                                const cashGiven = parseFloat(cashInput.value);
                                                let kembalian = 0;

                                                if (isNaN(cashGiven) || cashGiven < grandTotalPenjualan) {
                                                    kembalianInput.value = 'Uang Kurang';
                                                    btnBayar.disabled = true;
                                                    cashInput.classList.add('is-invalid');
                                                    cashInput.classList.remove('is-valid');
                                                } else {
                                                    kembalian = cashGiven - grandTotalPenjualan;
                                                    kembalianInput.value = 'Rp. ' + kembalian.toLocaleString('id-ID');
                                                    btnBayar.disabled = false;
                                                    cashInput.classList.remove('is-invalid');
                                                    cashInput.classList.add('is-valid');
                                                }
                                            }

                                            cashInput.addEventListener('input', calculateKembalian);

                                            btnBayar.addEventListener('click', function(event) {
                                                const cashGiven = parseFloat(cashInput.value);
                                                if (isNaN(cashGiven) || cashGiven < grandTotalPenjualan) {
                                                    alert('Jumlah uang tunai tidak valid atau kurang dari total pembayaran.');
                                                    event.preventDefault();
                                                    return;
                                                }

                                                const konfirmasiCetak = confirm('Ingin Cetak Struk?');
                                                if (konfirmasiCetak) {
                                                    cetakStrukInput.value = 'true';
                                                } else {
                                                    cetakStrukInput.value = 'false';
                                                }
                                            });

                                            calculateKembalian(); // Call once when DOM is ready

                                            // Menyesuaikan nilai max untuk input Pcs dan Ctn/Dus di modal edit
                                            document.querySelectorAll('[id^="edit"]').forEach(modal => {
                                                modal.addEventListener('shown.bs.modal', function() {
                                                    const iddp = this.id.replace('edit', '');
                                                    const inputPcsEdit = this.querySelector('input[name="jumlah_pcs"]');
                                                    const inputCtnDusEdit = this.querySelector('input[name="jumlah_ctn_dus"]');
                                                    const productStockDisplay = this.querySelector('.form-text.text-danger strong');

                                                    if (inputPcsEdit && inputCtnDusEdit && productStockDisplay) {
                                                        const totalAvailableStock = parseInt(productStockDisplay.textContent);
                                                        const pcsPerCtn = parseInt(this.querySelector('.form-text strong').textContent);

                                                        const trueAvailableStock = totalAvailableStock;

                                                        inputPcsEdit.setAttribute('max', trueAvailableStock);
                                                        if (pcsPerCtn > 0) {
                                                            inputCtnDusEdit.setAttribute('max', Math.floor(trueAvailableStock / pcsPerCtn));
                                                        } else {
                                                            inputCtnDusEdit.removeAttribute('max');
                                                        }

                                                        const updateEditMaxValues = () => {
                                                            let currentPcsVal = parseInt(inputPcsEdit.value) || 0;
                                                            let currentCtnVal = parseInt(inputCtnDusEdit.value) || 0;
                                                            let totalSelectedPcs = (currentCtnVal * pcsPerCtn) + currentPcsVal;

                                                            if (totalSelectedPcs > trueAvailableStock) {
                                                                inputPcsEdit.setCustomValidity('Jumlah melebihi stok tersedia.');
                                                                inputCtnDusEdit.setCustomValidity('Jumlah melebihi stok tersedia.');
                                                            } else {
                                                                inputPcsEdit.setCustomValidity('');
                                                                inputCtnDusEdit.setCustomValidity('');
                                                            }
                                                        };
                                                        inputPcsEdit.addEventListener('input', updateEditMaxValues);
                                                        inputCtnDusEdit.addEventListener('input', updateEditMaxValues);
                                                    }
                                                });
                                            });
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
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
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

        <?php if ($jumlah_bayar_sebelumnya == 0) { ?>
            <div class="modal fade" id="myModal">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h4 class="modal-title">Tambah Barang</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <form method="post">
                            <div class="modal-body">
                                Pencarian Barang
                                <div style="position: relative;">
                                    <input type="text" id="searchInput" class="form-control mt-2" placeholder="Cari berdasarkan nama produk...">
                                    <input type="hidden" name="idproduk" id="selectedProductId">

                                    <div id="searchResults" class="list-group" style="position: absolute; z-index: 1000; width: 100%; max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; border-top: none; background-color: #fff; display: none;">
                                    </div>
                                </div>
                                <div class="form-text mt-2 text-danger">
                                    Stok Tersedia: <strong id="stockValueTambah"></strong>
                                </div>

                                <label for="stock" class="mt-4">Jumlah (Pcs)</label>
                                <input type="number" name="stock" id="inputPcs" class="form-control mt-2 mb-2" placeholder="Pcs" min="0">
                                <input type="hidden" id="currentIdPenjualan" name="idp" value="<?=htmlspecialchars($idpenjualan_dari_url);?>">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success" name="addproduk">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('searchInput').addEventListener('keyup', function() {
                    let searchValue = this.value.toLowerCase();
                    let searchResultsDiv = document.getElementById('searchResults');
                    let selectedProductIdInput = document.getElementById('selectedProductId');
                    let stockValueTambahSpan = document.getElementById('stockValueTambah');
                    let inputPcsElement = document.getElementById('inputPcs');

                    searchResultsDiv.innerHTML = '';
                    stockValueTambahSpan.textContent = '';
                    selectedProductIdInput.value = '';
                    inputPcsElement.value = '';

                    inputPcsElement.removeAttribute('max');

                    if (searchValue.length > 0) {
                        searchResultsDiv.style.display = 'block';

                        let idPenjualan = document.getElementById('currentIdPenjualan').value;

                        fetch(`get_product_data.php?idpenjualan=${idPenjualan}&keyword=${searchValue}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok ' + response.statusText);
                                }
                                return response.json();
                            })
                            .then(allProducts => {
                                let filteredProducts = allProducts.filter(product => {
                                    return product.namaproduk.toLowerCase().includes(searchValue);
                                });

                                if (filteredProducts.length > 0) {
                                    filteredProducts.forEach(product => {
                                        let productItem = document.createElement('div');
                                        productItem.classList.add('list-group-item', 'list-group-item-action');
                                        productItem.innerHTML = `${product.namaproduk} - (Stok: ${product.stock})`;
                                        productItem.style.cursor = 'pointer';
                                        productItem.setAttribute('data-id', product.idproduk);
                                        productItem.setAttribute('data-name', product.namaproduk);
                                        productItem.setAttribute('data-stock', product.stock);

                                        productItem.addEventListener('click', function() {
                                            this.closest('.modal-body').querySelector('#searchInput').value = this.getAttribute('data-name');
                                            selectedProductIdInput.value = this.getAttribute('data-id');
                                            stockValueTambahSpan.textContent = this.getAttribute('data-stock');

                                            const stockLimit = parseInt(this.getAttribute('data-stock'));
                                            const pcsPerDus = parseInt(this.getAttribute('data-isipcs'));

                                            searchResultsDiv.style.display = 'none';

                                            inputPcsElement.value = '';
                                            inputPcsElement.setCustomValidity('');
                                        });
                                        searchResultsDiv.appendChild(productItem);
                                    });
                                } else {
                                    searchResultsDiv.innerHTML = '<div class="list-group-item text-muted">Tidak ada hasil ditemukan.</div>';
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching products:', error);
                                searchResultsDiv.innerHTML = '<div class="list-group-item text-danger">Terjadi kesalahan saat memuat produk.</div>';
                            });
                    } else {
                        searchResultsDiv.style.display = 'none';
                    }
                });

                document.addEventListener('click', function(event) {
                    let searchInput = document.getElementById('searchInput');
                    let searchResultsDiv = document.getElementById('searchResults');
                    if (!searchInput.contains(event.target) && !searchResultsDiv.contains(event.target)) {
                        searchResultsDiv.style.display = 'none';
                    }
                });

                document.addEventListener('DOMContentLoaded', function() {
                    const inputPcs = document.getElementById('inputPcs');
                    const stockValueTambah = document.getElementById('stockValueTambah');
                    const addProductForm = document.querySelector('#myModal form');
                    const addProductSubmitBtn = addProductForm.querySelector('button[name="addproduk"]');

                    const validateAddProductInputs = () => {
                        const currentStock = parseInt(stockValueTambah.textContent);
                        const pcsInput = parseInt(inputPcs.value) || 0;
                        const selectedProductId = document.getElementById('selectedProductId').value;

                        let totalRequestedPcs = pcsInput;
                        if (pcsPerCtn > 0) {
                            totalRequestedPcs += (ctnDusInput * pcsPerCtn);
                        }

                        if (!selectedProductId) {
                            inputPcs.setCustomValidity('Pilih produk terlebih dahulu.');
                            addProductSubmitBtn.disabled = true;
                            return false;
                        }

                        if (totalRequestedPcs <= 0) {
                            inputPcs.setCustomValidity('Jumlah harus lebih besar dari 0.');
                            addProductSubmitBtn.disabled = true;
                            return false;
                        }

                        if (totalRequestedPcs > currentStock) {
                            inputPcs.setCustomValidity('Jumlah melebihi stok tersedia. Maksimal: ' + currentStock + ' Pcs.');
                            addProductSubmitBtn.disabled = true;
                            return false;
                        } else {
                            inputPcs.setCustomValidity('');
                            addProductSubmitBtn.disabled = false;
                            return true;
                        }
                    };

                    inputPcs.addEventListener('input', validateAddProductInputs);
                    document.getElementById('myModal').addEventListener('shown.bs.modal', validateAddProductInputs);
                    document.getElementById('searchResults').addEventListener('click', function(event) {
                        if (event.target.classList.contains('list-group-item-action')) {
                            setTimeout(validateAddProductInputs, 0);
                        }
                    });
                });
            </script>
        <?php } ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-HYwDMaLLN4Xv8h0Zg+q+KHlyqkQoA4a3n4vAxzvjE1FLn2JpB+Kz8N2cC9t5rME0" crossorigin="anonymous"></script>

</body>
</html>