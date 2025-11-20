<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// ==================== KONEKSI DATABASE ====================
$c = mysqli_connect("localhost", "root", "", "kasir");
if (!$c) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

/**
 * Helper untuk upload gambar
 * @param string $field - nama input file (contoh 'gambar')
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function handle_upload($field = 'gambar') {
    $result = ['success' => false, 'filename' => null, 'error' => null];

    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        // tidak ada file diupload
        $result['success'] = true;
        $result['filename'] = null;
        return $result;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Upload error code: ' . $file['error'];
        return $result;
    }

    // validasi ukuran (limit 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $result['error'] = 'File terlalu besar, maksimal 5MB.';
        return $result;
    }

    // validasi tipe (image)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($mime, $allowed)) {
        $result['error'] = 'Tipe file tidak diizinkan. Hanya JPG/PNG/GIF/WEBP.';
        return $result;
    }

    $targetDir = __DIR__ . "/js/image/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // gunakan nama file yang unik
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $targetDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $result['error'] = 'Gagal memindahkan file ke folder tujuan.';
        return $result;
    }

    $result['success'] = true;
    $result['filename'] = $safeName;
    return $result;
}

/* ==================== LOGIN ==================== */
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($c, $_POST['username']);
    $password = md5($_POST['password']);

    $check  = mysqli_query($c, "SELECT * FROM user WHERE username='$username' AND password='$password'");
    $hitung = mysqli_num_rows($check);

    if ($hitung > 0) {
        $_SESSION['login'] = true;
        header('location:index.php');
        exit;
    } else {
        echo '<script>alert("Username atau Password salah"); window.location.href="login.php"</script>';
        exit;
    }
}

/* ==================== TAMBAH MENU ==================== */
if (isset($_POST['tambahmenu'])) {
    $namamakanan = mysqli_real_escape_string($c, $_POST['namamenu']);
    $deskripsi   = mysqli_real_escape_string($c, $_POST['deskripsi']);
    $stock       = intval($_POST['stock']);
    $harga       = intval($_POST['harga']);

    // handle upload
    $upload = handle_upload('gambar');
    if ($upload['success'] === false) {
        echo '<script>alert("Gagal upload gambar: ' . addslashes($upload['error']) . '"); window.location.href="stock.php";</script>';
        exit;
    }

    // jika tidak upload file, gunakan default noimage.png
    $gambar = $upload['filename'] ?? 'noimage.png';
    if ($gambar === null) $gambar = 'noimage.png';

    $sql = "INSERT INTO makanan (namamakanan, deskripsi, stock, harga, foto) 
            VALUES ('$namamakanan','$deskripsi','$stock','$harga','$gambar')";

    // NOTE: kolom foto di db saya asumsikan bernama 'foto' — ubah jika nama kolom berbeda (sebelumnya kamu pakai 'gambar' di beberapa tempat)
    $insert = mysqli_query($c, $sql);

    if ($insert) {
        header('location:stock.php');
        exit;
    } else {
        // jika gagal, hapus file yang baru diupload (jika bukan default)
        if (!empty($upload['filename']) && file_exists(__DIR__ . "/js/image/" . $upload['filename'])) {
            unlink(__DIR__ . "/js/image/" . $upload['filename']);
        }
        die("Query error: " . mysqli_error($c) . "<br>SQL: " . $sql);
    }
}

