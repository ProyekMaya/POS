<?php
require 'function.php'; // Pastikan file function.php ada dan benar

// Pastikan session_start() sudah dipanggil di function.php atau di awal file ini
if(isset($_SESSION['login']) && $_SESSION['login'] === 'True'){
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'kasir') {
            header('location:index.php'); // Dashboard Kasir
        } elseif ($_SESSION['role'] === 'owner') {
            header('location:owner/index_owner.php'); // Dashboard Owner
        } elseif ($_SESSION['role'] === 'admin') { // Tambahkan kondisi untuk Admin jika diperlukan
            header('location:admin/index_admin.php'); // Dashboard Admin
        }
    }
    exit();
}

// Menentukan judul form login
$loginTitle = "Login Aplikasi"; // Sekarang selalu ini

// Tidak ada lagi logika penanganan error di sini, semua dilakukan di function.php
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

        <style>
            .gradient-bg {
                min-height: 100vh;
                background: linear-gradient(135deg, #FFE5B4, #FFF8E7);
                background-size: cover;
                background-repeat: no-repeat;
            }
        </style>

    </head>
    <body class="gradient-bg">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-6 col-lg-4">

                                <div class="text-center mt-3">
                                    <img src="img/logo.png" alt="Logo" style="max-width: 250px;">
                                </div>

                                <div class="card shadow-sm border-0 rounded-lg mt-3" style="font-size: 1rem;">
                                    <div class="card-header py-2">
                                        <h4 class="text-center fw-light fw-semibold my-2"><?= $loginTitle; ?></h4>
                                    </div>
                                    <div class="card-body px-4">

                                        <form method="post" action="function.php"> <div class="mb-3">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-user"></i></span>
                                                    <div class="form-floating flex-grow-1">
                                                        <input class="form-control form-control-sm" id="inputUsername" name="username" type="text" placeholder="Enter username" style="font-size: 1rem;" required />
                                                        <label for="inputUsername">Username</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-lock"></i></span>
                                                    <div class="form-floating flex-grow-1">
                                                        <input class="form-control form-control-sm" id="inputPassword" name="password" type="password" placeholder="Enter password" required />
                                                        <label for="inputPassword">Password</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 mb-2">
                                                <button type="submit" name="login" class="btn btn-primary btn-sm w-100">Login</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </main>

            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>