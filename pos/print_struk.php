<?php
    require 'ceklogin.php';

    // Ambil nama kasir dari sesi login saat ini
    $nama_kasir = "Kasir Default";
    if (isset($_SESSION['username'])) {
        $nama_kasir = $_SESSION['username'];
    } else {
        if (isset($_SESSION['iduser'])) {
            $id_user_login = $_SESSION['iduser'];
            $ambil_username_login = mysqli_query($c, "SELECT username FROM user WHERE iduser = '$id_user_login'");
            $data_login = mysqli_fetch_array($ambil_username_login);
            if ($data_login) {
                $nama_kasir = $data_login['username'];
            }
        }
    }

    if (isset($_GET['idp'])) {
        $idp = $_GET['idp'];

        // Query untuk mengambil detail penjualan dan kasir
        $ambilnamakasir = mysqli_query($c,"SELECT p.*, pl.username FROM penjualan p JOIN datakasir pl ON p.idkasir=pl.idkasir WHERE p.idpenjualan='$idp'");
        $nk = mysqli_fetch_array($ambilnamakasir);

        if ($nk) {
            $namakasir = $nk['username'];
            $tanggal_penjualan = $nk['tanggal'];
            $jumlah_bayar = isset($nk['jumlah_bayar']) ? $nk['jumlah_bayar'] : 0;
            $kembalian = isset($nk['kembalian']) ? $nk['kembalian'] : 0;

        } else {
            echo "Data penjualan tidak ditemukan untuk ID: " . htmlspecialchars($idp);
            exit();
        }

    } else {
        echo "ID penjualan tidak ditemukan.";
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Belanja - Toko Mamah Azis</title>
    <style>
        body {
            font-family: 'Consolas', 'Monospace', sans-serif;
            font-size: 12px;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 10px;
        }
        .item-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .item-list th, .item-list td {
            text-align: left;
            padding: 2px 0;
        }
        .item-list td:nth-child(2) {
            text-align: right;
        }
        .item-list td:nth-child(3) {
            text-align: center;
        }
        .item-list td:nth-child(4) {
            text-align: right;
        }
        .total-section p {
            margin: 2px 0;
            text-align: right;
        }
        .total-section p strong {
            display: inline-block;
            width: 100px;
        }
        .thank-you {
            text-align: center;
            margin-top: 20px;
        }
        hr {
            border: 0;
            border-top: 1px dashed #ccc;
            margin: 5px 0;
        }
        @media print {
            body {
                width: auto;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>TOKO MAMAH AZIS</h3>
        <p>Jl. Raya Ciomas No. 123<br>Ciomas, Bogor, Jawa Barat</p>
        <p>Telp: 0812-3456-7890</p>
        <hr>
    </div>

    <div class="info">
        <p>No. penjualan: <strong><?= htmlspecialchars($idp); ?></strong></p>
        <p>Tanggal: <strong><?= htmlspecialchars(date('d-m-Y H:i', strtotime($tanggal_penjualan))); ?></strong></p>
        <p>kasir: <strong><?= htmlspecialchars($namakasir); ?></strong></p>
        <!-- <p>Kasir: <strong><?= htmlspecialchars($nama_kasir); ?></strong></p> <hr> -->
    </div>

    <div class="item-list">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $get_detail = mysqli_query($c, "SELECT dp.jumlah, pr.namaproduk, pr.hargajual FROM detailpenjualan dp JOIN produk pr ON dp.idproduk=pr.idproduk WHERE dp.idpenjualan='$idp'");
                $total_belanja = 0;
                while ($item = mysqli_fetch_array($get_detail)) {
                    $item_namaproduk = $item['namaproduk'];
                    $item_hargajual = $item['hargajual'];
                    $item_jumlah = $item['jumlah'];
                    $item_subtotal = $item_jumlah * $item_hargajual;
                    $total_belanja += $item_subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item_namaproduk); ?></td>
                    <td><?= number_format($item_hargajual); ?></td>
                    <td><?= $item_jumlah; ?></td>
                    <td><?= number_format($item_subtotal); ?></td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <hr>
    </div>

    <div class="total-section">
        <p>Total Belanja: <strong>Rp. <?= number_format($total_belanja); ?></strong></p>
        <p>CASH: <strong>Rp. <?= number_format($jumlah_bayar); ?></strong></p>
        <p>Kembalian: <strong>Rp. <?= number_format($kembalian); ?></strong></p>
    </div>

    <div class="footer">
        <p class="thank-you">Terima Kasih Atas Kunjungan Anda!</p>
        <p>-- Toko Mamah Azis --</p>
    </div>

</body>
</html>