/* ==================== EDIT MENU (dengan gambar & stok) ==================== */
if (isset($_POST['editmenu'])) {
    $idm         = intval($_POST['idm']);
    $namamakanan = mysqli_real_escape_string($c, $_POST['namamenu']);
    $deskripsi   = mysqli_real_escape_string($c, $_POST['deskripsi']);
    $harga       = intval($_POST['harga']);
    $stock       = intval($_POST['stock']);

    // Ambil data lama (kolom foto)
    $q = mysqli_query($c, "SELECT foto FROM makanan WHERE idmakanan='$idm' LIMIT 1");
    $old = mysqli_fetch_assoc($q);
    $foto_lama = $old ? $old['foto'] : null;

    // handle upload (jika ada)
    $upload = handle_upload('gambar');
    if ($upload['success'] === false) {
        echo '<script>alert("Gagal upload gambar: ' . addslashes($upload['error']) . '"); window.location.href="stock.php";</script>';
        exit;
    }

    $foto_baru = $foto_lama; // default tetap gambar lama
    if (!empty($upload['filename'])) {
        $foto_baru = $upload['filename'];

        // hapus file lama jika bukan default
        if (!empty($foto_lama) && $foto_lama !== 'noimage.png' && file_exists(__DIR__ . "/js/image/" . $foto_lama)) {
            @unlink(__DIR__ . "/js/image/" . $foto_lama);
        }
    }

    $update_sql = "
        UPDATE makanan 
        SET namamakanan='" . mysqli_real_escape_string($c, $namamakanan) . "',
            deskripsi='" . mysqli_real_escape_string($c, $deskripsi) . "',
            harga='$harga',
            stock='$stock',
            foto='" . mysqli_real_escape_string($c, $foto_baru) . "'
        WHERE idmakanan='$idm'
    ";

    $update = mysqli_query($c, $update_sql);

    if ($update) {
        echo '<script>alert("Menu berhasil diperbarui"); window.location.href="stock.php";</script>';
        exit;
    } else {
        // jika gagal, hapus file yang baru diupload (jika ada)
        if (!empty($upload['filename']) && file_exists(__DIR__ . "/js/image/" . $upload['filename'])) {
            @unlink(__DIR__ . "/js/image/" . $upload['filename']);
        }
        echo '<script>alert("Gagal memperbarui menu: ' . mysqli_real_escape_string($c, mysqli_error($c)) . '"); window.location.href="stock.php";</script>';
        exit;
    }
}

