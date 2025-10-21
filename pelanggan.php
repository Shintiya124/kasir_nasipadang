<?php
require 'function.php';

// ==================== Tambah pelanggan ====================
if (isset($_POST['tambahpelanggan'])) {
    $namapelanggan = mysqli_real_escape_string($c, $_POST['namapelanggan']);
    $telepon = mysqli_real_escape_string($c, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($c, $_POST['alamat']);

    $sql = "INSERT INTO pelanggan (namapelanggan, alamat, telepon) 
            VALUES ('$namapelanggan','$alamat','$telepon')";
    $tambah = mysqli_query($c, $sql);

    if ($tambah) {
        echo '<script>alert("Berhasil menambah pelanggan baru");window.location.href="pelanggan.php"</script>';
    } else {
        echo '<script>alert("Gagal menambah pelanggan baru: ' . mysqli_error($c) . '");window.location.href="pelanggan.php"</script>';
    }
}

// ==================== Edit pelanggan ====================
if (isset($_POST['editpelanggan'])) {
    $id = $_POST['id_pelanggan'];
    $namapelanggan = mysqli_real_escape_string($c, $_POST['namapelanggan']);
    $telepon = mysqli_real_escape_string($c, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($c, $_POST['alamat']);

    $edit = mysqli_query($c, "UPDATE pelanggan 
                              SET namapelanggan='$namapelanggan', telepon='$telepon', alamat='$alamat' 
                              WHERE id_pelanggan='$id'");

    if ($edit) {
        echo '<script>alert("Data pelanggan berhasil diupdate");window.location.href="pelanggan.php"</script>';
    } else {
        echo '<script>alert("Gagal update: ' . mysqli_error($c) . '");window.location.href="pelanggan.php"</script>';
    }
}

// ==================== Hapus pelanggan ====================
if (isset($_POST['hapuspelanggan'])) {
    $id = $_POST['id_pelanggan'];

    $hapus = mysqli_query($c, "DELETE FROM pelanggan WHERE id_pelanggan='$id'");

    if ($hapus) {
        echo '<script>alert("Data pelanggan berhasil dihapus");window.location.href="pelanggan.php"</script>';
    } else {
        echo '<script>alert("Gagal hapus: ' . mysqli_error($c) . '");window.location.href="pelanggan.php"</script>';
    }
}

// ==================== Hitung jumlah pelanggan ====================
$h1 = mysqli_query($c, "SELECT * FROM pelanggan");
$h2 = mysqli_num_rows($h1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Data Pelanggan</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">

    <!-- Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Aplikasi Kasir</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <div id="layoutSidenav">
        <!-- Sidebar -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                <div class="nav">
                        <div class="sb-sidenav-menu-heading">menu</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            order
                        </a>
                        <a class="nav-link" href="stock.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                             menu
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            stok menu
                        </a>
                        <a class="nav-link" href="pelanggan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            kelola pelanggan
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                            logout
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1 class="mt-4">Data Pelanggan</h1>
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">Jumlah pelanggan: <?= $h2; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Button Tambah -->
                    <button type="button" class="btn btn-info mb-4" data-toggle="modal" data-target="#myModal">
                        Tambah Pelanggan
                    </button>

                    <!-- Table -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-table me-1"></i> Data Pelanggan</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pelanggan</th>
                                            <th>No Telepon</th>
                                            <th>Alamat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $get = mysqli_query($c, "SELECT * FROM pelanggan");
                                    $i = 1;
                                    while ($p = mysqli_fetch_array($get)) {
                                        $idpel = $p['id_pelanggan'];
                                        $namapelanggan = $p['namapelanggan'];
                                        $notelp = $p['telepon'];
                                        $alamat = $p['alamat'];
                                    ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= $namapelanggan; ?></td>
                                            <td><?= $notelp; ?></td>
                                            <td><?= $alamat; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpel; ?>">Edit</button>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpel; ?>">Delete</button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal Edit -->
                                        <div class="modal fade" id="edit<?= $idpel; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Ubah <?= $namapelanggan; ?></h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post" action="pelanggan.php">
                                                        <div class="modal-body">
                                                            <input type="text" name="namapelanggan" class="form-control" value="<?= $namapelanggan; ?>" required>
                                                            <input type="text" name="telepon" class="form-control mt-2" value="<?= $notelp; ?>" required>
                                                            <input type="text" name="alamat" class="form-control mt-2" value="<?= $alamat; ?>" required>
                                                            <input type="hidden" name="id_pelanggan" value="<?= $idpel; ?>">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="editpelanggan">Submit</button>
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Delete -->
                                        <div class="modal fade" id="delete<?= $idpel; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Hapus <?= $namapelanggan; ?></h4>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post" action="pelanggan.php"> 
                                                        <div class="modal-body">
                                                            Apakah Anda yakin ingin menghapus pelanggan ini?
                                                            <input type="hidden" name="id_pelanggan" value="<?= $idpel; ?>">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success" name="hapuspelanggan">Submit</button>
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Data Pelanggan</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" action="pelanggan.php">
                    <div class="modal-body">
                        <input type="text" name="namapelanggan" class="form-control" placeholder="Nama pelanggan" required>
                        <input type="text" name="telepon" class="form-control mt-2" placeholder="No telepon" required>
                        <input type="text" name="alamat" class="form-control mt-2" placeholder="Alamat" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" name="tambahpelanggan">Submit</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/datatables-demo.js"></script>
</body>
</html>
