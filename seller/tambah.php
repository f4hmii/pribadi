<?php
session_start();
include '../db_connection.php';

// Pastikan hanya seller yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: ../login.php");
    exit;
}

// Pastikan session id pengguna ada dan valid
if (!isset($_SESSION['id'])) {
    die("Error: Anda belum login atau session pengguna tidak ditemukan.");
}

$pengguna_id = intval($_SESSION['id']);

// Cek apakah pengguna_id ada di tabel pengguna
$resultCheck = $conn->query("SELECT pengguna_id FROM pengguna WHERE pengguna_id = $pengguna_id");
if ($resultCheck->num_rows == 0) {
    die("Error: Pengguna tidak valid.");
}

// Setelah pengecekan di atas berhasil, kamu bisa lanjut ke proses insert produk di bagian form handler
// Misalnya setelah form submit:
if (isset($_POST['simpan'])) {
    // proses simpan produk ...
    // gunakan $pengguna_id untuk kolom pengguna_id di tabel produk
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tambah Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Tambah Produk</h2>
    <form method="POST" enctype="multipart/form-data">
       
        <div class="mb-3">
            <label>Nama Produk</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Gambar</label>
            <input type="file" name="gambar" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Kategori</label>
            <select name="kategori_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php
                $kategori = $conn->query("SELECT * FROM kategori");
                while ($row = $kategori->fetch_assoc()) {
                    echo "<option value='{$row['kategori_id']}'>{$row['nama_kategori']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Ukuran Produk</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="size[]" value="S"> <label class="form-check-label">S</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="size[]" value="M"> <label class="form-check-label">M</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="size[]" value="L"> <label class="form-check-label">L</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="size[]" value="XL"> <label class="form-check-label">XL</label>
            </div>
        </div>
        <div class="mb-3">
            <label>Warna</label>
            <input type="text" name="color" class="form-control" required>
        </div>

        <button class="btn btn-success" name="simpan">Simpan</button>
        <a href="produk.php" class="btn btn-secondary">Kembali</a>
    </form>

<?php
if (isset($_POST['simpan'])) {
    // Sanitasi input
    $nama        = $conn->real_escape_string(htmlspecialchars($_POST['nama']));
    $deskripsi   = $conn->real_escape_string(htmlspecialchars($_POST['deskripsi']));
    $stok        = intval($_POST['stok']);
    $harga       = floatval($_POST['harga']);
    $warna       = $conn->real_escape_string(htmlspecialchars($_POST['color']));
    $kategori_id = intval($_POST['kategori_id']);
    $pengguna_id = intval($_SESSION['id']);
    $sizes       = isset($_POST['size']) ? $_POST['size'] : [];  // ini array

    // Upload gambar
    $gambar     = $_FILES['gambar']['name'];
    $tmp_name   = $_FILES['gambar']['tmp_name'];
    $upload_dir = "../uploads/";

    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed_ext)) {
        $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($tmp_name, $target_file)) {
            // Insert produk (gunakan kolom yang benar, misal nama_produk)
           $sql = "INSERT INTO produk (nama_produk, deskripsi, stock, harga, foto_url, seller_id, kategori_id, color, verified) 
        VALUES ('$nama', '$deskripsi', $stok, $harga, '$new_filename', $pengguna_id, $kategori_id, '$warna', 0)";


            if ($conn->query($sql)) {
                $produk_id = $conn->insert_id;

                // Insert ukuran produk ke tabel produk_size (atau nama tabel kamu)
                foreach ($sizes as $size) {
                    $size_escaped = $conn->real_escape_string($size);
                    $conn->query("INSERT INTO produk_size (produk_id, size) VALUES ($produk_id, '$size_escaped')");
                }

                echo "<div class='alert alert-success mt-3'>Produk berhasil ditambahkan dan menunggu verifikasi admin</div>";
                echo "<script>setTimeout(() => { location.href = 'produk.php'; }, 1500);</script>";
            } else {
                echo "<div class='alert alert-danger mt-3'>Gagal menyimpan produk: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger mt-3'>Gagal mengupload gambar</div>";
        }
    } else {
        echo "<div class='alert alert-warning mt-3'>Format file tidak diizinkan. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.</div>";
    }
}

?>

</body>
</html>