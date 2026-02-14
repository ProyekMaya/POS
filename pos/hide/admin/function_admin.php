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
    $username_db_column = ''; // Kolom username di database (bisa 'username' atau 'namakasir')

    // Tentukan tabel dan kolom berdasarkan loginType
    if ($loginType === 'admin') {
        $table_name = 'user';
        $id_column = 'iduser';
        $username_db_column = 'username';
    } elseif ($loginType === 'kasir') {
        $table_name = 'datakasir';
        $id_column = 'idkasir';
        $username_db_column = 'namakasir'; // Sesuai skema database Anda
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
        unset($_SESSION['namakasir_kasir']);
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
            $_SESSION['namakasir_kasir'] = $loggedInUsername;
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

// Tambah Barang Admin
if (isset($_POST['tambahbarangadmin'])) {
    $namaproduk = $_POST['namaproduk'];
    $pcs_per_dus_ctn_baru = (int)$_POST['pcs_per_dus_ctn'];
    $stock_baru = (int)$_POST['stock'];
    $jumlah_dus_ctn_baru = (int)$_POST['jumlah_dus_ctn'];
    $hargamodal_baru = $_POST['hargamodal'];
    $hargajual_baru = $_POST['hargajual'];

    // Validasi: Pastikan nama produk tidak kosong
    if (empty($namaproduk)) {
        echo '
        <script>
            alert("Nama produk tidak boleh kosong.");
            window.location.href = "stock_admin.php", ;
        </script>
        ';
        exit;
    }

    // Hitung Jumlah Pcs per Dus/Ctn
    $total_pcs_dari_dus_ctn = $jumlah_dus_ctn_baru * $pcs_per_dus_ctn_baru;
    $hitung_final_stock = $stock_baru + $total_pcs_dari_dus_ctn;
    $jumlah_final_stock = $hitung_final_stock;
    
    // Sanitasi input untuk mencegah SQL Injection
    $namaproduk_escaped = mysqli_real_escape_string($c, $namaproduk);
    $pcs_per_dus_ctn_baru_escaped = mysqli_real_escape_string($c, $pcs_per_dus_ctn_baru);
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
                                 pcs_per_dus_ctn = '$pcs_per_dus_ctn_baru_escaped',
                                 stock = '$stock_total_terbaru'
                                 WHERE namaproduk = '$namaproduk_escaped'");

        if ($update) {
            echo '
            <script>
                alert("Produk dengan nama \'' . $namaproduk . '\' sudah ada. Stok berhasil ditambahkan menjadi ' . $stock_total_terbaru . ', Isi Pcs per Dus/Ctn, Harga Modal, dan Harga Jual berhasil diupdate.");
                window.location.href = "stock_admin.php";
            </script>
            ';
        } else {
            // Tampilkan error MySQL jika update gagal (opsional, untuk debugging)
            echo '
            <script>
                alert("Gagal mengupdate produk: ' . mysqli_error($c) . '");
                window.location.href = "stock_admin.php";
            </script>
            ';
        }
    } else {
        // Produk belum ada, lakukan INSERT
        $insert = mysqli_query($c, "INSERT INTO produk (namaproduk, hargamodal, hargajual, pcs_per_dus_ctn, stock)
                                 VALUES ('$namaproduk_escaped', '$hargamodal_baru_escaped', '$hargajual_baru_escaped', '$pcs_per_dus_ctn_baru_escaped', '$jumlah_final_stock_escaped')");

        if ($insert) {
            echo '
            <script>
                alert("Barang berhasil ditambahkan.");
                window.location.href = "stock_admin.php";
            </script>
            ';
        } else {
            // Tampilkan error MySQL jika insert gagal (opsional, untuk debugging)
            echo '
            <script>
                alert("Gagal menambah barang: ' . mysqli_error($c) . '");
                window.location.href = "stock_admin.php";
            </script>
            ';
        }
    }
}

// Edit Barang Admin
if (isset($_POST['editbarangadmin'])) {
    $np = $_POST['namaproduk'];
    $stock = $_POST['stock'];
    $pcs_per_dus_ctn = $_POST['pcs_per_dus_ctn'];
    $hargamodal = $_POST['hargamodal'];
    $hargajual = $_POST['hargajual'];
    $idp = $_POST['idp'];

    // Validasi: Pastikan nama produk tidak kosong
    if (empty($np)) {
        echo '
        <script>
            alert("Nama produk tidak boleh kosong.");
            window.location.href = "stock_admin.php";
        </script>
        ';
        exit;
    }

    // Sanitasi input untuk mencegah SQL Injection
    $np_escaped = mysqli_real_escape_string($c, $np);
    $stock_escaped = mysqli_real_escape_string($c, $stock);
    $pcs_per_dus_ctn_escaped = mysqli_real_escape_string($c, $pcs_per_dus_ctn);
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
            window.location.href = "stock_admin.php";
        </script>
        ';
    } else {
        $query = mysqli_query($c, "UPDATE produk SET namaproduk='$np_escaped', stock='$stock_escaped', pcs_per_dus_ctn='$pcs_per_dus_ctn_escaped', hargamodal='$hargamodal_escaped', hargajual='$hargajual_escaped' WHERE idproduk='$idp_escaped' ");

        if ($query) {
            echo '
            <script>
                alert("Data barang berhasil diupdate.");
                window.location.href = "stock_admin.php";
            </script>
            ';
        } else {
            echo '
            <script>
                alert("Gagal mengupdate barang: ' . mysqli_error($c) . '");
                window.location.href = "stock_admin.php";
            </script>
            ';
        }
    }
}