/* ==================== TAMBAH PELANGGAN ==================== */
if (isset($_POST['tambahpelanggan'])) {
    $namapelanggan = mysqli_real_escape_string($c, $_POST['namapelanggan']);
    $telepon       = mysqli_real_escape_string($c, $_POST['telepon']);
    $alamat        = mysqli_real_escape_string($c, $_POST['alamat']);

    $insert = mysqli_query($c, "INSERT INTO pelanggan (namapelanggan, telepon, alamat) 
                                VALUES ('$namapelanggan', '$telepon', '$alamat')");

    if ($insert) {
        header('location:pelanggan.php');
        exit;
    } else {
        echo '<script>alert("Gagal menambah pelanggan baru"); window.location.href="pelanggan.php"</script>';
        exit;
    }
}

/* ==================== TAMBAH PESANAN ==================== */
if (isset($_POST['tambahpesanan'])) {
    $idpelanggan = intval($_POST['idpelanggan']);
    $insert = mysqli_query($c, "INSERT INTO pesanan (idpelanggan, tanggal) VALUES ($idpelanggan, NOW())");
    if ($insert) {
        header('location:index.php');
        exit;
    } else {
        echo '<script>alert("Gagal menambah pesanan baru: '.mysqli_error($c).'"); window.location.href="index.php"</script>';
        exit;
    }
}


/* ==================== TAMBAH DETAIL PESANAN ==================== */
if (isset($_POST['addmakanan'])) {
    $idmakanan = intval($_POST['idmakanan']);
    $idp       = intval($_POST['idp']);
    $qty       = intval($_POST['qty']);

    $cekStock = mysqli_query($c, "SELECT stock FROM makanan WHERE idmakanan='$idmakanan'");
    $dataStock = mysqli_fetch_array($cekStock);
    $stocksekarang = intval($dataStock['stock']);

    if ($stocksekarang >= $qty) {
        $selisih = $stocksekarang - $qty;
        $insert = mysqli_query($c, "INSERT INTO detailpesanan (idpesanan, idmakanan, qty) VALUES ('$idp','$idmakanan','$qty')");
        $update = mysqli_query($c, "UPDATE makanan SET stock='$selisih' WHERE idmakanan='$idmakanan'");
        if ($insert && $update) {
            header('location:view.php?idp=' . $idp);
            exit;
        } else {
            echo '<script>alert("Gagal menambah detail pesanan"); window.location.href="view.php?idp=' . $idp . '"</script>';
            exit;
        }
    } else {
        echo '<script>alert("Stock menu tidak cukup"); window.location.href="view.php?idp=' . $idp . '"</script>';
        exit;
    }
}

/* ==================== TAMBAH MENU MASUK ==================== */
if (isset($_POST['menumasuk'])) {

    $idmakanan = intval($_POST['idmakanan']);
    $qty       = intval($_POST['qty']);

    // Ambil stok makanan
    $get = mysqli_query($c, "SELECT stock FROM makanan WHERE idmakanan='$idmakanan'");
    $data = mysqli_fetch_assoc($get);
    $stoklama = intval($data['stock']);

    // Hitung stok baru
    $stokbaru = $stoklama + $qty;

    // Insert tabel masuk
    mysqli_query($c, "INSERT INTO masuk (idmakanan, qty) VALUES ('$idmakanan', '$qty')");

    // Update stok makanan
    mysqli_query($c, "UPDATE makanan SET stock='$stokbaru' WHERE idmakanan='$idmakanan'");

    header("Location: masuk.php");
    exit;
}


/* ==================== MENU MASUK (menambah stok) ==================== */
if (isset($_POST['editmasuk'])) {
    $idmasuk = intval($_POST['idmasuk']);
    $idmakanan = intval($_POST['idmakanan']);
    $qty_baru = intval($_POST['qty']);
    $qty_lama = intval($_POST['old_qty']);

    // Ambil stok makanan saat ini
    $caristock = mysqli_query($c, "SELECT stock FROM makanan WHERE idmakanan='$idmakanan'");
    $data = mysqli_fetch_assoc($caristock);
    $stock_sekarang = intval($data['stock']);

    // Hitung perubahan stok
    if ($qty_baru > $qty_lama) {
        $selisih = $qty_baru - $qty_lama;
        $stock_baru = $stock_sekarang + $selisih;
    } else {
        $selisih = $qty_lama - $qty_baru;
        $stock_baru = $stock_sekarang - $selisih;
    }

    // Update tabel masuk dan tabel makanan
    $update1 = mysqli_query($c, "UPDATE masuk SET qty='$qty_baru' WHERE idmasuk='$idmasuk'");
    $update2 = mysqli_query($c, "UPDATE makanan SET stock='$stock_baru' WHERE idmakanan='$idmakanan'");

    if ($update1 && $update2) {
        echo '<script>alert("Data menu masuk berhasil diperbarui!"); window.location.href="masuk.php";</script>';
        exit;
    } else {
        echo '<script>alert("Gagal memperbarui data menu masuk: '.mysqli_error($c).'"); window.location.href="masuk.php";</script>';
        exit;
    }
}


/* ==================== HAPUS MENU MASUK ==================== */
if (isset($_GET['hapusmasuk'])) {

    $idmasuk = intval($_GET['hapusmasuk']);

    // Ambil datanya
    $get = mysqli_query($c, "SELECT * FROM masuk WHERE idmasuk='$idmasuk'");
    $data = mysqli_fetch_assoc($get);

    if ($data) {
        $idmakanan = $data['idmakanan'];
        $qty = $data['qty'];

        // Kembalikan stok
        mysqli_query($c, "UPDATE makanan SET stock = stock - $qty WHERE idmakanan='$idmakanan'");

        // Hapus data
        mysqli_query($c, "DELETE FROM masuk WHERE idmasuk='$idmasuk'");
    }

    header("Location: masuk.php");
    exit;
}

/* Hapus menu (GET) */
if (isset($_GET['hapusmenu'])) {
    $idmakanan = intval($_GET['idmakanan']);

    // ambil foto dulu
    $qfoto = mysqli_query($c, "SELECT foto FROM makanan WHERE idmakanan='$idmakanan' LIMIT 1");
    $ff = mysqli_fetch_assoc($qfoto);
    $foto_n = $ff ? $ff['foto'] : null;

    // Hapus juga data terkait
    mysqli_query($c, "DELETE FROM detailpesanan WHERE idmakanan='$idmakanan'");
    mysqli_query($c, "DELETE FROM masuk WHERE idmakanan='$idmakanan'");

    // Hapus data makanan
    $hapus = mysqli_query($c, "DELETE FROM makanan WHERE idmakanan='$idmakanan'");

    // hapus file gambar jika ada dan bukan default
    if ($hapus && $foto_n && $foto_n !== 'noimage.png' && file_exists(__DIR__ . "/js/image/" . $foto_n)) {
        @unlink(__DIR__ . "/js/image/" . $foto_n);
    }

    header('location:stock.php');
    exit;
}

/* Hapus pelanggan (GET) */
if (isset($_GET['hapuspelanggan'])) {
    $id = intval($_GET['id_pelanggan']);

    $ambilpesanan = mysqli_query($c, "SELECT idpesanan FROM pesanan WHERE idpelanggan='$id'");
    while ($p = mysqli_fetch_array($ambilpesanan)) {
        $idp = intval($p['idpesanan']);
        $getdetails = mysqli_query($c, "SELECT * FROM detailpesanan WHERE idpesanan='$idp'");
        while ($d = mysqli_fetch_array($getdetails)) {
            $idmakanan = intval($d['idmakanan']);
            $qty = intval($d['qty']);
            mysqli_query($c, "UPDATE makanan SET stock = stock + $qty WHERE idmakanan='$idmakanan'");
        }
        mysqli_query($c, "DELETE FROM detailpesanan WHERE idpesanan='$idp'");
        mysqli_query($c, "DELETE FROM pesanan WHERE idpesanan='$idp'");
    }

    mysqli_query($c, "DELETE FROM pelanggan WHERE id_pelanggan='$id'");
    header('location:pelanggan.php');
    exit;
}

/* Hapus pesanan (GET) */
if (isset($_GET['hapuspesanan'])) {
    $idp = intval($_GET['idp']);
    $getdetails = mysqli_query($c, "SELECT * FROM detailpesanan WHERE idpesanan='$idp'");
    while ($d = mysqli_fetch_array($getdetails)) {
        $idmakanan = intval($d['idmakanan']);
        $qty = intval($d['qty']);
        mysqli_query($c, "UPDATE makanan SET stock = stock + $qty WHERE idmakanan='$idmakanan'");
    }
    mysqli_query($c, "DELETE FROM detailpesanan WHERE idpesanan='$idp'");
    mysqli_query($c, "DELETE FROM pesanan WHERE idpesanan='$idp'");
    header('location:index.php');
    exit;
}

/* Hapus detail pesanan (GET) */
if (isset($_GET['hapusdetail'])) {
    $iddetail = intval($_GET['hapusdetail']);
    $ambil = mysqli_query($c, "SELECT * FROM detailpesanan WHERE iddetailpesanan='$iddetail'");
    $data  = mysqli_fetch_array($ambil);

    if ($data) {
        $idmakanan = intval($data['idmakanan']);
        $qty       = intval($data['qty']);
        $idp       = intval($data['idpesanan']);
        $updateStok = mysqli_query($c, "UPDATE makanan SET stock = stock + $qty WHERE idmakanan='$idmakanan'");
        $hapusDetail = mysqli_query($c, "DELETE FROM detailpesanan WHERE iddetailpesanan='$iddetail'");

        if ($updateStok && $hapusDetail) {
            header('location:view.php?idp=' . $idp);
            exit;
        } else {
            echo '<script>alert("Gagal menghapus detail pesanan atau mengupdate stok."); window.location.href="view.php?idp=' . $idp . '"</script>';
            exit;
        }
    } else {
        echo '<script>alert("Data detail pesanan tidak ditemukan."); window.location.href="index.php"</script>';
        exit;
    }
}

/* Hapus lewat POST untuk fallback (hapusmenu sudah di-handle) */
if (isset($_POST['hapusmenu'])) {
    $idm = intval($_POST['idm']);
    // ambil foto dulu
    $qfoto = mysqli_query($c, "SELECT foto FROM makanan WHERE idmakanan='$idm' LIMIT 1");
    $ff = mysqli_fetch_assoc($qfoto);
    $foto_n = $ff ? $ff['foto'] : null;

    mysqli_query($c, "DELETE FROM detailpesanan WHERE idmakanan='$idm'");
    mysqli_query($c, "DELETE FROM masuk WHERE idmakanan='$idm'");
    $hapus = mysqli_query($c, "DELETE FROM makanan WHERE idmakanan='$idm'");

    if ($hapus && $foto_n && $foto_n !== 'noimage.png' && file_exists(__DIR__ . "/js/image/" . $foto_n)) {
        @unlink(__DIR__ . "/js/image/" . $foto_n);
    }

    if ($hapus) {
        header('location:stock.php');
        exit;
    } else {
        die("Gagal menghapus menu: " . mysqli_error($c));
    }
}

/* Hapus data masuk via POST */
if (isset($_POST['hapusmasuk'])) {
    $idmasuk = intval($_POST['idmasuk']);
    $idmakanan = intval($_POST['idmakanan']);
    $caritahu = mysqli_query($c, "SELECT qty FROM masuk WHERE idmasuk='$idmasuk'");
    $data = mysqli_fetch_array($caritahu);
    $qty = intval($data['qty']);
    mysqli_query($c, "UPDATE makanan SET stock = stock - $qty WHERE idmakanan='$idmakanan'");
    mysqli_query($c, "DELETE FROM masuk WHERE idmasuk='$idmasuk'");
    header('location:masuk.php');
    exit;
}

/* Hapus order via POST (fallback) */
if (isset($_POST['hapusorder'])) {
    $ido = intval($_POST['ido']);
    $cekdata = mysqli_query($c, "SELECT * FROM detailpesanan WHERE idpesanan='$ido'");
    $success = true;
    while ($ok = mysqli_fetch_array($cekdata)) {
        $qty = intval($ok['qty']);
        $idmakanan = intval($ok['idmakanan']);
        $iddp = intval($ok['iddetailpesanan']);
        $caristock = mysqli_query($c, "SELECT stock FROM makanan WHERE idmakanan='$idmakanan' LIMIT 1");
        $caristock2 = mysqli_fetch_array($caristock);
        $stocksekarang = intval($caristock2['stock']);
        $newstock = $stocksekarang + $qty;
        $queryupdate = mysqli_query($c, "UPDATE makanan SET stock='$newstock' WHERE idmakanan='$idmakanan'");
        if (!$queryupdate) $success = false;
        $querydelete = mysqli_query($c, "DELETE FROM detailpesanan WHERE iddetailpesanan='$iddp'");
        if (!$querydelete) $success = false;
    }
    $query = mysqli_query($c, "DELETE FROM pesanan WHERE idpesanan='$ido'");
    if (!$query) $success = false;
    if ($success) {
        header('location:index.php');
        exit;
    } else {
        echo '<script>alert("Gagal menghapus order dengan benar. Cek error log."); window.location.href="index.php"</script>';
        exit;
    }
}
// ===================== PEMBAYARAN PESANAN =====================
if (isset($_POST['bayarpesanan'])) {
    $idp = $_POST['idp'];
    $bayar = $_POST['bayar'];

    // Ambil total pesanan
    $gettotal = mysqli_query($c, "SELECT SUM(dp.qty * m.harga) AS total
                                  FROM detailpesanan dp
                                  JOIN makanan m ON dp.idmakanan = m.idmakanan
                                  WHERE dp.idpesanan = '$idp'");
    $data = mysqli_fetch_assoc($gettotal);
    $total = $data['total'] ?? 0;

    if ($total <= 0) {
        echo "<script>alert('Pesanan kosong, tidak bisa diproses!'); 
              window.location='view.php?idp=$idp';</script>";
        exit;
    }

    if ($bayar < $total) {
        echo "<script>alert('Uang bayar kurang!'); 
              window.location='view.php?idp=$idp';</script>";
        exit;
    }

    // Hitung kembalian
    $kembalian = $bayar - $total;

    // Simpan ke tabel pembayaran
    $simpan = mysqli_query($c, "INSERT INTO pembayaran (idpesanan, total, bayar, kembalian, tanggal)
                                VALUES ('$idp', '$total', '$bayar', '$kembalian', NOW())");

    if ($simpan) {
        // Ambil ID pembayaran terakhir
        $idpembayaran = mysqli_insert_id($c);

        // Simpan juga ke tabel laporan
        $simpanlaporan = mysqli_query($c, "INSERT INTO laporan (idpembayaran, idpesanan, total, tanggal)
                                           VALUES ('$idpembayaran', '$idp', '$total', NOW())");

        if ($simpanlaporan) {
            echo "<script>alert('Pembayaran berhasil dan laporan ditambahkan!');
                  window.location='view.php?idp=$idp';</script>";
        } else {
            echo "<script>alert('Pembayaran berhasil tapi gagal menambahkan ke laporan!');
                  window.location='view.php?idp=$idp';</script>";
        }
    } else {
        echo "<script>alert('Gagal menyimpan pembayaran!');
              window.location='view.php?idp=$idp';</script>";
    }
}

// ==================== EDIT PEMBAYARAN ====================
if (isset($_POST['editpembayaran'])) {
    $idpembayaran = $_POST['idpembayaran'];
    $total = $_POST['total'];
    $bayar = $_POST['bayar'];
    $kembalian = $_POST['kembalian'];
    $idp = $_POST['idp'];

    $update = mysqli_query($c, "UPDATE pembayaran 
                                SET total='$total', bayar='$bayar', kembalian='$kembalian' 
                                WHERE idpembayaran='$idpembayaran'");

    if ($update) {
        echo "<script>alert('Data pembayaran berhasil diubah'); window.location='view.php?idp=$idp';</script>";
    } else {
        echo "<script>alert('Gagal mengubah data pembayaran'); window.location='view.php?idp=$idp';</script>";
    }
}

// ==================== HAPUS PEMBAYARAN ====================
if (isset($_GET['hapuspembayaran'])) {
    $idpembayaran = $_GET['hapuspembayaran'];
    $idp = $_GET['idp'];

    $hapus = mysqli_query($c, "DELETE FROM pembayaran WHERE idpembayaran='$idpembayaran'");

    if ($hapus) {
        echo "<script>alert('Pembayaran berhasil dihapus'); window.location='view.php?idp=$idp';</script>";
    } else {
        echo "<script>alert('Gagal menghapus pembayaran'); window.location='view.php?idp=$idp';</script>";
    }
}
if (isset($_POST['ajaxaddmakanan'])) {
    $idmakanan = intval($_POST['idmakanan']);
    $idp = intval($_POST['idp']);
    $qty = intval($_POST['qty']);

    // Ambil data harga dan stok
    $cek = mysqli_query($c, "SELECT * FROM makanan WHERE idmakanan='$idmakanan'");
    $data = mysqli_fetch_assoc($cek);
    $harga = intval($data['harga']);
    $stock = intval($data['stock']);
    $namamakanan = $data['namamakanan'];
    $deskripsi = $data['deskripsi'];

    if ($stock < $qty) {
        echo json_encode(["status" => "error", "message" => "Stok tidak cukup!"]);
        exit;
    }

    // Tambahkan ke detailpesanan dan kurangi stok
    $tambah = mysqli_query($c, "INSERT INTO detailpesanan (idpesanan, idmakanan, qty) VALUES ('$idp', '$idmakanan', '$qty')");
    $kurangistock = mysqli_query($c, "UPDATE makanan SET stock = stock - $qty WHERE idmakanan = '$idmakanan'");

    if ($tambah && $kurangistock) {
        // Ambil ulang isi tabel detail pesanan
        $get = mysqli_query($c, "
            SELECT dp.*, m.namamakanan, m.harga, m.deskripsi
            FROM detailpesanan dp
            JOIN makanan m ON dp.idmakanan = m.idmakanan
            WHERE dp.idpesanan = '$idp'
        ");
        
        $rows = "";
        $i = 1;
        while ($p = mysqli_fetch_assoc($get)) {
            $iddp = $p['iddetailpesanan'];
            $subtotal = $p['qty'] * $p['harga'];

            // Setiap baris + modal edit
            $rows .= "
            <tr>
                <td>{$i}</td>
                <td>{$p['namamakanan']}</td>
                <td>Rp" . number_format($p['harga']) . "</td>
                <td>" . number_format($p['qty']) . "</td>
                <td>Rp" . number_format($subtotal) . "</td>
                <td>
                    <button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#edit{$iddp}'>Edit</button>
                    <a href='function.php?hapusdetail={$iddp}&idp={$idp}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin hapus item ini?\")'>Hapus</a>
                </td>
            </tr>

            <!-- Modal Edit -->
            <div class='modal fade' id='edit{$iddp}' tabindex='-1' role='dialog'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>Edit Pesanan</h5>
                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                        </div>
                        <form method='post' action='function.php'>
                            <div class='modal-body'>
                                <label>Nama Makanan</label>
                                <input type='text' class='form-control' value='{$p['namamakanan']}' readonly>
                                <label class='mt-3'>Jumlah</label>
                                <input type='number' name='qty' class='form-control' value='{$p['qty']}' min='1' required>
                                <input type='hidden' name='iddetailpesanan' value='{$iddp}'>
                                <input type='hidden' name='idp' value='{$idp}'>
                            </div>
                            <div class='modal-footer'>
                                <button type='submit' class='btn btn-success' name='editmakananpesanan'>Simpan</button>
                                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            ";
            $i++;
        }

        echo json_encode([
            "status" => "success",
            "tabel" => $rows
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($c)]);
    }

    exit;
}

/* ==================== EDIT MAKANAN DALAM PESANAN ==================== */
if (isset($_POST['editmakananpesanan'])) {
    $iddetail = intval($_POST['iddetailpesanan']);
    $idp = intval($_POST['idp']);
    $qty_baru = intval($_POST['qty']);

    // Ambil data lama
    $cek = mysqli_query($c, "
        SELECT dp.*, m.stock AS stok_makanan, m.idmakanan 
        FROM detailpesanan dp 
        JOIN makanan m ON dp.idmakanan = m.idmakanan 
        WHERE dp.iddetailpesanan='$iddetail'
    ");
    $data = mysqli_fetch_assoc($cek);

    if (!$data) {
        echo "<script>alert('Data tidak ditemukan'); window.location='view.php?idp=$idp';</script>";
        exit;
    }

    $idmakanan = intval($data['idmakanan']);
    $qty_lama = intval($data['qty']);
    $stok_sekarang = intval($data['stok_makanan']);

    // Hitung stok baru
    $stok_update = $stok_sekarang + $qty_lama - $qty_baru;
    if ($stok_update < 0) {
        echo "<script>alert('Stok tidak cukup!'); window.location='view.php?idp=$idp';</script>";
        exit;
    }

    // Update tabel
    $update_detail = mysqli_query($c, "UPDATE detailpesanan SET qty='$qty_baru' WHERE iddetailpesanan='$iddetail'");
    $update_stok = mysqli_query($c, "UPDATE makanan SET stock='$stok_update' WHERE idmakanan='$idmakanan'");

    if ($update_detail && $update_stok) {
        echo "<script>alert('Pesanan berhasil diperbarui!'); window.location='view.php?idp=$idp';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui pesanan: " . mysqli_error($c) . "'); window.location='view.php?idp=$idp';</script>";
    }

    exit;
}
/* =========================================================
   EDIT MAKANAN DALAM PESANAN (AJAX)
========================================================= */
if (isset($_POST['ajaxeditmakanan'])) {
    $iddetailpesanan = intval($_POST['iddetailpesanan']);
    $idp = intval($_POST['idp']);
    $qty = intval($_POST['qty']);

    // Ambil data lama
    $cek = mysqli_query($c, "SELECT * FROM detailpesanan WHERE iddetailpesanan='$iddetailpesanan'");
    $dataLama = mysqli_fetch_assoc($cek);
    $idmakanan = $dataLama['idmakanan'];
    $qtyLama = $dataLama['qty'];

    // Ambil stok
    $cekStok = mysqli_query($c, "SELECT stock FROM makanan WHERE idmakanan='$idmakanan'");
    $stokData = mysqli_fetch_assoc($cekStok);
    $stokSekarang = $stokData['stock'];

    // Hitung selisih
    $selisih = $qty - $qtyLama;
    if ($stokSekarang < $selisih) {
        echo json_encode(["status" => "error", "message" => "Stok tidak cukup untuk update jumlah!"]);
        exit;
    }

    // Update qty
    $update = mysqli_query($c, "UPDATE detailpesanan SET qty='$qty' WHERE iddetailpesanan='$iddetailpesanan'");
    $updateStok = mysqli_query($c, "UPDATE makanan SET stock=stock-$selisih WHERE idmakanan='$idmakanan'");

    if ($update && $updateStok) {
        // Ambil ulang seluruh isi tabel
        $get = mysqli_query($c, "
            SELECT dp.*, m.namamakanan, m.harga, m.deskripsi
            FROM detailpesanan dp
            JOIN makanan m ON dp.idmakanan = m.idmakanan
            WHERE dp.idpesanan='$idp'
        ");

        $i = 1;
        $rows = "";

        while ($p = mysqli_fetch_assoc($get)) {
            $subtotal = $p['qty'] * $p['harga'];
            $rows .= "
                <tr>
                    <td>{$i}</td>
                    <td>{$p['namamakanan']}</td>
                    <td>Rp" . number_format($p['harga']) . "</td>
                    <td>" . number_format($p['qty']) . "</td>
                    <td>Rp" . number_format($subtotal) . "</td>
                    <td>
                        <button type='button' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#edit{$p['iddetailpesanan']}'>Edit</button>
                        <a href='function.php?hapusdetail={$p['iddetailpesanan']}&idp={$idp}' class='btn btn-danger btn-sm'>Hapus</a>
                    </td>
                </tr>

                <!-- Modal Edit -->
                <div class='modal fade' id='edit{$p['iddetailpesanan']}' tabindex='-1' role='dialog'>
                    <div class='modal-dialog' role='document'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title'>Edit Pesanan</h5>
                                <button type='button' class='close' data-dismiss='modal'>&times;</button>
                            </div>
                            <form method='post' class='formEditMenu'>
                                <div class='modal-body'>
                                    <label>Nama Makanan</label>
                                    <input type='text' class='form-control' value='{$p['namamakanan']}' readonly>

                                    <label class='mt-3'>Jumlah</label>
                                    <input type='number' name='qty' class='form-control' value='{$p['qty']}' min='1' required>

                                    <input type='hidden' name='iddetailpesanan' value='{$p['iddetailpesanan']}'>
                                    <input type='hidden' name='idp' value='{$idp}'>
                                </div>
                                <div class='modal-footer'>
                                    <button type='submit' class='btn btn-success'>Simpan</button>
                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            ";
            $i++;
        }

        echo json_encode([
            "status" => "success",
            "tabel" => $rows
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($c)]);
    }

    exit;
}
if(isset($_GET['getTotalDanBayar'])){
    $idp = $_GET['idp'];
    
    // Hitung total
    $gettotal = mysqli_query($c, "SELECT SUM(m.harga * dp.qty) as total 
                                  FROM detailpesanan dp 
                                  JOIN makanan m ON dp.idmakanan = m.idmakanan 
                                  WHERE dp.idpesanan='$idp'");
    $t = mysqli_fetch_assoc($gettotal);
    $total = $t['total'] ?? 0;

    // Ambil tabel pembayaran
    $getbayar = mysqli_query($c, "SELECT * FROM pembayaran WHERE idpesanan='$idp'");
    $html = "";
    if(mysqli_num_rows($getbayar) > 0){
        while($b = mysqli_fetch_assoc($getbayar)){
            $html .= "<tr>
                <td>{$b['tanggal']}</td>
                <td>Rp".number_format($b['total'])."</td>
                <td>Rp".number_format($b['bayar'])."</td>
                <td>Rp".number_format($b['kembalian'])."</td>
                <td>
                    <button class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></button>
                    <button class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></button>
                </td>
            </tr>";
        }
    } else {
        $html = "<tr><td colspan='5' align='center'>Belum ada pembayaran</td></tr>";
    }

    echo json_encode([
        "total" => $total,
        "totalFormatted" => number_format($total, 0, ',', '.'),
        "tabelPembayaran" => $html
    ]);
    exit;
}







?>
