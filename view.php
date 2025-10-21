<?php
require 'ceklogin.php';

if (isset($_GET['idp'])) {
    $idp = $_GET['idp'];

    $ambilnamapelanggan = mysqli_query(
        $c,
        "SELECT * FROM pesanan p
         JOIN pelanggan pl ON p.idpelanggan = pl.id_pelanggan
         WHERE p.idpesanan = '$idp'"
    );
    
    $np = mysqli_fetch_array($ambilnamapelanggan);
    
    if ($np) {
        $namapel = $np['namapelanggan'];
    } else {
        $namapel = "Tidak ditemukan";
    }
} else {
    header('location:index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Data Pesanan</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">aplikasi kasir</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
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
                    <h1 class="mt-4">Nama pelanggan: <?= $namapel; ?></h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Selamat datang</li>
                    </ol>

                    <!-- Button to Open the Modal -->
                    <button type="button" class="btn btn-info mb-4" data-toggle="modal" data-target="#myModal">
                        Tambah menu
                    </button>

                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            Data pesanan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama makanan</th>
                                            <th>Harga satuan</th>
                                            <th>Jumlah</th>
                                            <th>Sub-total</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
                                        $get = mysqli_query($c, "
                                            SELECT dp.*, m.namamakanan, m.harga, m.deskripsi, p.tanggal, pel.namapelanggan, pel.alamat
                                            FROM detailpesanan dp
                                            JOIN makanan m ON dp.idmakanan = m.idmakanan
                                            JOIN pesanan p ON dp.idpesanan = p.idpesanan
                                            JOIN pelanggan pel ON p.idpelanggan = pel.id_pelanggan
                                            WHERE dp.idpesanan = '$idp'
                                        ");

                                        if (!$get) {
                                            die("Query error: " . mysqli_error($c));
                                        }

                                        $i = 1;
                                        $total = 0; // 🔥 Tambahan: total keseluruhan
                                        while ($p = mysqli_fetch_array($get)) {
                                            $idmakanan = $p['idmakanan'];
                                            $iddp = $p['iddetailpesanan'];
                                            $qty  = $p['qty'];
                                            $harga = $p['harga'];
                                            $namamakanan = $p['namamakanan'];
                                            $deskripsi = $p['deskripsi'];
                                            $subtotal  = $qty * $harga;
                                            $total += $subtotal; // 🔥 Tambahan: akumulasi total
                                        ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= $namamakanan; ?></td>
                                                <td>Rp<?= number_format($harga); ?></td>
                                                <td><?= number_format($qty); ?></td>
                                                <td>Rp<?= number_format($subtotal); ?></td>
                                                <td>
                                                    <!-- Tombol Edit -->
                                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?= $iddp; ?>">
                                                        Edit
                                                    </button>

                                                    <!-- Tombol Hapus -->
                                                    <a href="function.php?hapusdetail=<?= $iddp; ?>&idp=<?= $idp; ?>" 
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin hapus item ini?')">Hapus</a>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="edit<?= $iddp; ?>" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Pesanan</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <form method="post" action="function.php">
                                                            <div class="modal-body">
                                                                <label>Nama Makanan</label>
                                                                <input type="text" class="form-control" value="<?= $namamakanan; ?>" readonly>

                                                                <label class="mt-3">Jumlah</label>
                                                                <input type="number" name="qty" class="form-control" value="<?= $qty; ?>" min="1" required>

                                                                <input type="hidden" name="iddetailpesanan" value="<?= $iddp; ?>">
                                                                <input type="hidden" name="idp" value="<?= $idp; ?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-success" name="editmakananpesanan">Simpan</button>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php 
                                        }; // end while
                                        ?>
                                        </tbody>

                                        <!-- 🔥 Tambahan: Total keseluruhan -->
                                        <tfoot>
                                            <tr style="font-weight:bold; background-color:#f8f9fa;">
                                                <td colspan="4" align="right">Total Keseluruhan:</td>
                                                <td colspan="2">Rp<?= number_format($total); ?></td>
                                            </tr>
                                        </tfoot>

                                </table>
                                <!-- 🔥 Form Pembayaran -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-money-bill-wave"></i> Pembayaran
                                        </div>
                                        <div class="card-body">
                                            <form method="post" action="function.php">
                                                <div class="form-group">
                                                    <label>Total Tagihan</label>
                                                    <input type="text" class="form-control" id="total" value="<?= $total; ?>" readonly>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <label>Uang Bayar</label>
                                                    <input type="number" class="form-control" name="bayar" id="bayar" placeholder="Masukkan nominal uang" required>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <label>Kembalian</label>
                                                    <input type="text" class="form-control" id="kembalian" readonly>
                                                </div>

                                                <input type="hidden" name="idp" value="<?= $idp; ?>">
                                                
                                                <button type="submit" class="btn btn-success mt-3" name="bayarpesanan">Simpan Pembayaran</button>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- 🔥 Tabel Riwayat Pembayaran -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <i class="fas fa-receipt"></i> Riwayat Pembayaran
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Bayar</th>
                    <th>Kembalian</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $getbayar = mysqli_query($c, "SELECT * FROM pembayaran WHERE idpesanan='$idp'");
                if (mysqli_num_rows($getbayar) > 0) {
                    while ($b = mysqli_fetch_assoc($getbayar)) {
                        $idpembayaran = $b['idpembayaran'];
                        $total = $b['total'];
                        $bayar = $b['bayar'];
                        $kembalian = $b['kembalian'];
                        $tanggal = $b['tanggal'];
                ?>
                        <tr>
                            <td><?= $tanggal; ?></td>
                            <td>Rp<?= number_format($total); ?></td>
                            <td>Rp<?= number_format($bayar); ?></td>
                            <td>Rp<?= number_format($kembalian); ?></td>
                            <td>
                                <!-- Tombol Edit -->
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editbayar<?= $idpembayaran; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Tombol Hapus -->
                                <a href="function.php?hapuspembayaran=<?= $idpembayaran; ?>&idp=<?= $idp; ?>"
                                   onclick="return confirm('Yakin hapus pembayaran ini?')"
                                   class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit Pembayaran -->
                        <div class="modal fade" id="editbayar<?= $idpembayaran; ?>" tabindex="-1" role="dialog" aria-labelledby="editLabel<?= $idpembayaran; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title" id="editLabel<?= $idpembayaran; ?>">Edit Pembayaran</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="post" action="function.php">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Total</label>
                                                <input type="number" name="total" class="form-control" value="<?= $total; ?>" required readonly>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label>Bayar</label>
                                                <input type="number" name="bayar" class="form-control" id="bayar<?= $idpembayaran; ?>" value="<?= $bayar; ?>" required oninput="hitungKembalian(<?= $idpembayaran; ?>)">
                                            </div>
                                            <div class="form-group mt-3">
                                                <label>Kembalian</label>
                                                <input type="number" name="kembalian" class="form-control" id="kembalian<?= $idpembayaran; ?>" value="<?= $kembalian; ?>" readonly>
                                            </div>

                                            <input type="hidden" name="idpembayaran" value="<?= $idpembayaran; ?>">
                                            <input type="hidden" name="idp" value="<?= $idp; ?>">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success" name="editpembayaran">Simpan Perubahan</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' align='center'>Belum ada pembayaran</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 🔧 Script Otomatis Hitung Kembalian -->
<script>
function hitungKembalian(id) {
    const total = parseInt(document.querySelector(`#editbayar${id} input[name='total']`).value) || 0;
    const bayar = parseInt(document.getElementById(`bayar${id}`).value) || 0;
    const kembalianField = document.getElementById(`kembalian${id}`);
    const hasil = bayar - total;

    kembalianField.value = hasil >= 0 ? hasil : 0;
}
</script>


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
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/datatables-demo.js"></script>
    <script>
document.getElementById('bayar').addEventListener('input', function() {
    const total = parseInt(document.getElementById('total').value) || 0;
    const bayar = parseInt(this.value) || 0;
    const kembalianInput = document.getElementById('kembalian');

    if (bayar < total) {
        kembalianInput.value = 'Uang kurang Rp' + (total - bayar).toLocaleString('id-ID');
        kembalianInput.style.color = 'red';
    } else {
        const kembalian = bayar - total;
        kembalianInput.value = 'Rp' + kembalian.toLocaleString('id-ID');
        kembalianInput.style.color = 'black';
    }
});
</script>


</body>

<!-- Modal Tambah Menu -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
        
            <div class="modal-header">
                <h4 class="modal-title">Tambah menu</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <form method="post" action="function.php">
                <div class="modal-body">
                    <label for="idmakanan">Pilih menu</label>
                    <select name="idmakanan" class="form-control" required>
                        <?php
                        $getmakanan = mysqli_query($c, "SELECT * FROM makanan");
                        if (mysqli_num_rows($getmakanan) == 0) {
                            echo '<option disabled>Tidak ada menu tersedia</option>';
                        } else {
                            while ($pl = mysqli_fetch_array($getmakanan)) {
                                $namamakanan = $pl['namamakanan'];
                                $deskripsi   = $pl['deskripsi'];
                                $idmakanan   = $pl['idmakanan'];
                                $stock       = $pl['stock'];

                                echo '<option value="' . $idmakanan . '">' . $namamakanan . ' - ' . $deskripsi . ' (stock: ' . $stock . ')</option>';
                            }
                        }
                        ?>
                    </select>

                    <input type="number" name="qty" class="form-control mt-4" placeholder="Jumlah" min="1" required>
                    <input type="hidden" name="idp" value="<?= $idp; ?>">
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" name="addmakanan">Submit</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </form>     
        </div>
    </div>
</div>
</html>
