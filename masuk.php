<?php
require 'ceklogin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Menu Masuk</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">aplikasi kasir</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    </nav>

    <div id="layoutSidenav">
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
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Start Bootstrap
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1 class="mt-4">Data menu masuk</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">selamat datang</li>
                    </ol>

                    <!-- Button to Open the Modal -->
                    <button type="button" class="btn btn-info mb-4" data-toggle="modal" data-target="#modalTambahMasuk">
                        Tambah menu masuk
                    </button>

                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            Data menu masuk
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>nama menu</th>
                                            <th>jumlah</th>
                                            <th>tanggal</th>
                                            <th>aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $get = mysqli_query($c, "
                                        SELECT m.idmasuk, m.idmakanan, mk.namamakanan, mk.deskripsi, m.qty, m.tanggal
                                        FROM masuk m
                                        JOIN makanan mk ON m.idmakanan = mk.idmakanan
                                        ORDER BY m.tanggal DESC, m.idmasuk DESC
                                    ");
                                    if (!$get) {
                                        die("SQL Error: " . mysqli_error($c));
                                    }

                                    $i = 1;
                                    while($p = mysqli_fetch_array($get)){
                                        $namamakanan = $p['namamakanan'];
                                        $deskripsi   = $p['deskripsi'];
                                        $qty         = $p['qty'];
                                        $idmasuk     = $p['idmasuk'];
                                        $idmakanan   = $p['idmakanan'];
                                        $tanggal     = $p['tanggal'];
                                    ?>
                                        <tr>
                                            <td><?=$i++;?></td>
                                            <td><?=htmlspecialchars($namamakanan);?>: <?=htmlspecialchars($deskripsi);?></td>
                                            <td><?=intval($qty);?></td>
                                            <td><?=htmlspecialchars($tanggal);?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?=$idmasuk;?>">Edit</button>
                                                <a href="function.php?hapusmasuk=<?=$idmasuk;?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data masuk ini?')">Delete</a>
                                            </td>
                                        </tr>

                                        <!-- Modal EDIT -->
                                            <div class="modal fade" id="modalEdit<?=$idmasuk;?>" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel<?=$idmasuk;?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                <form method="post" action="function.php">
                                                    <div class="modal-header">
                                                    <h5 class="modal-title" id="modalEditLabel<?=$idmasuk;?>">Edit Menu Masuk</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    </div>
                                                    <div class="modal-body">
                                                    <p><strong><?=htmlspecialchars($namamakanan);?></strong></p>
                                                    <div class="form-group">
                                                        <label>Jumlah (qty)</label>
                                                        <input type="number" name="qty" min="1" class="form-control" value="<?=intval($qty);?>" required>
                                                    </div>
                                                    <input type="hidden" name="idmasuk" value="<?=$idmasuk;?>">
                                                    <input type="hidden" name="idmakanan" value="<?=$idmakanan;?>">
                                                    <input type="hidden" name="old_qty" value="<?=$qty;?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                    <button type="submit" name="editmasuk" class="btn btn-success">Simpan</button>
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                                                    </div>
                                                </form>
                                                </div>
                                            </div>
                                            </div>


                                    <?php
                                    } // end while
                                    ?>
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
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a> &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <!-- JS lib -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

</body>

<!-- Modal Tambah Menu Masuk -->
<div class="modal fade" id="modalTambahMasuk" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="function.php">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Menu Masuk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <label for="idmakanan">Pilih menu</label>
                    <select name="idmakanan" id="idmakanan" class="form-control" required>
                        <?php
                        $getmakanan = mysqli_query($c, "SELECT * FROM makanan ORDER BY namamakanan");
                        while ($mk = mysqli_fetch_array($getmakanan)) {
                            echo '<option value="'.intval($mk['idmakanan']).'">'.htmlspecialchars($mk['namamakanan']).' - '.htmlspecialchars($mk['deskripsi']).' (stock: '.intval($mk['stock']).')</option>';
                        }
                        ?>
                    </select>
                    <div class="form-group mt-3">
                        <label>Jumlah</label>
                        <input type="number" name="qty" class="form-control" placeholder="Jumlah" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" name="menumasuk">Submit</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

</html>
