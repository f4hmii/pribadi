<?php
include '../db_connection.php';

// Ambil ID produk dari URL
$id = $_GET['produk_id'];

// Ambil data produk saat ini
$produk = $conn->query("SELECT * FROM produk WHERE produk_id = $id")->fetch_assoc();

// Ambil semua kategori untuk dropdown
$kategoriList = $conn->query("SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Kategori Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Edit Kategori Produk</h2>

    <form method="POST">
        <div class="mb-3">
            <label>Kategori</label>
            <select name="kategori_id" class="form-control">
                <?php while ($kategori = $kategoriList->fetch_assoc()): ?>
                    <option value="<?= $kategori['kategori_id'] ?>" 
                        <?= $kategori['kategori_id'] == $produk['kategori_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kategori['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" name="update" class="btn btn-primary">Simpan</button>
        <a href="../admincontrol/kelola_produk.php" class="btn btn-secondary">Kembali</a>
    </form>

<?php
if (isset($_POST['update'])) {
    $kategori_id = $_POST['kategori_id'];

    // Update kategori_id produk
    $conn->query("UPDATE produk SET kategori_id = $kategori_id WHERE produk_id = $id");

    echo "<script>alert('Kategori berhasil diperbarui');location.href='../admincontrol/category_admin';</script>";
}
?>
</body>
</html>
