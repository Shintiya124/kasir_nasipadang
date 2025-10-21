<?php
require 'ceklogin.php';

// Hitung jumlah menu
$h1 = mysqli_query($c, "SELECT * FROM makanan");
$h2 = mysqli_num_rows($h1); // jumlah menu
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Stock Barang</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Aplikasi Kasir</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Menu</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div> Order
                        </a>
                        <a class="nav-link" href="stock.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-utensils"></i></div> Menu
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div> Stok Menu
                        </a>
                        <a class="nav-link" href="pelanggan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div> Kelola Pelanggan
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div> Logout
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Start Bootstrap
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1 class="mt-4">Data Menu</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Selamat Datang</li>
                    </ol>

                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">Jumlah Menu: <?= $h2; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol tambah menu -->
                    <button type="button" class="btn btn-info mb-4" data-toggle="modal" data-target="#myModal">
                        Tambah Menu Baru
                    </button>

                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i> Data Menu
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Menu</th>
                                            <th>Deskripsi</th>
                                            <th>Stok</th>
                                            <th>Foto</th>
                                            <th>Harga</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $get = mysqli_query($c, "SELECT * FROM makanan");
                                        $i = 1;
                                        while ($p = mysqli_fetch_array($get)) {
                                            $namamakanan = $p['namamakanan'];
                                            $deskripsi = $p['deskripsi'];
                                            $harga = $p['harga'];
                                            $stock = $p['stock'];
                                            $idmakanan = $p['idmakanan'];
                                            $foto = $p['foto'];
                                        ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= htmlspecialchars($namamakanan); ?></td>
                                                <td><?= htmlspecialchars($deskripsi); ?></td>
                                                <td><?= $stock; ?></td>
                                                <td>
                                                    <img src="js/image/<?= $foto ? $foto : 'noimage.png'; ?>" width="60" height="60"
                                                        style="object-fit:cover;border-radius:8px;"
                                                        onerror="this.onerror=null;this.src='js/image/noimage.png';">
                                                </td>
                                                <td>Rp<?= number_format($harga); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?= $idmakanan; ?>">Edit</button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete<?= $idmakanan; ?>">Delete</button>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="edit<?= $idmakanan; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Ubah <?= htmlspecialchars($namamakanan); ?></h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>

                                                        <form method="post" action="function.php" enctype="multipart/form-data">
                                                            <div class="modal-body">
                                                                <input type="text" name="namamenu" class="form-control" placeholder="Nama menu" value="<?= htmlspecialchars($namamakanan); ?>" required>
                                                                <input type="text" name="deskripsi" class="form-control mt-2" placeholder="Deskripsi" value="<?= htmlspecialchars($deskripsi); ?>">
                                                                <input type="number" name="harga" class="form-control mt-2" placeholder="Harga menu" value="<?= $harga; ?>" required>
                                                                <input type="number" name="stock" class="form-control mt-2" placeholder="Stok menu" value="<?= $stock; ?>" required>
                                                                
                                                                <small>Gambar saat ini:</small><br>
                                                                <img src="js/image/<?= $foto ? $foto : 'noimage.png'; ?>" width="70" height="70" style="object-fit:cover;border-radius:8px;"><br>
                                                                <input type="file" name="gambar" class="form-control mt-2" accept="image/*">
                                                                
                                                                <input type="hidden" name="idm" value="<?= $idmakanan; ?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-success" name="editmenu">Simpan</button>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Delete -->
                                            <div class="modal fade" id="delete<?= $idmakanan; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Hapus <?= htmlspecialchars($namamakanan); ?>?</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <form method="post" action="function.php">
                                                            <div class="modal-body">
                                                                Apakah Anda yakin ingin menghapus menu ini?
                                                                <input type="hidden" name="idm" value="<?= $idmakanan; ?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-danger" name="hapusmenu">Hapus</button>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Aplikasi Kasir 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modal Tambah Menu -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Menu Baru</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form method="post" action="function.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="text" name="namamenu" class="form-control" placeholder="Nama menu" required>
                        <input type="text" name="deskripsi" class="form-control mt-2" placeholder="Deskripsi">
                        <input type="number" name="stock" class="form-control mt-2" placeholder="Stok awal" required>
                        <input type="number" name="harga" class="form-control mt-2" placeholder="Harga menu" required>
                        <input type="file" name="gambar" class="form-control mt-2" accept="image/*">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" name="tambahmenu">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/datatables-demo.js"></script>
</body>
</html>
