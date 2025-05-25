<?php
session_start();
include "header.php";
include '../db_connection.php'; // Koneksi database

$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

if ($kategori == 'semua') {
    // Tampilkan semua produk yang sudah diverifikasi
    $query = "SELECT * FROM produk WHERE verified = 1 ORDER BY produk_id DESC";
} else {
    // Ambil kategori_id berdasarkan nama kategori, lalu tampilkan produk verified dari kategori itu
    $ambilKategori = mysqli_query($conn, "SELECT kategori_id FROM kategori WHERE nama_kategori = '" . mysqli_real_escape_string($conn, $kategori) . "'");
    $dataKategori = mysqli_fetch_assoc($ambilKategori);
    $idKategori = $dataKategori['kategori_id'] ?? 0;

    $query = "SELECT * FROM produk WHERE kategori_id = '$idKategori' AND verified = 1 ORDER BY produk_id DESC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Produk - <?php echo htmlspecialchars($kategori); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<h1 class="text-3xl font-bold text-center my-6">Produk Kategori: <?php echo htmlspecialchars($kategori); ?></h1>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 px-6">
<?php
while ($produk = mysqli_fetch_assoc($result)) {
    $gambar = !empty($produk['foto_url']) ? '../uploads/' . htmlspecialchars($produk['foto_url']) : 'gambar/default.jpg';
    ?>
    <div class="relative w-full bg-white border border-gray-200 rounded-lg shadow hover:shadow-lg transition-shadow duration-300">
      <form method="POST" action="favorite.php" class="absolute top-3 right-3 z-10">
        <input type="hidden" name="produk_id" value="<?= $produk['produk_id'] ?>">
        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors duration-200" title="Tambah Favorit">
          <i data-feather="heart" class="w-5 h-5"></i>
        </button>
      </form>

      <a href="detail.php?id=<?= $produk['produk_id'] ?>" class="block p-4">
        <img class="rounded-t-lg w-full h-48 object-contain" src="<?= $gambar ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" />
      </a>

      <div class="px-5 pb-5">
        <a href="detail.php?id=<?= $produk['produk_id'] ?>">
          <h5 class="text-xl font-semibold tracking-tight text-gray-900 mb-1"><?= htmlspecialchars($produk['nama_produk']) ?></h5>
        </a>
        <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?= htmlspecialchars($produk['deskripsi']) ?></p>

        <div class="flex items-center justify-between mb-3">
          <span class="text-2xl font-bold text-gray-900">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></span>
          <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="produk_id" value="<?= $produk['produk_id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 transition-colors duration-200">
              Add to Cart
            </button>
          </form>
        </div>

        <a href="checkout_process.php?produk_id=<?= $produk['produk_id'] ?>&quantity=1" class="block w-full text-center text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors duration-200">
          Checkout Sekarang
        </a>
      </div>
    </div>
<?php
}
?>
</div>