// Hapus Barang Admin
if (isset($_POST['hapusbarangadmin'])) {
    $idp = $_POST['idp'];

    // Sanitasi input
    $idp_escaped = mysqli_real_escape_string($c, $idp);

    $query = mysqli_query($c, "DELETE FROM produk WHERE idproduk='$idp_escaped'");

    if ($query) {
        echo '
        <script>
            alert("Barang berhasil dihapus.");
            window.location.href = "stock_admin.php";
        </script>
        ';
    } else {
        echo '
        <script>
            alert("Gagal menghapus barang: ' . mysqli_error($c) . '");
            window.location.href = "stock_admin.php";
        </script>
        ';
    }
}

// Logika untuk menambah kasir
if (isset($_POST['tambahkasir'])) {
    $namakasir = mysqli_real_escape_string($c, $_POST['namakasir']);
    $notelp = mysqli_real_escape_string($c, $_POST['notelp']);
    $alamat = mysqli_real_escape_string($c, $_POST['alamat']);
    $password = mysqli_real_escape_string($c, $_POST['password']);
    // Direkomendasikan: Hash password sebelum menyimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert = mysqli_query($c, "INSERT INTO datakasir (namakasir, notelp, alamat, password) VALUES ('$namakasir', '$notelp', '$alamat', '$hashed_password')");

    if ($insert) {
        echo "<script>alert('Data kasir berhasil ditambahkan!'); window.location.href='manajemen_user.php'</script>"; // Ganti 'index.php' dengan URL halaman Anda
    } else {
        echo "<script>alert('Gagal menambahkan data kasir: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk menambah user
if (isset($_POST['tambahuser'])) {
    $username = mysqli_real_escape_string($c, $_POST['username']);
    $password = mysqli_real_escape_string($c, $_POST['password']);
    // Direkomendasikan: Hash password sebelum menyimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert = mysqli_query($c, "INSERT INTO user (username, password) VALUES ('$username', '$hashed_password')");

    if ($insert) {
        echo "<script>alert('Data user berhasil ditambahkan!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data user: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk menambah dataowner
if (isset($_POST['tambahadmin'])) {
    $username = mysqli_real_escape_string($c, $_POST['username']);
    $password = mysqli_real_escape_string($c, $_POST['password']);
    // Direkomendasikan: Hash password sebelum menyimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert = mysqli_query($c, "INSERT INTO dataowner (username, password) VALUES ('$username', '$hashed_password')");

    if ($insert) {
        echo "<script>alert('Data admin berhasil ditambahkan!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data admin: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk edit kasir
if (isset($_POST['editkasir'])) {
    $idkasir = mysqli_real_escape_string($c, $_POST['id']);
    $namakasir = mysqli_real_escape_string($c, $_POST['nama']);
    $notelp = mysqli_real_escape_string($c, $_POST['notelp']);
    $alamat = mysqli_real_escape_string($c, $_POST['alamat']);
    // Tambahkan password jika ingin mengizinkan perubahan password di modal edit
    $update_query = "UPDATE datakasir SET namakasir='$namakasir', notelp='$notelp', alamat='$alamat' WHERE idkasir='$idkasir'";
    if (!empty($_POST['password'])) {
        $new_password_hashed = password_hash(mysqli_real_escape_string($c, $_POST['password']), PASSWORD_DEFAULT);
        $update_query = "UPDATE datakasir SET namakasir='$namakasir', notelp='$notelp', alamat='$alamat', password='$new_password_hashed' WHERE idkasir='$idkasir'";
    }

    $update = mysqli_query($c, $update_query);
    if ($update) {
        echo "<script>alert('Data kasir berhasil diubah!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal mengubah data kasir: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk delete kasir
if (isset($_POST['hapuskasir'])) {
    $idkasir = mysqli_real_escape_string($c, $_POST['id']);
    $delete = mysqli_query($c, "DELETE FROM datakasir WHERE idkasir='$idkasir'");
    if ($delete) {
        echo "<script>alert('Data kasir berhasil dihapus!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal menghapus data kasir: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk edit user
if (isset($_POST['edituser'])) {
    $iduser = mysqli_real_escape_string($c, $_POST['id']);
    $username = mysqli_real_escape_string($c, $_POST['nama']);
    $update_query = "UPDATE user SET username='$username' WHERE iduser='$iduser'";
    if (!empty($_POST['password'])) {
        $new_password_hashed = password_hash(mysqli_real_escape_string($c, $_POST['password']), PASSWORD_DEFAULT);
        $update_query = "UPDATE user SET username='$username', password='$new_password_hashed' WHERE iduser='$iduser'";
    }
    $update = mysqli_query($c, $update_query);
    if ($update) {
        echo "<script>alert('Data user berhasil diubah!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal mengubah data user: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk delete user
if (isset($_POST['hapususer'])) {
    $iduser = mysqli_real_escape_string($c, $_POST['id']);
    $delete = mysqli_query($c, "DELETE FROM user WHERE iduser='$iduser'");
    if ($delete) {
        echo "<script>alert('Data user berhasil dihapus!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal menghapus data user: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk edit dataowner
if (isset($_POST['editadmin'])) {
    $idowner = mysqli_real_escape_string($c, $_POST['id']);
    $username = mysqli_real_escape_string($c, $_POST['nama']);
    $update_query = "UPDATE dataowner SET username='$username' WHERE idowner='$idowner'";
    if (!empty($_POST['password'])) {
        $new_password_hashed = password_hash(mysqli_real_escape_string($c, $_POST['password']), PASSWORD_DEFAULT);
        $update_query = "UPDATE dataowner SET username='$username', password='$new_password_hashed' WHERE idowner='$idowner'";
    }
    $update = mysqli_query($c, $update_query);
    if ($update) {
        echo "<script>alert('Data admin berhasil diubah!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal mengubah data admin: " . mysqli_error($c) . "');</script>";
    }
}

// Logika untuk delete dataowner
if (isset($_POST['hapusowner'])) {
    $idowner = mysqli_real_escape_string($c, $_POST['id']);
    $delete = mysqli_query($c, "DELETE FROM dataowner WHERE idowner='$idowner'");
    if ($delete) {
        echo "<script>alert('Data admin berhasil dihapus!'); window.location.href='manajemen_user.php'</script>";
    } else {
        echo "<script>alert('Gagal menghapus data admin: " . mysqli_error($c) . "');</script>";
    }
}

?>