<?php
session_start();
include "../view/header.php";
include '../db_connection.php'; // pastikan koneksi sudah benar

// Ambil produk berdasarkan produk_id (misal lewat GET)
$produk_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($produk_id <= 0) {
    die("Produk tidak ditemukan.");
}

// Query data produk utama
$sqlProduk = "SELECT * FROM produk WHERE produk_id = $produk_id";
$resultProduk = $conn->query($sqlProduk);
if ($resultProduk->num_rows == 0) {
    die("Produk tidak ditemukan.");
}
$produk = $resultProduk->fetch_assoc();

// Query gambar thumbnail
$images = [];
if (!empty($produk['foto_url'])) {
    $images[] = 'uploads/' . htmlspecialchars($produk['foto_url']);
} else {
    // fallback jika gak ada gambar
    $images[] = 'path/to/default-image.jpg';
}
?>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Product Page
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
 </head>
 <body class="bg-gray-100 text-gray-900 font-sans">
<main class="max-w-7xl mx-auto px-4 py-8 space-y-8">

<section class="bg-white p-6 space-y-6">
 <div class="flex flex-col lg:flex-row gap-6">
   <!-- Left images -->
   <div class="flex flex-col space-y-4">
     <img alt="<?= htmlspecialchars($produk['nama_produk']) ?>" class="w-72 h-72 object-cover rounded-lg" src="<?= htmlspecialchars($produk['foto_url']) ?>" />
    <div class="flex space-x-4">
  <?php foreach ($images as $img): ?>
    <img alt="Thumbnail <?= htmlspecialchars($produk['nama_produk']) ?>" class="w-14 h-14 object-cover rounded border border-gray-200" src="<?= $img ?>" />
  <?php endforeach; ?>
</div>
   </div>

   <!-- Right details -->
   <div class="flex-grow space-y-4">
     <div class="flex flex-wrap items-center gap-2 text-sm font-semibold">
       <span class="text-gray-900"><?= htmlspecialchars($produk['nama_produk']) ?></span>
       <!-- Tambah rating atau info lain jika ada -->
     </div>

     <div>
       <span class="text-3xl font-extrabold text-red-700">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></span>
     </div>


<?php
// Contoh koneksi sudah ada, dan $produk_id sudah didapat

$sizes = []; // inisialisasi dulu supaya tidak undefined

$sqlSizes = "SELECT size_name FROM sizes WHERE produk_id = $produk_id AND stock > 0";
$resultSizes = $conn->query($sqlSizes);

if ($resultSizes && $resultSizes->num_rows > 0) {
    while ($row = $resultSizes->fetch_assoc()) {
        $sizes[] = $row;
    }
}
?>

    <div class="flex items-center space-x-6 text-xs sm:text-sm">
  <?php foreach ($sizes as $size): ?>
    <label class="flex items-center space-x-1">
      <input class="w-4 h-4" type="checkbox" name="size[]" value="<?= htmlspecialchars($size['size_name']) ?>" />
      <span><?= htmlspecialchars($size['size_name']) ?></span>
    </label>
  <?php endforeach; ?>
</div>

     <div class="space-y-2 max-w-xs">
       <button class="w-full bg-black text-white py-2 flex items-center justify-center space-x-2 text-sm font-semibold rounded">
         <i class="fas fa-shopping-cart"></i>
         <span>Tambah ke Keranjang</span>
       </button>
       <button class="w-full border border-gray-400 text-gray-600 py-2 flex items-center justify-center space-x-2 text-sm font-semibold rounded">
         <i class="far fa-heart"></i>
         <span>Wishlist</span>
       </button>
     </div>

     <p class="text-xs max-w-xs"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
   </div>
 </div>

    
     <!-- Product card 1 -->
      <?php
// Perbaiki path include sesuai struktur folder kamu
include __DIR__ . '/../db_connection.php';

// Cek koneksi
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ambil data produk
$query = "SELECT * FROM produk";
$result = $conn->query($query);

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
}
?>

<div class="container" id="product-list">
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-6">
    <?php foreach ($products as $product): ?>
      <div class="relative w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <!-- Icon Love (favorite) -->
        <form method="POST" action="favorite.php" class="absolute top-3 right-3">
          <input type="hidden" name="produk_id" value="<?= htmlspecialchars($product['produk_id']) ?>">
          <button type="submit" class="text-gray-500 hover:text-red-500">
            <i data-feather="heart" class="w-5 h-5"></i>
          </button>
        </form>

        <a href="detail.php?id=<?= htmlspecialchars($product['produk_id']) ?>">
          <img class="p-6 rounded-t-lg mx-auto max-h-48 object-contain" src="uploads/<?= htmlspecialchars($product['foto_url']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" />
        </a>

        <div class="px-5 pb-5">
          <a href="detail.php?id=<?= htmlspecialchars($product['produk_id']) ?>">
            <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white"><?= htmlspecialchars($product['nama_produk']) ?></h5>
          </a>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-2"><?= htmlspecialchars($product['deskripsi']) ?></p>

          <div class="flex items-center justify-between mt-4 mb-3">
            <span class="text-2xl font-bold text-gray-900 dark:text-white">Rp<?= number_format($product['harga'], 0, ',', '.') ?></span>
            <a href="add_to_cart.php?id=<?= htmlspecialchars($product['produk_id']) ?>" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:focus:ring-blue-800">
              Add to Cart
            </a>
          </div>

          <a href="checkout.php?id=<?= htmlspecialchars($product['produk_id']) ?>" class="block w-full text-center text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:focus:ring-green-800">
            Checkout Sekarang
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

    
   </section>
  </main>
   </script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>


    <script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace();
</script>
 </body>
</html>
