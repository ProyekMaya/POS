-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 12:14 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kasir`
--

-- --------------------------------------------------------

--
-- Table structure for table `datakasir`
--

CREATE TABLE `datakasir` (
  `idkasir` int(11) NOT NULL,
  `namakasir` varchar(50) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `notelp` varchar(13) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `datakasir`
--

INSERT INTO `datakasir` (`idkasir`, `namakasir`, `username`, `password`, `notelp`, `alamat`) VALUES
(1, 'nurmalia', 'nurmalia', '12345', '0987654321', 'Bogor'),
(9, 'maya', 'maya', '12345', '085678901245', 'Bogor');

-- --------------------------------------------------------

--
-- Table structure for table `dataowner`
--

CREATE TABLE `dataowner` (
  `idowner` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dataowner`
--

INSERT INTO `dataowner` (`idowner`, `username`, `password`) VALUES
(1, 'mamah azis', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `detailpembelian`
--

CREATE TABLE `detailpembelian` (
  `iddetailpembelian` int(11) NOT NULL,
  `idpembelian` int(11) NOT NULL,
  `idproduk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detailpembelian`
--

INSERT INTO `detailpembelian` (`iddetailpembelian`, `idpembelian`, `idproduk`, `jumlah`) VALUES
(15, 2025000002, 202, 2);

-- --------------------------------------------------------

--
-- Table structure for table `detailpenjualan`
--

CREATE TABLE `detailpenjualan` (
  `iddetailpenjualan` int(11) NOT NULL,
  `idpenjualan` int(11) NOT NULL,
  `idproduk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detailpenjualan`
--

INSERT INTO `detailpenjualan` (`iddetailpenjualan`, `idpenjualan`, `idproduk`, `jumlah`) VALUES
(126, 2025000086, 14, 2),
(127, 2025000086, 13, 2),
(128, 2025000086, 173, 1),
(129, 2025000086, 65, 1),
(130, 2025000086, 37, 1),
(132, 2025000089, 65, 2),
(134, 2025000091, 65, 3);

-- --------------------------------------------------------

--
-- Table structure for table `pembelian`
--

CREATE TABLE `pembelian` (
  `idpembelian` int(11) NOT NULL,
  `idowner` int(11) NOT NULL,
  `idsupplier` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `namasupplier` varchar(255) NOT NULL,
  `alamatsupplier` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembelian`
--

INSERT INTO `pembelian` (`idpembelian`, `idowner`, `idsupplier`, `tanggal`, `namasupplier`, `alamatsupplier`) VALUES
(2025000001, 1, 2025000001, '2025-11-16 15:17:07', 'Jaya Abadi', 'Sukabumi'),
(2025000002, 1, 2025000002, '2025-11-17 17:47:03', 'Sentosa Abadi', 'Jonggol');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `idpenjualan` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `idkasir` int(11) NOT NULL,
  `jumlah_bayar` decimal(10,2) DEFAULT 0.00,
  `kembalian` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`idpenjualan`, `tanggal`, `idkasir`, `jumlah_bayar`, `kembalian`) VALUES
(2025000086, '2025-08-09 01:58:34', 9, 26000.00, 0.00),
(2025000089, '2025-08-11 08:24:11', 9, 6000.00, 0.00),
(2025000091, '2025-08-12 03:03:19', 9, 9000.00, 0.00),
(2025000092, '2025-11-16 07:40:28', 9, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `idproduk` int(11) NOT NULL,
  `namaproduk` varchar(20) NOT NULL,
  `hargajual` int(11) NOT NULL,
  `hargamodal` int(11) NOT NULL,
  `isi_pcs_per_ctn` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `satuan` varchar(20) NOT NULL DEFAULT 'Pcs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`idproduk`, `namaproduk`, `hargajual`, `hargamodal`, `isi_pcs_per_ctn`, `stock`, `updated_at`, `satuan`) VALUES
(13, 'Indomie Soto', 3000, 2100, 40, 52, '2025-11-16 14:12:35', 'Pcs'),
(14, 'Indomie Goreng', 3500, 2700, 40, 49, '2025-10-07 15:27:32', 'Pcs'),
(37, 'Gula Pasir 1/4 kg', 4500, 4000, 10, 184, '2025-08-12 09:54:34', 'Pcs'),
(65, 'Terigu biasa 1/4kg', 3000, 2500, 10, 20, '2025-08-12 10:03:14', 'Pcs'),
(66, 'Terigu Segitiga1/4kg', 3500, 3000, 10, 8, '2025-07-25 14:35:14', 'Pcs'),
(171, 'Beras 1 liter', 12000, 11000, 50, 60, '2025-07-29 19:00:39', 'Pcs'),
(173, 'Minyak Goreng 1/4 kg', 5500, 5000, 40, 19, '2025-08-09 08:55:38', 'Pcs'),
(174, 'Telur 1/4 kg', 7500, 6500, 4, 10, '2025-07-29 19:43:56', 'Pcs'),
(178, 'Telur 1 pcs', 2000, 1500, 16, 100, '2025-08-09 08:54:12', 'Pcs'),
(179, 'Rinso cair 45gr', 1000, 500, 6, 50, '2025-08-07 13:53:44', 'Pcs'),
(180, 'Rinso Bubuk 45gr', 1000, 500, 6, 50, '2025-08-07 13:53:36', 'Pcs'),
(181, 'Pewangi Downy 9ml', 500, 300, 12, 50, '2025-08-07 14:02:36', 'Pcs'),
(182, 'Pewangi Molto 9ml', 500, 300, 12, 50, '2025-08-07 14:01:19', 'Pcs'),
(183, 'Shampo Sunsilk ml', 500, 300, 12, 50, '2025-08-07 14:00:49', 'Pcs'),
(184, 'Shampo Dove 9ml', 5000, 3000, 12, 50, '2025-08-07 14:00:35', 'Pcs'),
(185, 'Mie sedap goreng', 3500, 3000, 40, 80, '2025-08-07 14:03:59', 'Pcs'),
(186, 'Mie Sedap Soto', 5000, 3000, 40, 80, '2025-08-07 14:04:24', 'Pcs'),
(187, 'Indomie rendang', 5000, 3000, 40, 90, '2025-08-07 14:13:59', 'Pcs'),
(188, 'Indomie bawang', 3000, 2500, 40, 80, '2025-08-07 14:13:09', 'Pcs'),
(190, 'Teh gelas 200ml', 1000, 500, 40, 80, '2025-08-07 14:24:55', 'Pcs'),
(191, 'Teh rio 200ml', 1000, 500, 40, 80, '2025-08-07 14:25:16', 'Pcs'),
(192, 'Floridina 240ml', 3000, 2500, 6, 20, '2025-08-07 14:26:10', 'Pcs'),
(193, 'Frisian Flag Putih 4', 2000, 1500, 6, 20, '2025-08-07 14:29:21', 'Pcs'),
(194, 'Frisian Flag Coklat ', 2000, 1500, 6, 20, '2025-08-07 14:29:42', 'Pcs'),
(195, 'Ultramilk coklat 125', 3000, 2500, 6, 20, '2025-08-07 14:30:51', 'Pcs'),
(196, 'Ultramilk fullcream ', 3000, 2500, 6, 20, '2025-08-07 14:31:16', 'Pcs'),
(197, 'Saos ABC 135ml', 500, 300, 6, 20, '2025-08-07 14:32:06', 'Pcs'),
(198, 'Kecap bangau 20ml', 1000, 500, 6, 20, '2025-08-07 14:34:16', 'Pcs'),
(199, 'Kecap Bangau 135ml', 3000, 2500, 20, 40, '2025-08-07 15:02:33', 'Pcs'),
(200, 'Racik Ayam 20gr', 2000, 1500, 6, 20, '2025-08-07 15:03:43', 'Pcs'),
(202, 'pulpen', 8, 6, 8, 10, '2025-11-17 18:13:17', 'Pcs');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `idsupplier` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `nama_supplier` varchar(255) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `alamat_supplier` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`idsupplier`, `tanggal`, `nama_supplier`, `no_telp`, `alamat_supplier`) VALUES
(2025000001, '2025-11-16', 'Jaya Abadi', '082298536454', 'Sukabumi'),
(2025000002, '2025-11-17', 'Sentosa Abadi', '082298536455', 'Jonggol');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `datakasir`
--
ALTER TABLE `datakasir`
  ADD PRIMARY KEY (`idkasir`);

--
-- Indexes for table `dataowner`
--
ALTER TABLE `dataowner`
  ADD PRIMARY KEY (`idowner`);

--
-- Indexes for table `detailpembelian`
--
ALTER TABLE `detailpembelian`
  ADD PRIMARY KEY (`iddetailpembelian`),
  ADD KEY `fk_detailpembelian_pembelian` (`idpembelian`);

--
-- Indexes for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD PRIMARY KEY (`iddetailpenjualan`),
  ADD KEY `fk_detailpenjualan_penjualan` (`idpenjualan`);

--
-- Indexes for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`idpembelian`),
  ADD UNIQUE KEY `idsupplier` (`idsupplier`),
  ADD KEY `fk_pembelian_dataowner` (`idowner`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`idpenjualan`),
  ADD KEY `fk_penjualan_datakasir` (`idkasir`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`idproduk`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`idsupplier`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `datakasir`
--
ALTER TABLE `datakasir`
  MODIFY `idkasir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `dataowner`
--
ALTER TABLE `dataowner`
  MODIFY `idowner` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detailpembelian`
--
ALTER TABLE `detailpembelian`
  MODIFY `iddetailpembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  MODIFY `iddetailpenjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `idpembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2025000003;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `idpenjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2025000093;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `idproduk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=203;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `idsupplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2025000003;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detailpembelian`
--
ALTER TABLE `detailpembelian`
  ADD CONSTRAINT `fk_detailpembelian_pembelian` FOREIGN KEY (`idpembelian`) REFERENCES `pembelian` (`idpembelian`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD CONSTRAINT `fk_detailpenjualan_penjualan` FOREIGN KEY (`idpenjualan`) REFERENCES `penjualan` (`idpenjualan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `fk_pembelian_dataowner` FOREIGN KEY (`idowner`) REFERENCES `dataowner` (`idowner`) ON UPDATE CASCADE;

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `fk_penjualan_datakasir` FOREIGN KEY (`idkasir`) REFERENCES `datakasir` (`idkasir`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
