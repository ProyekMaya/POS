<?php
    require '../ceklogin.php';

    $jumlah_datakasir = mysqli_query($c,"SELECT * FROM datakasir");
    $h1 = mysqli_num_rows($jumlah_datakasir);

    $jumlah_user = mysqli_query($c,"SELECT * FROM user");
    $h2 = mysqli_num_rows($jumlah_user);

    $jumlah_datasadmin = mysqli_query($c,"SELECT * FROM datasadmin");
    $h3 = mysqli_num_rows($jumlah_datasadmin);

    $h4 = $h1 + $h2 + $h3;

    $displayName = "Guest";
    $roleName = "";

    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin' && isset($_SESSION['username_admin'])) {
            $roleName = "Admin";
            $displayName = $_SESSION['username_admin'];
        } elseif ($_SESSION['role'] == 'kasir' && isset($_SESSION['namakasir_kasir'])) {
            $roleName = "Kasir";
            $displayName = $_SESSION['namakasir_kasir'];
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
                                <strong style="font-size: 1.1em;"><?php echo $roleName . ": " . $displayName; ?></strong>
                            </div>

                            <?php
                            $current_page = basename($_SERVER['PHP_SELF']);
                            ?>

                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'index_admin.php') ? 'active' : '' ?>" href="index_admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-grip-horizontal"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link w-100 text-start ps-4<?= ($current_page == 'stock_admin.php') ? 'active' : '' ?>" href="stock_admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-archive"></i></div>
                                Stok Barang
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'manajemen_user.php') ? 'active' : '' ?>" href="manajemen_user.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                                Manajemen User
                            </a>
                            <a class="nav-link w-100 text-start ps-4 <?= ($current_page == 'laporan_admin.php') ? 'active' : '' ?>" href="laporan_admin.php">
                                <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                                Laporan
                            </a>
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
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Data User</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active"></li>
                        </ol>
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">Jumlah User: <?=$h4;?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Button To Open The Modal -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#myModalKasir">
                                Tambah Kasir
                            </button>
                            <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#myModalUser">
                                Tambah Admin
                            </button>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#myModalAdmin">
                                Tambah Super Admin
                            </button>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data User
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Role</th>
                                            <th>No Telepon</th>
                                            <th>Alamat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Query menggunakan UNION ALL untuk menggabungkan data dari ketiga tabel
                                        // Penting: Setiap SELECT harus memiliki jumlah kolom dan tipe data yang sama
                                        // Kita akan menggunakan NULL untuk kolom yang tidak ada di tabel tertentu
                                        $get_all_entities = mysqli_query($c, "
                                            SELECT
                                                idkasir AS id_entity,
                                                namakasir AS nama_entity,
                                                'Kasir' AS role_entity,
                                                notelp AS notelp_entity,
                                                alamat AS alamat_entity,
                                                'kasir' AS type_entity
                                            FROM datakasir
                                            UNION ALL
                                            SELECT
                                                iduser AS id_entity,
                                                username AS nama_entity,
                                                'Admin' AS role_entity,
                                                NULL AS notelp_entity, -- User tidak punya no.telp/alamat
                                                NULL AS alamat_entity,
                                                'user' AS type_entity
                                            FROM user
                                            UNION ALL
                                            SELECT
                                                idsadmin AS id_entity,
                                                username AS nama_entity,
                                                'Super Admin' AS role_entity,
                                                NULL AS notelp_entity, -- Admin tidak punya no.telp/alamat
                                                NULL AS alamat_entity,
                                                'admin' AS type_entity
                                            FROM datasadmin
                                            ORDER BY role_entity, nama_entity -- Opsional: Urutkan untuk tampilan yang lebih rapi
                                        ");

                                        // Periksa jika query gagal
                                        if (!$get_all_entities) {
                                            die("Error mengambil data: " . mysqli_error($c));
                                        }

                                        $i = 1;

                                        // Loop untuk menampilkan semua data
                                        while ($p = mysqli_fetch_array($get_all_entities)) {
                                            $id_entity = $p['id_entity'];
                                            $nama_entity = $p['nama_entity'];
                                            $role_entity = $p['role_entity'];
                                            $notelp_entity = $p['notelp_entity'];
                                            $alamat_entity = $p['alamat_entity'];
                                            $type_entity = $p['type_entity']; // 'kasir', 'user', atau 'admin'
                                        ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= htmlspecialchars($nama_entity); ?></td>
                                                <td><?= htmlspecialchars($role_entity); ?></td>
                                                <td><?= htmlspecialchars($notelp_entity ?? '-'); ?></td> <td><?= htmlspecialchars($alamat_entity ?? '-'); ?></td>   <td>
                                                    <button type="button" class="btn btn-warning me-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal"
                                                        data-id="<?= htmlspecialchars($id_entity); ?>"
                                                        data-name="<?= htmlspecialchars($nama_entity); ?>"
                                                        data-role="<?= htmlspecialchars($role_entity); ?>"
                                                        data-notelp="<?= htmlspecialchars($notelp_entity ?? ''); ?>"
                                                        data-alamat="<?= htmlspecialchars($alamat_entity ?? ''); ?>"
                                                        data-type="<?= htmlspecialchars($type_entity); ?>">
                                                        Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal"
                                                        data-id="<?= htmlspecialchars($id_entity); ?>"
                                                        data-name="<?= htmlspecialchars($nama_entity); ?>"
                                                        data-type="<?= htmlspecialchars($type_entity); ?>">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        } // end of while loop
                                        ?>
                                    </tbody>
                                </table>

                            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Ubah Data</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="post" id="editForm">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" id="editId">
                                                <input type="hidden" name="type" id="editType">

                                                <div class="mb-3">
                                                    <label for="editNama" class="form-label">Nama</label>
                                                    <input type="text" class="form-control" id="editNama" name="nama" required>
                                                </div>

                                                <div class="mb-3" id="editNoTelpDiv">
                                                    <label for="editNoTelp" class="form-label">No Telepon</label>
                                                    <input type="text" class="form-control" id="editNoTelp" name="notelp">
                                                </div>

                                                <div class="mb-3" id="editAlamatDiv">
                                                    <label for="editAlamat" class="form-label">Alamat</label>
                                                    <textarea class="form-control" id="editAlamat" name="alamat"></textarea>
                                                </div>
                                                </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary" id="submitEdit">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="post" id="deleteForm">
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus <strong id="deleteEntityName"></strong> (<span id="deleteEntityType"></span>)?
                                                <input type="hidden" name="id" id="deleteId">
                                                <input type="hidden" name="type" id="deleteType">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger" id="confirmDelete">Hapus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="myModalKasir" tabindex="-1" aria-labelledby="myModalKasirLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myModalKasirLabel">Tambah Data Kasir</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="text" name="namakasir" class="form-control" placeholder="Nama Kasir" required>
                                                <input type="text" name="notelp" class="form-control mt-2" placeholder="No Telepon">
                                                <input type="text" name="alamat" class="form-control mt-2" placeholder="Alamat">
                                                <input type="password" name="password" class="form-control mt-2" placeholder="Password" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success" name="tambahkasir">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="myModalUser" tabindex="-1" aria-labelledby="myModalUserLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myModalUserLabel">Tambah Data User</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                                                <input type="password" name="password" class="form-control mt-2" placeholder="Password" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-info" name="tambahuser">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal fade" id="myModalAdmin" tabindex="-1" aria-labelledby="myModalAdminLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myModalAdminLabel">Tambah Data Admin</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                                                <input type="password" name="password" class="form-control mt-2" placeholder="Password" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary" name="tambahadmin">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var editModal = document.getElementById('editModal');
                                    editModal.addEventListener('show.bs.modal', function (event) {
                                        var button = event.relatedTarget; // Button that triggered the modal
                                        var id = button.getAttribute('data-id');
                                        var name = button.getAttribute('data-name');
                                        var role = button.getAttribute('data-role');
                                        var notelp = button.getAttribute('data-notelp');
                                        var alamat = button.getAttribute('data-alamat');
                                        var type = button.getAttribute('data-type');

                                        var modalTitle = editModal.querySelector('.modal-title');
                                        var editIdInput = editModal.querySelector('#editId');
                                        var editTypeInput = editModal.querySelector('#editType');
                                        var editNamaInput = editModal.querySelector('#editNama');
                                        var editNoTelpDiv = editModal.querySelector('#editNoTelpDiv');
                                        var editAlamatDiv = editModal.querySelector('#editAlamatDiv');
                                        var editNoTelpInput = editModal.querySelector('#editNoTelp');
                                        var editAlamatInput = editModal.querySelector('#editAlamat');
                                        var editForm = editModal.querySelector('#editForm');
                                        var submitEditButton = editModal.querySelector('#submitEdit');


                                        modalTitle.textContent = 'Ubah Data ' + name + ' (' + role + ')';
                                        editIdInput.value = id;
                                        editTypeInput.value = type;
                                        editNamaInput.value = name;

                                        // Sesuaikan tampilan form berdasarkan jenis entitas
                                        if (type === 'kasir') {
                                            editNoTelpDiv.style.display = 'block';
                                            editAlamatDiv.style.display = 'block';
                                            editNoTelpInput.value = notelp;
                                            editAlamatInput.value = alamat;
                                            // Atur action form untuk kasir
                                            editForm.setAttribute('action', ''); // Biarkan kosong agar POST ke halaman ini
                                            submitEditButton.setAttribute('name', 'editkasir'); // Nama tombol submit untuk PHP
                                        } else {
                                            editNoTelpDiv.style.display = 'none'; // Sembunyikan untuk user/admin
                                            editAlamatDiv.style.display = 'none'; // Sembunyikan untuk user/admin
                                            editNoTelpInput.value = ''; // Kosongkan nilai
                                            editAlamatInput.value = ''; // Kosongkan nilai
                                            // Atur action form dan nama tombol submit sesuai tipe
                                            if (type === 'user') {
                                                submitEditButton.setAttribute('name', 'edituser');
                                            } else if (type === 'admin') {
                                                submitEditButton.setAttribute('name', 'editadmin');
                                            }
                                            editForm.setAttribute('action', ''); // Biarkan kosong agar POST ke halaman ini
                                        }
                                    });

                                    var deleteModal = document.getElementById('deleteModal');
                                    deleteModal.addEventListener('show.bs.modal', function (event) {
                                        var button = event.relatedTarget;
                                        var id = button.getAttribute('data-id');
                                        var name = button.getAttribute('data-name');
                                        var type = button.getAttribute('data-type');

                                        var deleteEntityName = deleteModal.querySelector('#deleteEntityName');
                                        var deleteEntityType = deleteModal.querySelector('#deleteEntityType');
                                        var deleteIdInput = deleteModal.querySelector('#deleteId');
                                        var deleteTypeInput = deleteModal.querySelector('#deleteType');
                                        var deleteForm = deleteModal.querySelector('#deleteForm');
                                        var confirmDeleteButton = deleteModal.querySelector('#confirmDelete');

                                        deleteEntityName.textContent = name;
                                        deleteEntityType.textContent = type;
                                        deleteIdInput.value = id;
                                        deleteTypeInput.value = type;

                                        // Atur nama tombol submit sesuai tipe
                                        if (type === 'kasir') {
                                            confirmDeleteButton.setAttribute('name', 'hapuskasir');
                                        } else if (type === 'user') {
                                            confirmDeleteButton.setAttribute('name', 'hapususer');
                                        } else if (type === 'admin') {
                                            confirmDeleteButton.setAttribute('name', 'hapusadmin');
                                        }
                                        deleteForm.setAttribute('action', ''); // Biarkan kosong agar POST ke halaman ini
                                    });
                                });
                            </script>
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
    </body>
</html>
