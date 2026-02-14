<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$c = mysqli_connect('localhost', 'root', '', 'kasir');

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// Login Logic
if (isset($_POST['login'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    $isLoggedIn = false; // Flag untuk melacak status login
    $role = '';          // Akan menyimpan 'kasir', 'owner', atau 'admin'
    $loggedInId = null;  // Akan menyimpan ID pengguna yang berhasil login
    $loggedInUsername = ''; // Akan menyimpan username/nama pengguna yang berhasil login

    // Daftar tabel yang akan dicari beserta kolom username/nama dan ID-nya
    $user_tables = [
        'owner' => [
            'table_name' => 'dataowner',
            'id_column' => 'idowner',
            'username_db_column' => 'username', // Kolom username di tabel dataowner
            'dashboard' => 'owner/index_owner.php'
        ],
        'kasir' => [
            'table_name' => 'datakasir',
            'id_column' => 'idkasir',
            'username_db_column' => 'username', // Kolom username di tabel datakasir
            'dashboard' => 'index.php'
        ],
        'admin' => [ // Jika Anda masih ingin mendukung admin login dari form yang sama
            'table_name' => 'user',
            'id_column' => 'iduser',
            'username_db_column' => 'username',
            'dashboard' => 'admin/index_admin.php'
        ]
    ];

    foreach ($user_tables as $user_role => $table_info) {
        $table_name = $table_info['table_name'];
        $id_column = $table_info['id_column'];
        $username_db_column = $table_info['username_db_column'];

        // Gunakan prepared statement untuk keamanan
        $stmt = $c->prepare("SELECT {$id_column}, {$username_db_column}, password FROM {$table_name} WHERE {$username_db_column} = ?");
        
        // Periksa jika prepare() gagal
        if ($stmt === false) {
            error_log("Prepare statement failed for table {$table_name}: " . $c->error);
            continue; // Lanjutkan ke tabel berikutnya jika ada masalah
        }
        
        $stmt->bind_param("s", $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data_user = $result->fetch_assoc();
            $hashed_password_from_db = $data_user['password'];

            // PENTING: SANGAT DISARANKAN untuk menggunakan password_verify()
            // Untuk ini, password di DB harus di-hash menggunakan password_hash() saat pendaftaran/update
            // Contoh penggunaan: password_verify($password_input, $hashed_password_from_db)

            // Saat ini menggunakan perbandingan plain text sesuai kode Anda sebelumnya:
            if ($password_input === $hashed_password_from_db) {
            // Jika Anda sudah mengimplementasikan hashing password, gunakan ini:
            // if (password_verify($password_input, $hashed_password_from_db)) {
                $isLoggedIn = true;
                $role = $user_role;
                $loggedInId = $data_user[$id_column];
                $loggedInUsername = $data_user[$username_db_column];
                break; // Hentikan pencarian jika sudah ditemukan dan berhasil login
            }
        }
        $stmt->close(); // Tutup statement setelah selesai dengan tabel ini
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
        unset($_SESSION['idowner_owner']);
        unset($_SESSION['username_owner']);

        // Set ID dan username/nama sesuai dengan role yang berhasil login
        if ($role === 'admin') {
            $_SESSION['iduser_admin'] = $loggedInId;
            $_SESSION['username_admin'] = $loggedInUsername;
            header('location:admin/index_admin.php');
            exit();
        } elseif ($role === 'kasir') {
            $_SESSION['idkasir_kasir'] = $loggedInId;
            $_SESSION['username_kasir'] = $loggedInUsername;
            header('location:index.php'); // Dashboard Kasir
            exit();
        } elseif ($role === 'owner') {
            $_SESSION['idowner_owner'] = $loggedInId;
            $_SESSION['username_owner'] = $loggedInUsername; // Menggunakan 'username' dari tabel dataowner
            header('location:owner/index_owner.php'); // Dashboard Owner
            exit();
        }
    } else {
        // Gagal login
        echo '
        <script>
            alert("Username atau Password salah.");
            window.location.href = "login.php"; // Redirect kembali ke halaman login tanpa tipe
        </script>
        ';
    }
}


// Menambah penjualan baru
if (isset($_POST['tambahpenjualan'])) {
    // Ambil idkasir dari session, bukan dari POST (lebih aman)
    $idkasir = isset($_SESSION['idkasir_kasir']) ? $_SESSION['idkasir_kasir'] : null;

    if ($idkasir === null) {
        error_log("Gagal menambah penjualan baru. ID Kasir tidak tersedia di sesi.");
        echo '
        <script>
            alert("Gagal menambah penjualan baru: ID Kasir tidak ditemukan. Harap login kembali.");
            window.location.href="login.php?type=kasir";
        </script>
        ';
        exit();
    }

    // Mulai transaksi untuk memastikan konsistensi data
    mysqli_begin_transaction($c);
    $insert_success = false;
    $new_idpenjualan = '';

    try {
        // Gunakan prepared statement untuk INSERT
        // Kolom idpenjualan akan otomatis diisi oleh trigger database
        $stmt_insert_penjualan = mysqli_prepare($c, "INSERT INTO penjualan (idkasir) VALUES (?)");
        if (!$stmt_insert_penjualan) {
            throw new Exception("Gagal menyiapkan statement INSERT penjualan: " . mysqli_error($c));
        }
        mysqli_stmt_bind_param($stmt_insert_penjualan, "i", $idkasir);
        $insert_success = mysqli_stmt_execute($stmt_insert_penjualan);
        mysqli_stmt_close($stmt_insert_penjualan);

        if ($insert_success) {
            // Setelah berhasil insert, ambil idpenjualan yang baru saja dibuat oleh trigger.
            // Kita mencari penjualan terbaru oleh idkasir ini, diurutkan berdasarkan tanggal terbaru.
            $stmt_get_new_id = mysqli_prepare($c, "SELECT idpenjualan FROM penjualan WHERE idkasir = ? ORDER BY tanggal DESC LIMIT 1");
            if (!$stmt_get_new_id) {
                throw new Exception("Gagal menyiapkan statement SELECT idpenjualan: " . mysqli_error($c));
            }
            mysqli_stmt_bind_param($stmt_get_new_id, "i", $idkasir);
            mysqli_stmt_execute($stmt_get_new_id);
            $result_get_new_id = mysqli_stmt_get_result($stmt_get_new_id);
            
            if ($result_get_new_id && mysqli_num_rows($result_get_new_id) > 0) {
                $row_new_id = mysqli_fetch_assoc($result_get_new_id);
                $new_idpenjualan = $row_new_id['idpenjualan'];
            } else {
                // Ini seharusnya tidak terjadi jika insert berhasil dan trigger berfungsi
                throw new Exception("ID penjualan baru tidak ditemukan setelah insert. Periksa trigger.");
            }
            mysqli_stmt_close($stmt_get_new_id);

            // Commit transaksi jika semua operasi berhasil
            mysqli_commit($c); 

            error_log("Penjualan baru berhasil ditambahkan. ID Kasir: " . $idkasir . ", ID Penjualan Baru: " . $new_idpenjualan);
            echo '
            <script>
                alert("Penjualan baru berhasil ditambahkan!");
                // Redirect ke halaman detail penjualan yang baru dibuat
                window.location.href="penjualan.php?idp=' . htmlspecialchars($new_idpenjualan) . '"; 
            </script>';
            exit(); // Penting untuk memastikan script berhenti setelah redirect

        } else {
            throw new Exception("Gagal mengeksekusi INSERT penjualan baru.");
        }

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($c); 
        error_log("Gagal menambah penjualan baru. Error: " . $e->getMessage());
        echo '
        <script>
            alert("Gagal menambah penjualan baru: ' . $e->getMessage() . '");
            window.location.href="penjualan.php"; // Kembali ke halaman daftar penjualan
        </script>
        ';
        exit();
    }
}


// Hapus penjualan
if(isset($_POST['hapusorder'])){
    $ido = $_POST['ido']; // idpenjualan - Tidak perlu di-cast ke int

    // Mulai transaksi
    mysqli_begin_transaction($c);
    $all_success = true; // flag keberhasilan semua proses

    try {
        $stmt_cekdata = mysqli_prepare($c, "SELECT iddetailpenjualan, idproduk, jumlah FROM detailpenjualan WHERE idpenjualan=?");
        // DIUBAH: Bind parameter idpenjualan sebagai string ("s")
        mysqli_stmt_bind_param($stmt_cekdata, "s", $ido); 
        mysqli_stmt_execute($stmt_cekdata);
        $result_cekdata = mysqli_stmt_get_result($stmt_cekdata);

        while($ok = mysqli_fetch_array($result_cekdata)){
            $jumlah = $ok['jumlah'];
            $idproduk = $ok['idproduk'];
            $iddp = $ok['iddetailpenjualan'];

            // Cek stok saat ini
            $stmt_caristock = mysqli_prepare($c,"SELECT stock FROM produk WHERE idproduk=?");
            mysqli_stmt_bind_param($stmt_caristock, "i", $idproduk);
            mysqli_stmt_execute($stmt_caristock);
            $result_caristock = mysqli_stmt_get_result($stmt_caristock);
            $caristock2 = mysqli_fetch_array($result_caristock);
            $stocksekarang = $caristock2['stock'];
            mysqli_stmt_close($stmt_caristock);

            $newstock = $stocksekarang + $jumlah;

            // Update stok
            $stmt_update = mysqli_prepare($c, "UPDATE produk SET stock=? WHERE idproduk=?");
            mysqli_stmt_bind_param($stmt_update, "ii", $newstock, $idproduk);
            if (!mysqli_stmt_execute($stmt_update)) {
                $all_success = false;
                throw new Exception("Gagal update stok produk.");
            }
            mysqli_stmt_close($stmt_update);

            // Hapus detail penjualan
            $stmt_delete_detail = mysqli_prepare($c, "DELETE FROM detailpenjualan WHERE iddetailpenjualan=?");
            mysqli_stmt_bind_param($stmt_delete_detail, "i", $iddp);
            if (!mysqli_stmt_execute($stmt_delete_detail)) {
                $all_success = false;
                throw new Exception("Gagal hapus detail penjualan.");
            }
            mysqli_stmt_close($stmt_delete_detail);
        }
        mysqli_stmt_close($stmt_cekdata);

        // Hapus penjualan utama
        $stmt_delete_penjualan = mysqli_prepare($c, "DELETE FROM penjualan WHERE idpenjualan=?");
        // DIUBAH: Bind parameter idpenjualan sebagai string ("s")
        mysqli_stmt_bind_param($stmt_delete_penjualan, "s", $ido); 
        if (!mysqli_stmt_execute($stmt_delete_penjualan)) {
            $all_success = false;
            throw new Exception("Gagal hapus penjualan utama.");
        }
        mysqli_stmt_close($stmt_delete_penjualan);

        if($all_success){
            mysqli_commit($c); // Commit transaksi jika semua berhasil
            echo '
            <script>
                alert("Berhasil hapus penjualan!");
                window.location.href="penjualan_owner.php";
            </script>
            ';
        } else {
            mysqli_rollback($c); // Rollback jika ada yang gagal
            echo '
            <script>
                alert("Gagal menghapus data sepenuhnya!");
                window.location.href="penjualan.php";
            </script>
            ';
        }
    } catch (Exception $e) {
        mysqli_rollback($c); // Pastikan rollback jika ada exception
        echo '
        <script>
            alert("Gagal menghapus data sepenuhnya: ' . $e->getMessage() . '");
            window.location.href="penjualan.php";
        </script>
        ';
    }
}


// Menambah produk penjualan
if (isset($_POST['addproduk'])) {
    $idproduk = $_POST['idproduk'];
    $idp = $_POST['idp']; // id penjualan

    $input_stock_pcs = isset($_POST['stock']) ? intval($_POST['stock']) : 0; 
    $jumlah_ctn_dus = isset($_POST['jumlah_ctn_dus']) ? intval($_POST['jumlah_ctn_dus']) : 0;

    // Ambil isi_pcs_per_ctn menggunakan prepared statement
    $isi_pcs_per_ctn = 1; // Default
    $stmt_pcs_per_ctn = mysqli_prepare($c, "SELECT isi_pcs_per_ctn FROM produk WHERE idproduk=?");
    if ($stmt_pcs_per_ctn) {
        mysqli_stmt_bind_param($stmt_pcs_per_ctn, "i", $idproduk);
        mysqli_stmt_execute($stmt_pcs_per_ctn);
        $result_pcs_per_ctn = mysqli_stmt_get_result($stmt_pcs_per_ctn);
        if ($result_pcs_per_ctn && mysqli_num_rows($result_pcs_per_ctn) > 0) {
            $data_produk_pcs = mysqli_fetch_array($result_pcs_per_ctn);
            $isi_pcs_per_ctn = $data_produk_pcs['isi_pcs_per_ctn'];
        }
        mysqli_stmt_close($stmt_pcs_per_ctn);
    }

    $total_pcs_dari_ctn_dus = $jumlah_ctn_dus * $isi_pcs_per_ctn;
    $jumlah_final = $input_stock_pcs + $total_pcs_dari_ctn_dus;
    $jumlah = $jumlah_final; 

    // Cek stok tersedia menggunakan prepared statement
    $stocksekarang_tersedia = 0;
    $stmt_stock_available = mysqli_prepare($c, "SELECT stock FROM produk WHERE idproduk=?");
    if ($stmt_stock_available) {
        mysqli_stmt_bind_param($stmt_stock_available, "i", $idproduk);
        mysqli_stmt_execute($stmt_stock_available);
        $result_stock_available = mysqli_stmt_get_result($stmt_stock_available);
        
        if ($result_stock_available && mysqli_num_rows($result_stock_available) > 0) {
            $data_stock = mysqli_fetch_array($result_stock_available);
            $stocksekarang_tersedia = $data_stock['stock'];
        } else {
            echo '
            <script>
                alert("Produk tidak ditemukan di database.");
                window.location.href = "view.php?idp=' . $idp . '";
            </script>';
            exit();
        }
        mysqli_stmt_close($stmt_stock_available);
    } else {
        echo '
        <script>
            alert("Gagal menyiapkan query cek stok.");
            window.location.href = "view.php?idp=' . $idp . '";
        </script>';
        exit();
    }
    
    if ($stocksekarang_tersedia >= $jumlah) {
        $selisih = $stocksekarang_tersedia - $jumlah;
        
        // Mulai transaksi untuk INSERT dan UPDATE
        mysqli_begin_transaction($c);
        $insert_success = false;
        $update_success = false;

        try {
            // INSERT ke detailpenjualan menggunakan prepared statement
            $stmt_insert_detail = mysqli_prepare($c, "INSERT INTO detailpenjualan (idpenjualan, idproduk, jumlah) VALUES (?, ?, ?)");
            // PERBAIKAN DI SINI: Ubah "iii" menjadi "sii"
            mysqli_stmt_bind_param($stmt_insert_detail, "sii", $idp, $idproduk, $jumlah);
            $insert_success = mysqli_stmt_execute($stmt_insert_detail);
            mysqli_stmt_close($stmt_insert_detail);

            // UPDATE stock produk menggunakan prepared statement
            $stmt_update_produk = mysqli_prepare($c, "UPDATE produk SET stock = ? WHERE idproduk = ?");
            mysqli_stmt_bind_param($stmt_update_produk, "ii", $selisih, $idproduk);
            $update_success = mysqli_stmt_execute($stmt_update_produk);
            mysqli_stmt_close($stmt_update_produk);

            if ($insert_success && $update_success) {
                mysqli_commit($c); // Commit transaksi
                echo '
                <script>
                    window.location.href = "view.php?idp=' . $idp . '";
                </script>';
                exit();
            } else {
                throw new Exception("Gagal menambah penjualan baru atau memperbarui stock.");
            }
        } catch (Exception $e) {
            mysqli_rollback($c); // Rollback transaksi jika ada error
            echo '
            <script>
                alert("Error: ' . $e->getMessage() . ' Error detail: ' . mysqli_error($c) . '");
                window.location.href = "view.php?idp=' . $idp . '";
            </script>';
        }
    } else {
        echo '
        <script>
            alert("Stock barang tidak cukup. Stock tersedia: ' . $stocksekarang_tersedia . ' pcs.");
            window.location.href = "view.php?idp=' . $idp . '";
        </script>';
    }
}


// Mengubah produk penjualan
if (isset($_POST['editdetailpenjualan'])) {
    $iddp = (int)$_POST['iddp']; // iddetailpenjualan
    $idpr = (int)$_POST['idpr']; // idproduk
    $idp = $_POST['idp'];        // idpenjualan - DIUBAH: Tidak lagi di-cast ke (int)

    // Ambil input dari form
    $pcs = isset($_POST['jumlah_pcs']) ? (int)$_POST['jumlah_pcs'] : 0;
    $ctn = isset($_POST['jumlah_ctn_dus']) ? (int)$_POST['jumlah_ctn_dus'] : 0;

    // Ambil nilai isi per dus dari database menggunakan prepared statement (lebih aman)
    $isi_pcs_per_ctn = 1; // Default
    $stmt_pcs_per_dus = mysqli_prepare($c, "SELECT isi_pcs_per_ctn FROM produk WHERE idproduk = ?");
    if ($stmt_pcs_per_dus) {
        mysqli_stmt_bind_param($stmt_pcs_per_dus, "i", $idpr);
        mysqli_stmt_execute($stmt_pcs_per_dus);
        $result_pcs_per_dus = mysqli_stmt_get_result($stmt_pcs_per_dus);
        if ($result_pcs_per_dus && mysqli_num_rows($result_pcs_per_dus) > 0) {
            $data_pcs = mysqli_fetch_assoc($result_pcs_per_dus);
            $isi_pcs_per_ctn = (int)$data_pcs['isi_pcs_per_ctn'];
        }
        mysqli_stmt_close($stmt_pcs_per_dus);
    } else {
        // Handle error jika prepared statement gagal
        echo '
        <script>
            alert("Gagal menyiapkan query untuk isi_pcs_per_ctn.");
            window.location.href="view.php?idp=' . htmlspecialchars($idp) . '";
        </script>
        ';
        exit();
    }

    // Hitung jumlah baru dalam satuan PCS
    $jumlah_baru = ($ctn * $isi_pcs_per_ctn) + $pcs;

    // Mulai transaksi
    mysqli_begin_transaction($c);
    try {
        // Cari jumlah lama di detailpenjualan
        $stmt_caritahu = mysqli_prepare($c, "SELECT jumlah FROM detailpenjualan WHERE iddetailpenjualan=?");
        mysqli_stmt_bind_param($stmt_caritahu, "i", $iddp);
        mysqli_stmt_execute($stmt_caritahu);
        $result_caritahu = mysqli_stmt_get_result($stmt_caritahu);
        $caritahu2 = mysqli_fetch_array($result_caritahu);
        $jumlahsekarang_lama = $caritahu2['jumlah'];
        mysqli_stmt_close($stmt_caritahu);

        // Cari stok produk saat ini
        $stmt_caristock = mysqli_prepare($c, "SELECT stock FROM produk WHERE idproduk=?");
        mysqli_stmt_bind_param($stmt_caristock, "i", $idpr);
        mysqli_stmt_execute($stmt_caristock);
        $result_caristock = mysqli_stmt_get_result($stmt_caristock);
        $caristock2 = mysqli_fetch_array($result_caristock);
        $stocksekarang_produk = $caristock2['stock'];
        mysqli_stmt_close($stmt_caristock);

        $newstock_produk = $stocksekarang_produk;

        if ($jumlah_baru > $jumlahsekarang_lama) {
            // Jumlah naik → stok dikurangi
            $selisih = $jumlah_baru - $jumlahsekarang_lama;
            $newstock_produk = $stocksekarang_produk - $selisih;

            if ($newstock_produk < 0) {
                throw new Exception("Stok tidak mencukupi untuk menambah jumlah.");
            }
        } else {
            // Jumlah turun → stok dikembalikan
            $selisih = $jumlahsekarang_lama - $jumlah_baru;
            $newstock_produk = $stocksekarang_produk + $selisih;
        }

        // Update detailpenjualan
        $stmt_update_detail = mysqli_prepare($c, "UPDATE detailpenjualan SET jumlah=? WHERE iddetailpenjualan=?");
        mysqli_stmt_bind_param($stmt_update_detail, "ii", $jumlah_baru, $iddp);
        $query1_success = mysqli_stmt_execute($stmt_update_detail);
        mysqli_stmt_close($stmt_update_detail);

        // Update stock produk
        $stmt_update_produk = mysqli_prepare($c, "UPDATE produk SET stock=? WHERE idproduk=?");
        mysqli_stmt_bind_param($stmt_update_produk, "ii", $newstock_produk, $idpr);
        $query2_success = mysqli_stmt_execute($stmt_update_produk);
        mysqli_stmt_close($stmt_update_produk);

        if ($query1_success && $query2_success) {
            mysqli_commit($c);
            header('location:view.php?idp=' . htmlspecialchars($idp)); // Pastikan idp di-encode
            exit();
        } else {
            throw new Exception("Gagal mengupdate detail penjualan atau stok produk.");
        }
    } catch (Exception $e) {
        mysqli_rollback($c);
        echo '
        <script>alert("Gagal: ' . $e->getMessage() . '");
        window.location.href="view.php?idp=' . htmlspecialchars($idp) . '";
        </script>
        ';
    }
}


// Mengubah produk penjualan
if (isset($_POST['editdetailpenjualan'])) {
    $iddp = (int)$_POST['iddp']; // iddetailpenjualan
    $idpr = (int)$_POST['idpr']; // idproduk
    $idp = $_POST['idp'];        // idpenjualan - DIUBAH: Tidak lagi di-cast ke (int)

    // Ambil input dari form
    $pcs = isset($_POST['jumlah_pcs']) ? (int)$_POST['jumlah_pcs'] : 0;
    $ctn = isset($_POST['jumlah_ctn_dus']) ? (int)$_POST['jumlah_ctn_dus'] : 0;

    // Ambil nilai isi per dus dari database menggunakan prepared statement (lebih aman)
    $isi_pcs_per_ctn = 1; // Default
    $stmt_pcs_per_dus = mysqli_prepare($c, "SELECT isi_pcs_per_ctn FROM produk WHERE idproduk = ?");
    if ($stmt_pcs_per_dus) {
        mysqli_stmt_bind_param($stmt_pcs_per_dus, "i", $idpr);
        mysqli_stmt_execute($stmt_pcs_per_dus);
        $result_pcs_per_dus = mysqli_stmt_get_result($stmt_pcs_per_dus);
        if ($result_pcs_per_dus && mysqli_num_rows($result_pcs_per_dus) > 0) {
            $data_pcs = mysqli_fetch_assoc($result_pcs_per_dus);
            $isi_pcs_per_ctn = (int)$data_pcs['isi_pcs_per_ctn'];
        }
        mysqli_stmt_close($stmt_pcs_per_dus);
    } else {
        // Handle error jika prepared statement gagal
        echo '
        <script>
            alert("Gagal menyiapkan query untuk isi_pcs_per_ctn.");
            window.location.href="view.php?idp=' . htmlspecialchars($idp) . '";
        </script>
        ';
        exit();
    }

    // Hitung jumlah baru dalam satuan PCS
    $jumlah_baru = ($ctn * $isi_pcs_per_ctn) + $pcs;

    // Mulai transaksi
    mysqli_begin_transaction($c);
    try {
        // Cari jumlah lama di detailpenjualan
        $stmt_caritahu = mysqli_prepare($c, "SELECT jumlah FROM detailpenjualan WHERE iddetailpenjualan=?");
        mysqli_stmt_bind_param($stmt_caritahu, "i", $iddp);
        mysqli_stmt_execute($stmt_caritahu);
        $result_caritahu = mysqli_stmt_get_result($stmt_caritahu);
        $caritahu2 = mysqli_fetch_array($result_caritahu);
        $jumlahsekarang_lama = $caritahu2['jumlah'];
        mysqli_stmt_close($stmt_caritahu);

        // Cari stok produk saat ini
        $stmt_caristock = mysqli_prepare($c, "SELECT stock FROM produk WHERE idproduk=?");
        mysqli_stmt_bind_param($stmt_caristock, "i", $idpr);
        mysqli_stmt_execute($stmt_caristock);
        $result_caristock = mysqli_stmt_get_result($stmt_caristock);
        $caristock2 = mysqli_fetch_array($result_caristock);
        $stocksekarang_produk = $caristock2['stock'];
        mysqli_stmt_close($stmt_caristock);

        $newstock_produk = $stocksekarang_produk;

        if ($jumlah_baru > $jumlahsekarang_lama) {
            // Jumlah naik → stok dikurangi
            $selisih = $jumlah_baru - $jumlahsekarang_lama;
            $newstock_produk = $stocksekarang_produk - $selisih;

            if ($newstock_produk < 0) {
                throw new Exception("Stok tidak mencukupi untuk menambah jumlah.");
            }
        } else {
            // Jumlah turun → stok dikembalikan
            $selisih = $jumlahsekarang_lama - $jumlah_baru;
            $newstock_produk = $stocksekarang_produk + $selisih;
        }

        // Update detailpenjualan
        $stmt_update_detail = mysqli_prepare($c, "UPDATE detailpenjualan SET jumlah=? WHERE iddetailpenjualan=?");
        mysqli_stmt_bind_param($stmt_update_detail, "ii", $jumlah_baru, $iddp);
        $query1_success = mysqli_stmt_execute($stmt_update_detail);
        mysqli_stmt_close($stmt_update_detail);

        // Update stock produk
        $stmt_update_produk = mysqli_prepare($c, "UPDATE produk SET stock=? WHERE idproduk=?");
        mysqli_stmt_bind_param($stmt_update_produk, "ii", $newstock_produk, $idpr);
        $query2_success = mysqli_stmt_execute($stmt_update_produk);
        mysqli_stmt_close($stmt_update_produk);

        if ($query1_success && $query2_success) {
            mysqli_commit($c);
            header('location:view.php?idp=' . htmlspecialchars($idp)); // Pastikan idp di-encode
            exit();
        } else {
            throw new Exception("Gagal mengupdate detail penjualan atau stok produk.");
        }
    } catch (Exception $e) {
        mysqli_rollback($c);
        echo '
        <script>alert("Gagal: ' . $e->getMessage() . '");
        window.location.href="view.php?idp=' . htmlspecialchars($idp) . '";
        </script>
        ';
    }
}


// Hapus produk penjualan
if (isset($_POST['hapusprodukpenjualan'])) {
    $iddp = (int)$_POST['iddp']; // iddetailpenjualan
    $idpr = (int)$_POST['idpr'];
    $idpenjualan = $_POST['idpenjualan']; // idpenjualan - Tidak perlu di-cast ke int

    // Mulai transaksi
    mysqli_begin_transaction($c);
    try {
        // Cek jumlah sekarang di detailpenjualan
        $stmt_cek1 = mysqli_prepare($c, "SELECT jumlah FROM detailpenjualan WHERE iddetailpenjualan=?");
        mysqli_stmt_bind_param($stmt_cek1, "i", $iddp);
        mysqli_stmt_execute($stmt_cek1);
        $result_cek1 = mysqli_stmt_get_result($stmt_cek1);
        $cek2 = mysqli_fetch_array($result_cek1);
        $jumlahsekarang = $cek2['jumlah'];
        mysqli_stmt_close($stmt_cek1);


        // Cek stock sekarang di produk
        $stmt_cek3 = mysqli_prepare($c, "SELECT stock FROM produk WHERE idproduk=?");
        mysqli_stmt_bind_param($stmt_cek3, "i", $idpr);
        mysqli_stmt_execute($stmt_cek3);
        $result_cek3 = mysqli_stmt_get_result($stmt_cek3);
        $cek4 = mysqli_fetch_array($result_cek3);
        $stocksesekarang = $cek4['stock'];
        mysqli_stmt_close($stmt_cek3);

        $hitung_stock_kembali = $stocksesekarang + $jumlahsekarang;

        // Update stock produk
        $stmt_update_stock = mysqli_prepare($c, "UPDATE produk SET stock=? WHERE idproduk=?"); // update stock
        mysqli_stmt_bind_param($stmt_update_stock, "ii", $hitung_stock_kembali, $idpr);
        $update_success = mysqli_stmt_execute($stmt_update_stock);
        mysqli_stmt_close($stmt_update_stock);

        // Hapus detail penjualan
        $stmt_hapus_detail = mysqli_prepare($c, "DELETE FROM detailpenjualan WHERE iddetailpenjualan=?");
        mysqli_stmt_bind_param($stmt_hapus_detail, "i", $iddp);
        $hapus_success = mysqli_stmt_execute($stmt_hapus_detail);
        mysqli_stmt_close($stmt_hapus_detail);

        if ($update_success && $hapus_success) {
            mysqli_commit($c); // Commit transaksi
            header('location:view.php?idp=' . htmlspecialchars($idpenjualan)); // PERBAIKAN: Gunakan htmlspecialchars
            exit();
        } else {
            throw new Exception("Gagal memperbarui stok atau menghapus item penjualan.");
        }

    } catch (Exception $e) {
        mysqli_rollback($c); // Rollback jika ada error
        echo '
        <script>
            alert("Gagal menghapus barang: ' . $e->getMessage() . ' Error detail: ' . mysqli_error($c) . '");
            window.location.href = "view.php?idp=' . htmlspecialchars($idpenjualan) . '"; // PERBAIKAN: Gunakan htmlspecialchars
        </script>
        ';
    }
}


// --- LOGIKA PENANGANAN PEMBAYARAN BARU DIMULAI DI SINI ---
if (isset($_POST['proses_pembayaran'])) {
    // DIUBAH: Gunakan FILTER_SANITIZE_STRING (atau biarkan langsung) karena idpenjualan sekarang VARCHAR
    $id_penjualan_bayar = filter_input(INPUT_POST, 'idpenjualan_bayar', FILTER_SANITIZE_STRING); 
    $cash_input = filter_input(INPUT_POST, 'cash_input', FILTER_VALIDATE_FLOAT);

    // Ambil ID Kasir yang sedang login dari sesi
    $idkasir_aktif = isset($_SESSION['idkasir_kasir']) ? $_SESSION['idkasir_kasir'] : null;
    $iduser_aktif = isset($_SESSION['iduser_admin']) ? $_SESSION['iduser_admin'] : null; // Jika admin bisa bayar

    // Validasi ID Kasir/User
    if ($idkasir_aktif === null && $iduser_aktif === null) {
        echo '
        <script>
            alert("Sesi login tidak ditemukan. Harap login kembali.");
            window.location.href = "login.php"; // Redirect ke halaman login
        </script>
        ';
        exit();
    }
    // Pilih ID yang relevan, prioritaskan idkasir_aktif jika ada
    $current_id_pengguna = ($idkasir_aktif !== null) ? $idkasir_aktif : $iduser_aktif;


    // Ambil grand total penjualan dari database (ini harus dilakukan secara aman)
    $grand_total_for_validation = 0;
    // DIUBAH: Cek $id_penjualan_bayar tidak kosong, bukan > 0
    if (!empty($id_penjualan_bayar)) {
        $query_get_grand_total = "
            SELECT SUM(dp.jumlah * p.hargajual) AS grand_total
            FROM kasir.detailpenjualan dp
            JOIN kasir.produk p ON dp.idproduk = p.idproduk
            WHERE dp.idpenjualan = ?
        ";
        
        if ($stmt_grand_total = mysqli_prepare($c, $query_get_grand_total)) {
            // DIUBAH: Bind parameter sebagai string ("s")
            mysqli_stmt_bind_param($stmt_grand_total, "s", $id_penjualan_bayar);
            mysqli_stmt_execute($stmt_grand_total);
            $result_grand_total = mysqli_stmt_get_result($stmt_grand_total);
            $row_grand_total = mysqli_fetch_assoc($result_grand_total);

            if ($row_grand_total && $row_grand_total['grand_total'] !== null) {
                $grand_total_for_validation = $row_grand_total['grand_total'];
            } else {
                echo '
                <script>
                    alert("Error: Data penjualan tidak ditemukan atau tidak memiliki item.");
                    window.location.href = "index.php"; // Redirect ke halaman utama atau error
                </script>
                ';
                exit();
            }
            mysqli_stmt_close($stmt_grand_total);
        } else {
            echo '
            <script>
                alert("Error prepare statement saat mengambil grand total: ' . mysqli_error($c) . '");
                window.location.href = "index.php";
            </script>
            ';
            exit();
        }
    } else {
        echo '
        <script>
            alert("ID Penjualan tidak valid.");
            window.location.href = "index.php";
        </script>
        ';
        exit();
    }


    // Validasi input pembayaran
    if ($cash_input === false || $cash_input < $grand_total_for_validation) {
        echo '
        <script>
            alert("Jumlah uang tunai tidak valid atau kurang dari total pembayaran. Harap masukkan minimal Rp. ' . number_format($grand_total_for_validation, 0, ',', '.') . '");
            // DIUBAH: Gunakan htmlspecialchars untuk keamanan URL
            window.location.href = "view.php?idp=' . htmlspecialchars($id_penjualan_bayar) . '"; // Kembali ke halaman view order
        </script>
        ';
        exit();
    }

    $kembalian = $cash_input - $grand_total_for_validation;

    // Mulai transaksi database
    mysqli_begin_transaction($c);

    try {
        // 1. Update data di tabel `kasir.penjualan`
        // Perhatikan: idkasir sudah ada di tabel penjualan. Tidak perlu diupdate ulang.
        // Cukup update jumlah_bayar, kembalian, dan tanggal.
        $query_update_penjualan = "
            UPDATE kasir.penjualan
            SET jumlah_bayar = ?, kembalian = ?, tanggal = NOW()
            WHERE idpenjualan = ? AND (jumlah_bayar IS NULL OR jumlah_bayar = 0)
        ";
        if ($stmt_update_penjualan = mysqli_prepare($c, $query_update_penjualan)) {
            // DIUBAH: Bind parameter idpenjualan sebagai string ("s")
            mysqli_stmt_bind_param($stmt_update_penjualan, "dds", $cash_input, $kembalian, $id_penjualan_bayar);
            mysqli_stmt_execute($stmt_update_penjualan);

            if (mysqli_stmt_affected_rows($stmt_update_penjualan) === 0) {
                throw new Exception("Penjualan tidak ditemukan atau sudah dibayar.");
            }
            mysqli_stmt_close($stmt_update_penjualan);
        } else {
            throw new Exception("Error prepare statement update penjualan: " . mysqli_error($c));
        }

        // 2. Tidak perlu update stok di sini karena sudah dikurangi saat `addproduk`
        // Jika ada kasus di mana stok belum dikurangi, logika pengurangan stok akan ditempatkan di sini.
        // Berdasarkan logika 'addproduk' Anda, stok sudah dikurangi saat item ditambahkan ke detail penjualan.
        // Jadi, untuk pembayaran, kita hanya update status pembayaran di tabel penjualan.

        // Commit transaksi jika semua berhasil
        mysqli_commit($c);

        echo '
        <script>
            alert("Pembayaran berhasil! Kembalian: Rp. ' . number_format($kembalian, 0, ',', '.') . '");
            // DIUBAH: Gunakan htmlspecialchars untuk keamanan URL
            window.location.href = "print_struk.php?idp=' . htmlspecialchars($id_penjualan_bayar) . '"; // Ganti dengan halaman struk Anda
        </script>
        ';
        exit();

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($c);
        echo '
        <script>
            alert("Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage() . '");
            // DIUBAH: Gunakan htmlspecialchars untuk keamanan URL
            window.location.href = "view.php?idp=' . htmlspecialchars($id_penjualan_bayar) . '"; // Kembali ke halaman view order
        </script>
        ';
    }
}


// --- LOGIKA PENANGANAN PEMBAYARAN BARU BERAKHIR DI SINI ---

?>