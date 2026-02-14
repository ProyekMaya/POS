<?php
$c = mysqli_connect('localhost', 'root', '', 'kasir');

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// Login
if (isset($_POST['login'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Perhatikan: $loginType ditentukan dari GET parameter di URL form action
    // Gunakan 'admin' sebagai default jika tidak ada type
    $loginType = isset($_GET['type']) ? $_GET['type'] : 'admin';

    $isLoggedIn = false; // Flag untuk melacak status login
    $role = '';          // Akan menyimpan 'admin', 'kasir', atau 'owner'
    $loggedInId = null;  // Akan menyimpan ID pengguna yang berhasil login
    $loggedInUsername = ''; // Akan menyimpan username/nama pengguna yang berhasil login

    $table_name = '';
    $id_column = '';
    $username_db_column = '';

    // Tentukan tabel dan kolom berdasarkan loginType
    if ($loginType === 'admin') {
        $table_name = 'user';
        $id_column = 'iduser';
        $username_db_column = 'username';
    } elseif ($loginType === 'kasir') {
        $table_name = 'datakasir';
        $id_column = 'idkasir';
        $username_db_column = 'username'; // Sesuai skema database Anda
    } elseif ($loginType === 'owner') {
        $table_name = 'dataowner';
        $id_column = 'idowner';
        $username_db_column = 'username'; // Sesuai skema database Anda (bukan 'namaowner')
    } else {
        // Tipe login tidak valid, bisa arahkan kembali atau tampilkan error
        echo '
        <script>
            alert("Tipe login tidak valid!");
            window.location.href = "login.php"; // Redirect ke halaman login default
        </script>
        ';
        exit();
    }

    // Jika table_name berhasil ditentukan
    if (!empty($table_name)) {
        // Gunakan prepared statement untuk keamanan
        $stmt = $c->prepare("SELECT {$id_column}, {$username_db_column}, password FROM {$table_name} WHERE {$username_db_column} = ?");
        $stmt->bind_param("s", $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data_user = $result->fetch_assoc();
            $hashed_password_from_db = $data_user['password']; // Password dari database

            // PENTING: SANGAT DISARANKAN untuk menggunakan password_verify()
            // Untuk ini, password di DB harus di-hash menggunakan password_hash() saat pendaftaran/update
            // Contoh penggunaan: password_verify($password_input, $hashed_password_from_db)

            // Saat ini menggunakan perbandingan plain text sesuai kode Anda:
            if ($password_input === $hashed_password_from_db) {
            // Jika Anda sudah mengimplementasikan hashing password, gunakan ini:
            // if (password_verify($password_input, $hashed_password_from_db)) {
                $isLoggedIn = true;
                $role = $loginType; // Role langsung diambil dari loginType
                $loggedInId = $data_user[$id_column];
                $loggedInUsername = $data_user[$username_db_column];
            }
        }
        $stmt->close(); // Tutup statement
    }

    if ($isLoggedIn) {
        // Login berhasil, set session
        $_SESSION['login'] = 'True';
        $_SESSION['role'] = $role;

        // Reset semua sesi peran lainnya untuk menghindari konflik
        unset($_SESSION['iduser_admin']);
        unset($_SESSION['username_admin']);
        unset($_SESSION['idkasir_kasir']);
        unset($_SESSION['username_kasir']);
        unset($_SESSION['idowner_owner']); // Tambahkan unset untuk owner
        unset($_SESSION['username_owner']); // Tambahkan unset untuk owner

        // Set ID dan username/nama sesuai dengan role yang berhasil login
        if ($role === 'admin') {
            $_SESSION['iduser_admin'] = $loggedInId;
            $_SESSION['username_admin'] = $loggedInUsername;
            header('location:admin/index_admin.php');
            exit();
        } elseif ($role === 'kasir') {
            $_SESSION['idkasir_kasir'] = $loggedInId;
            $_SESSION['username_kasir'] = $loggedInUsername;
            header('location:index.php'); // Atau sesuaikan dengan halaman dashboard kasir
            exit();
        } elseif ($role === 'owner') { // Tambahkan blok untuk Owner
            $_SESSION['idowner_owner'] = $loggedInId;
            $_SESSION['username_owner'] = $loggedInUsername; // Menggunakan 'username' dari tabel dataowner
            header('location:owner/index_owner.php'); // Arahkan ke dashboard Owner
            exit();
        }
    } else {
        // Gagal login
        echo '
        <script>
            alert("Username atau Password salah");
            window.location.href = "../login.php?type=' . htmlspecialchars($loginType) . '";
        </script>
        ';
    }
}

// Tambah Barang Owner
if (isset($_POST['tambahbarangowner'])) {
    $namaproduk = $_POST['namaproduk'];
    $isi_pcs_per_ctn_baru = (int)$_POST['isi_pcs_per_ctn'];
    $stock_baru = (int)$_POST['stock'];
    $hargamodal_baru = $_POST['hargamodal'];
    $hargajual_baru = $_POST['hargajual'];

    // Validasi: Pastikan nama produk tidak kosong
    if (empty($namaproduk)) {
        echo '
        <script>
            alert("Nama produk tidak boleh kosong.");
            window.location.href = "stock_owner.php", ;
        </script>
        ';
        exit;
    }

    // Hitung Jumlah Pcs per Dus/Ctn
    $total_pcs_dari_dus_ctn = $jumlah_dus_ctn_baru * $isi_pcs_per_ctn_baru;
    $hitung_final_stock = $stock_baru + $total_pcs_dari_dus_ctn;
    $jumlah_final_stock = $hitung_final_stock;
    
    // Sanitasi input untuk mencegah SQL Injection
    $namaproduk_escaped = mysqli_real_escape_string($c, $namaproduk);
    $isi_pcs_per_ctn_baru_escaped = mysqli_real_escape_string($c, $isi_pcs_per_ctn_baru);
    $jumlah_final_stock_escaped = mysqli_real_escape_string($c, $jumlah_final_stock);
    $hargamodal_baru_escaped = mysqli_real_escape_string($c, $hargamodal_baru);
    $hargajual_baru_escaped = mysqli_real_escape_string($c, $hargajual_baru);

    // Pengecekan duplikasi nama produk dan ambil data yang sudah ada (terutama stock)
    $cek_produk = mysqli_query($c, "SELECT stock FROM produk WHERE namaproduk = '$namaproduk_escaped'");
    $hitung_produk = mysqli_num_rows($cek_produk);

    if ($hitung_produk > 0) {
        // Produk sudah ada, ambil stok lama untuk ditambahkan
        $data_produk = mysqli_fetch_assoc($cek_produk);
        $stock_lama = $data_produk['stock'];
        $stock_total_terbaru = $stock_lama + $jumlah_final_stock_escaped;

        $update = mysqli_query($c, "UPDATE produk SET
                                 hargamodal = '$hargamodal_baru_escaped',
                                 hargajual = '$hargajual_baru_escaped',
                                 isi_pcs_per_ctn = '$isi_pcs_per_ctn_baru_escaped',
                                 stock = '$stock_total_terbaru'
                                 WHERE namaproduk = '$namaproduk_escaped'");

        if ($update) {
            echo '
            <script>
                alert("Produk dengan nama \'' . $namaproduk . '\' sudah ada. Stok berhasil ditambahkan menjadi ' . $stock_total_terbaru . ', Isi Pcs per Dus/Ctn, Harga Modal, dan Harga Jual berhasil diupdate.");
                window.location.href = "stock_owner.php";
            </script>
            ';
        } else {
            // Tampilkan error MySQL jika update gagal (opsional, untuk debugging)
            echo '
            <script>
                alert("Gagal mengupdate produk: ' . mysqli_error($c) . '");
                window.location.href = "stock_owner.php";
            </script>
            ';
        }
    } else {
        // Produk belum ada, lakukan INSERT
        $insert = mysqli_query($c, "INSERT INTO produk (namaproduk, hargamodal, hargajual, isi_pcs_per_ctn, stock)
                                 VALUES ('$namaproduk_escaped', '$hargamodal_baru_escaped', '$hargajual_baru_escaped', '$isi_pcs_per_ctn_baru_escaped', '$jumlah_final_stock_escaped')");

        if ($insert) {
            echo '
            <script>
                alert("Barang berhasil ditambahkan.");
                window.location.href = "stock_owner.php";
            </script>
            ';
        } else {
            // Tampilkan error MySQL jika insert gagal (opsional, untuk debugging)
            echo '
            <script>
                alert("Gagal menambah barang: ' . mysqli_error($c) . '");
                window.location.href = "stock_owner.php";
            </script>
            ';
        }
    }
}

// Edit Barang Owner
if (isset($_POST['editbarangowner'])) {
    $np = $_POST['namaproduk'];
    $stock = $_POST['stock'];
    $isi_pcs_per_ctn = $_POST['isi_pcs_per_ctn'];
    $hargamodal = $_POST['hargamodal'];
    $hargajual = $_POST['hargajual'];
    $idp = $_POST['idp'];

    // Validasi: Pastikan nama produk tidak kosong
    if (empty($np)) {
        echo '
        <script>
            alert("Nama produk tidak boleh kosong.");
            window.location.href = "stock_owner.php";
        </script>
        ';
        exit;
    }

    // Sanitasi input untuk mencegah SQL Injection
    $np_escaped = mysqli_real_escape_string($c, $np);
    $stock_escaped = mysqli_real_escape_string($c, $stock);
    $isi_pcs_per_ctn_escaped = mysqli_real_escape_string($c, $isi_pcs_per_ctn);
    $hargamodal_escaped = mysqli_real_escape_string($c, $hargamodal);
    $hargajual_escaped = mysqli_real_escape_string($c, $hargajual);
    $idp_escaped = mysqli_real_escape_string($c, $idp);

    // Pengecekan duplikasi nama produk saat edit, kecuali untuk produk itu sendiri
    $cek_produk_edit = mysqli_query($c, "SELECT * FROM produk WHERE namaproduk = '$np_escaped' AND idproduk != '$idp_escaped'");
    $hitung_produk_edit = mysqli_num_rows($cek_produk_edit);

    if ($hitung_produk_edit > 0) {
        echo '
        <script>
            alert("Produk dengan nama \'' . $np . '\' sudah ada. Silakan gunakan nama lain.");
            window.location.href = "stock_owner.php";
        </script>
        ';
    } else {
        $query = mysqli_query($c, "UPDATE produk SET namaproduk='$np_escaped', stock='$stock_escaped', isi_pcs_per_ctn='$isi_pcs_per_ctn_escaped', hargamodal='$hargamodal_escaped', hargajual='$hargajual_escaped' WHERE idproduk='$idp_escaped' ");

        if ($query) {
            echo '
            <script>
                alert("Data barang berhasil diupdate.");
                window.location.href = "stock_owner.php";
            </script>
            ';
        } else {
            echo '
            <script>
                alert("Gagal mengupdate barang: ' . mysqli_error($c) . '");
                window.location.href = "stock_owner.php";
            </script>
            ';
        }
    }
}

// Hapus Barang Owner
if (isset($_POST['hapusbarangowner'])) {
    $idp = $_POST['idp'];

    // Sanitasi input
    $idp_escaped = mysqli_real_escape_string($c, $idp);

    $query = mysqli_query($c, "DELETE FROM produk WHERE idproduk='$idp_escaped'");

    if ($query) {
        echo '
        <script>
            alert("Barang berhasil dihapus.");
            window.location.href = "stock_owner.php";
        </script>
        ';
    } else {
        echo '
        <script>
            alert("Gagal menghapus barang: ' . mysqli_error($c) . '");
            window.location.href = "stock_owner.php";
        </script>
        ';
    }
}

// Menambah Pembelian Baru
if (isset($_POST['tambahpembelian'])) {
    $idowner_session = $_SESSION['idowner_owner']; // (1) Owner ID
    $idsupplier_input = isset($_POST['idsupplier']) ? (int)$_POST['idsupplier'] : null;

    if ($idsupplier_input === null || $idsupplier_input === 0) {
        // Gagal jika ID Supplier tidak valid
        echo '<script>alert("Supplier tidak valid. Harap pilih supplier."); window.location.href="pembelian_owner.php";</script>';
        exit();
    }

    // 1. Ambil Nama dan Alamat Supplier berdasarkan ID
    $namasupplier = '';
    $alamatsupplier = '';
    
    $stmt_get_supplier_info = mysqli_prepare($c, "SELECT nama_supplier, alamat_supplier FROM supplier WHERE idsupplier = ?");
    if ($stmt_get_supplier_info) {
        mysqli_stmt_bind_param($stmt_get_supplier_info, "i", $idsupplier_input);
        mysqli_stmt_execute($stmt_get_supplier_info);
        $result_supplier_info = mysqli_stmt_get_result($stmt_get_supplier_info);
        
        if ($row_supplier = mysqli_fetch_assoc($result_supplier_info)) {
            $namasupplier = $row_supplier['nama_supplier']; // (2)
            $alamatsupplier = $row_supplier['alamat_supplier']; // (3)
        }
        mysqli_stmt_close($stmt_get_supplier_info);
    }

    if (empty($namasupplier)) {
        // Jika data supplier tidak ditemukan
        echo '<script>alert("Data supplier tidak ditemukan."); window.location.href="pembelian_owner.php";</script>';
        exit();
    }

    // 2. Mulai transaksi dan masukkan data ke tabel pembelian
    mysqli_begin_transaction($c);
    try {
        // Query INSERT ke tabel pembelian dengan MENYIMPAN idowner dan idsupplier
        $stmt_insert_pembelian = mysqli_prepare($c, "INSERT INTO pembelian (idowner, tanggal, namasupplier, alamatsupplier, idsupplier) VALUES (?, NOW(), ?, ?, ?)");
        
        if (!$stmt_insert_pembelian) {
            throw new Exception("Gagal menyiapkan statement INSERT pembelian: " . mysqli_error($c));
        }

        // KOREKSI UTAMA ADA DISINI: Tipe data binding diubah dari "isssi" menjadi "issi"
        // 'i' untuk idowner, 's' untuk namasupplier, 's' untuk alamatsupplier, 'i' untuk idsupplier
        mysqli_stmt_bind_param($stmt_insert_pembelian, "issi", $idowner_session, $namasupplier, $alamatsupplier, $idsupplier_input);
        
        $insert_success = mysqli_stmt_execute($stmt_insert_pembelian);
        
        if (!$insert_success) {
            throw new Exception("Gagal mengeksekusi INSERT pembelian baru.");
        }
        mysqli_stmt_close($stmt_insert_pembelian);
        
        // 3. Ambil ID pembelian yang baru dibuat
        $new_idpembelian = mysqli_insert_id($c);

        mysqli_commit($c); 

        echo '
        <script>
            alert("Pembelian baru berhasil ditambahkan!");
            window.location.href="pembelian_owner.php"; 
        </script>';
        exit(); 

    } catch (Exception $e) {
        mysqli_rollback($c); 
        error_log("Gagal menambah pembelian baru. Error: " . $e->getMessage());
        echo '
        <script>
            alert("Gagal menambah pembelian baru: ' . $e->getMessage() . '");
            window.location.href="pembelian_owner.php"; 
        </script>
        ';
        exit();
    }
}

    // Logika untuk menghapus pembelian
    if (isset($_POST['hapuspembelian'])) {
        $idpembelian_to_delete = $_POST['idpembelian_del'];

        // Start transaction
        mysqli_begin_transaction($c);

        try {
            // Delete associated detailpembelian first
            $delete_detail = mysqli_query($c, "DELETE FROM detailpembelian WHERE idpembelian='$idpembelian_to_delete'");
            if (!$delete_detail) {
                throw new Exception(mysqli_error($c));
            }

            // Then delete the purchase itself
            $delete_pembelian = mysqli_query($c, "DELETE FROM pembelian WHERE idpembelian='$idpembelian_to_delete'");
            if (!$delete_pembelian) {
                throw new Exception(mysqli_error($c));
            }

            mysqli_commit($c); // Commit transaction
            echo '<script>alert("Pembelian berhasil dihapus."); window.location.href="pembelian_owner.php";</script>';
        } catch (Exception $e) {
            mysqli_rollback($c); // Rollback transaction on error
            echo '<script>alert("Gagal menghapus pembelian: ' . $e->getMessage() . '"); window.location.href="pembelian_owner.php";</script>';
        }
    }

    // Menambah Produk ke Detail Pembelian
    if (isset($_POST['tambahprodukpembelian'])) {
        $idp = $_POST['idp']; // idpembelian
        $idpr = (int)$_POST['idproduk'];
        $harga_supplier = (int)$_POST['harga_supplier'];
        
        // Input jumlah dalam Pcs dan Ctn/Dus dari form modal
        $jumlah_pcs_input = (int)$_POST['stock']; // Disesuaikan dengan nama input: stock -> jumlah_pcs_input
        $jumlah_ctn_dus_input = (int)$_POST['jumlah_ctn_dus'];

        // 1. Ambil isi_pcs_per_ctn dari tabel produk
        $get_isi_pcs_q = mysqli_query($c, "SELECT isi_pcs_per_ctn FROM produk WHERE idproduk = '$idpr'");
        if (mysqli_num_rows($get_isi_pcs_q) == 0) {
            echo '<script>alert("ID Produk tidak valid."); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            exit();
        }
        $produk_data = mysqli_fetch_array($get_isi_pcs_q);
        $isi_pcs_per_ctn = (int)$produk_data['isi_pcs_per_ctn'];

        // 2. Hitung Total Jumlah Pcs Final
        if ($isi_pcs_per_ctn > 0) {
            $total_jumlah_final = $jumlah_pcs_input + ($jumlah_ctn_dus_input * $isi_pcs_per_ctn);
        } else {
            // Jika isi_pcs_per_ctn = 0, hanya ambil dari input Pcs
            $total_jumlah_final = $jumlah_pcs_input;
        }
        
        // Validasi minimal jumlah (Opsional: pastikan setidaknya ada 1 Pcs atau Ctn)
        if ($total_jumlah_final <= 0) {
            echo '<script>alert("Jumlah produk (Pcs atau Ctn) harus lebih dari 0."); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            exit();
        }
        
        // Validasi Harga Modal (Harga Supplier)
        if ($harga_supplier <= 0) {
            echo '<script>alert("Harga dari Supplier (Harga Modal) harus lebih dari 0."); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            exit();
        }


        // 3. Cek apakah produk sudah ada di detailpembelian ini (seharusnya tidak, karena JS sudah filter)
        // Walaupun JS sudah filter, cek keamanan di sisi server
        $cek_duplikasi_q = mysqli_query($c, "SELECT iddetailpembelian FROM detailpembelian WHERE idpembelian='$idp' AND idproduk='$idpr'");
        if (mysqli_num_rows($cek_duplikasi_q) > 0) {
            echo '<script>alert("Produk ini sudah ditambahkan ke pembelian ini. Gunakan fitur Edit."); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            exit();
        }

        // --- START TRANSACTION ---
        mysqli_begin_transaction($c);
        try {
            
            // 4. Masukkan ke detailpembelian (Hanya idpembelian, idproduk, dan jumlah yang dibutuhkan)
            $insert_detail = mysqli_query($c, "INSERT INTO detailpembelian (idpembelian, idproduk, jumlah) 
                                            VALUES ('$idp', '$idpr', '$total_jumlah_final')");
                                            
            if (!$insert_detail) {
                throw new Exception("Gagal insert ke detailpembelian: " . mysqli_error($c));
            }

            // 5. Update stok produk di tabel produk: STOK = STOK + Jumlah Baru
            $update_stok = mysqli_query($c, "UPDATE produk SET stock = stock + '$total_jumlah_final' WHERE idproduk='$idpr'");
            
            if (!$update_stok) {
                throw new Exception("Gagal update stok produk: " . mysqli_error($c));
            }
            
            // 6. Update Harga Modal produk di tabel produk: Harga Modal = Harga Supplier
            // Note: Harga Modal di produk diupdate setiap ada pembelian dengan harga baru dari supplier
            $update_hargamodal = mysqli_query($c, "UPDATE produk SET hargamodal = '$harga_supplier' WHERE idproduk='$idpr'");
            
            if (!$update_hargamodal) {
                throw new Exception("Gagal update harga modal produk: " . mysqli_error($c));
            }

            // Commit jika semua berhasil
            mysqli_commit($c);
            
            echo '<script>alert("Produk berhasil ditambahkan ke pembelian dengan jumlah ' . $total_jumlah_final . ' Pcs."); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            
        } catch (Exception $e) {
            // Rollback jika ada yang gagal
            mysqli_rollback($c);
            error_log("Gagal tambah produk pembelian. Error: " . $e->getMessage());
            echo '<script>alert("Transaksi gagal: ' . $e->getMessage() . '"); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
        }
    }
    
    // Edit Produk Pembelian
    if (isset($_POST['ubahprodukpembelian'])) {
        $iddp = $_POST['iddp'];
        $idp = $_POST['idp']; // idpembelian
        $idpr = $_POST['idpr'];
        
        // Input baru dari form
        $jumlah_pcs_edit = (int)$_POST['jumlah_pcs_edit'];
        $jumlah_ctn_dus_edit = (int)$_POST['jumlah_ctn_dus_edit'];
        $isi_pcs_per_ctn = (int)$_POST['isi_pcs_per_ctn'];
        $hargamodal_edit = (int)$_POST['hargamodal_edit'];

        // 1. Hitung TOTAL JUMLAH PCS BARU
        $jumlah_baru = ($jumlah_ctn_dus_edit * $isi_pcs_per_ctn) + $jumlah_pcs_edit;

        // Ambil jumlah lama dari detailpembelian
        $get_old_jumlah = mysqli_query($c, "SELECT jumlah FROM detailpembelian WHERE iddetailpembelian='$iddp'");
        $old_data = mysqli_fetch_array($get_old_jumlah);
        $jumlah_lama = $old_data['jumlah'];

        // 2. Hitung selisih jumlah (Baru - Lama)
        $selisih_jumlah = $jumlah_baru - $jumlah_lama;

        // 3. Update jumlah di detailpembelian
        $update_detail = mysqli_query($c, "UPDATE detailpembelian SET jumlah='$jumlah_baru' WHERE iddetailpembelian='$iddp'");

        if ($update_detail) {
            // 4. Update stok produk (tambah stok jika selisih positif, kurangi jika selisih negatif)
            $update_stok_produk = mysqli_query($c, "UPDATE produk SET stock = stock + '$selisih_jumlah' WHERE idproduk='$idpr'");
            $update_hargamodal_produk = mysqli_query($c, "UPDATE produk SET hargamodal = '$hargamodal_edit' WHERE idproduk='$idpr'");
            
            if ($update_stok_produk && $update_hargamodal_produk) {
                echo '<script>alert("Detail pembelian dan Harga Modal berhasil diubah."); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            } else {
                // Walaupun update detail berhasil, jika update stok gagal, tampilkan error
                echo '<script>alert("Gagal update stok produk: ' . mysqli_error($c) . '"); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            }
        } else {
            echo '<script>alert("Gagal mengubah detail pembelian: ' . mysqli_error($c) . '"); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
        }
    }

    // Hapus Produk Pembelian
    if (isset($_POST['hapusprodukpembelian'])) {
        $iddp = $_POST['iddp'];
        $idpr = $_POST['idpr'];
        $idp = $_POST['idpembelian']; // Menggunakan idpembelian dari form

        // Ambil jumlah produk yang akan dihapus dari detailpembelian
        $get_jumlah_hapus = mysqli_query($c, "SELECT jumlah FROM detailpembelian WHERE iddetailpembelian='$iddp'");
        $data_hapus = mysqli_fetch_array($get_jumlah_hapus);
        $jumlah_hapus = $data_hapus['jumlah'];

        // Hapus item dari detailpembelian
        $delete_item = mysqli_query($c, "DELETE FROM detailpembelian WHERE iddetailpembelian='$iddp'");

        if ($delete_item) {
            // Kurangi stok produk (karena item dihapus dari pembelian, stok harus dikembalikan)
            $update_stok_kembali = mysqli_query($c, "UPDATE produk SET stock = stock - '$jumlah_hapus' WHERE idproduk='$idpr'");
            if ($update_stok_kembali) {
                echo '<script>alert("Produk berhasil dihapus dari pembelian."); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            } else {
                echo '<script>alert("Gagal mengurangi stok produk: ' . mysqli_error($c) . '"); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
            }
        } else {
            echo '<script>alert("Gagal menghapus produk dari pembelian: ' . mysqli_error($c) . '"); window.location.href="view_pembelian.php?idp=' . $idp . '";</script>';
        }
    }

// Menambah Supplier Baru
if (isset($_POST['tambahsupplier'])) {
    $nama_supplier = $_POST['nama_supplier'];
    $notelepon_supplier = $_POST['notelepon_supplier'];
    $alamat_supplier = $_POST['alamat_supplier'];
    $tanggal_input = date('Y-m-d'); // Tanggal saat ini

    mysqli_begin_transaction($c);
    try {
        $stmt_insert_supplier = mysqli_prepare($c, "INSERT INTO supplier (tanggal, nama_supplier, no_telp, alamat_supplier) VALUES (?, ?, ?, ?)");
        
        // ssss = 4 string parameters
        if (!mysqli_stmt_bind_param($stmt_insert_supplier, "ssss", $tanggal_input, $nama_supplier, $notelepon_supplier, $alamat_supplier)) {
            throw new Exception("Gagal bind parameter.");
        }

        if (!mysqli_stmt_execute($stmt_insert_supplier)) {
            throw new Exception("Gagal mengeksekusi INSERT supplier.");
        }

        mysqli_stmt_close($stmt_insert_supplier);
        mysqli_commit($c);

        echo '
        <script>
            alert("Supplier baru berhasil ditambahkan!");
            window.location.href="supplier_owner.php";
        </script>
        ';
        exit();

    } catch (Exception $e) {
        mysqli_rollback($c);
        error_log("Gagal menambah supplier. Error: " . $e->getMessage());
        echo '
        <script>
            alert("Gagal menambah supplier: ' . $e->getMessage() . '");
            window.location.href="supplier_owner.php";
        </script>
        ';
        exit();
    }
}

// Mengubah Supplier
if (isset($_POST['editsupplier'])) {
    $idsupplier_edit = (int)$_POST['idsupplier_edit'];
    $nama_supplier = $_POST['nama_supplier'];
    $notelepon_supplier = $_POST['notelepon_supplier'];
    $alamat_supplier = $_POST['alamat_supplier'];

    mysqli_begin_transaction($c);
    try {
        $stmt_update_supplier = mysqli_prepare($c, "UPDATE supplier SET nama_supplier = ?, no_telp = ?, alamat_supplier = ? WHERE idsupplier = ?");
        
        // sssi = 3 string parameters + 1 integer parameter
        if (!mysqli_stmt_bind_param($stmt_update_supplier, "sssi", $nama_supplier, $notelepon_supplier, $alamat_supplier, $idsupplier_edit)) {
            throw new Exception("Gagal bind parameter.");
        }

        if (!mysqli_stmt_execute($stmt_update_supplier)) {
            throw new Exception("Gagal mengeksekusi UPDATE supplier.");
        }

        mysqli_stmt_close($stmt_update_supplier);
        mysqli_commit($c);

        echo '
        <script>
            alert("Data Supplier berhasil diperbarui!");
            window.location.href="supplier_owner.php";
        </script>
        ';
        exit();

    } catch (Exception $e) {
        mysqli_rollback($c);
        error_log("Gagal mengedit supplier. Error: " . $e->getMessage());
        echo '
        <script>
            alert("Gagal mengedit supplier: ' . $e->getMessage() . '");
            window.location.href="supplier_owner.php";
        </script>
        ';
        exit();
    }
}

// Menghapus Supplier
if (isset($_POST['hapussupplier'])) {
    $idsupplier_del = (int)$_POST['idsupplier_del'];

    mysqli_begin_transaction($c);
    try {
        // Hapus data supplier
        $stmt_delete_supplier = mysqli_prepare($c, "DELETE FROM supplier WHERE idsupplier=?");
        
        if (!mysqli_stmt_bind_param($stmt_delete_supplier, "i", $idsupplier_del)) {
            throw new Exception("Gagal bind parameter.");
        }

        if (!mysqli_stmt_execute($stmt_delete_supplier)) {
            throw new Exception("Gagal mengeksekusi DELETE supplier.");
        }

        mysqli_stmt_close($stmt_delete_supplier);
        mysqli_commit($c);

        echo '
        <script>
            alert("Supplier berhasil dihapus!");
            window.location.href="supplier_owner.php";
        </script>
        ';
        exit();

    } catch (Exception $e) {
        mysqli_rollback($c);
        error_log("Gagal menghapus supplier. Error: " . $e->getMessage());
        echo '
        <script>
            alert("Gagal menghapus supplier: ' . $e->getMessage() . '");
            window.location.href="supplier_owner.php";
        </script>
        ';
        exit();
    }
}

?>