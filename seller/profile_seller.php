<?php
session_start();
include '../db_connection.php'; // pastikan koneksi sudah benar
include "../view/header.php";

// fungsi ambil jumlah berdasarkan status
function getCountStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pesanan WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count;
}

// Fungsi baru untuk mendapatkan jumlah pesanan menunggu konfirmasi pembayaran
function getCountPaymentConfirmation($conn, $seller_id) {
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT p.pesanan_id)
                            FROM pesanan p
                            JOIN pembayaran py ON p.pesanan_id = py.pesanan_id
                            JOIN pesanan_detail pd ON p.pesanan_id = pd.pesanan_id
                            JOIN produk pr ON pd.produk_id = pr.produk_id
                            WHERE pr.seller_id = ? AND py.status_pembayaran = 'pending' AND p.status = 'tertunda_pembayaran'");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count;
}

$username = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? 'Role tidak diketahui';

// Dapatkan seller_id dari sesi
$seller_id_session = $_SESSION['id'] ?? 0;

$menungguKonfirmasiPembayaran = getCountPaymentConfirmation($conn, $seller_id_session);
$siapDikirim = getCountStatus($conn, 'diproses_penjual'); // Pesanan yang sudah dikonfirmasi pembayarannya oleh seller
$sudahDikirim = getCountStatus($conn, 'dikirim'); // Pesanan yang sudah ditandai dikirim oleh seller
$selesai = getCountStatus($conn, 'selesai'); // Pesanan yang sudah dikonfirmasi pembeli
$pembatalan = getCountStatus($conn, 'dibatalkan'); // Pesanan yang dibatalkan
// $ulasanPerluDibalas = getCountStatus($conn, 'ulasan_perlu_dibalas'); // Pastikan ini sesuai dengan status di DB Anda
$ulasanPerluDibalas = 0; // Contoh, jika tidak ada implementasi ulasan, bisa 0 dulu

?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
 </head>
 <body class="bg-white text-gray-900 font-sans">
  <main class="flex min-h-[calc(100vh-144px)]">
   <aside class="bg-gray-100 w-64 p-6 flex flex-col space-y-8 text-gray-700 text-sm select-none">
    <div class="flex items-center space-x-3">
      <i class="fas fa-user-circle text-2xl"></i>
      <div>
        <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($username); ?></p>
        <p class="text-xs text-gray-400"><?php echo htmlspecialchars($role); ?></p>
      </div>
    </div>

    <div class="flex flex-col space-y-2">
     <p class="font-bold text-gray-700 text-sm">Toko Saya</p>
     <a class="text-gray-500 text-sm hover:underline" href="#">Profile</a>
     <a class="text-gray-500 text-sm hover:underline" href="#">Alamat</a>
     <a class="text-gray-500 text-sm hover:underline" href="#">Ubah Password</a>
    </div>
    <p class="font-bold text-gray-700 text-sm cursor-pointer select-none">Produk</p>
    <p class="font-bold text-gray-700 text-sm cursor-pointer select-none">Notifikasi</p>
   </aside>

   <section class="flex-1 p-6 space-y-6">
    <div class="bg-gray-600 rounded-md p-6 flex justify-between max-w-full text-white">
        <div class="flex flex-col items-center space-y-1 cursor-pointer">
            <a href="detail_pesanan_seller.php?status=menunggu_konfirmasi_pembayaran" class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center font-bold text-3xl text-black">
                <?php echo $menungguKonfirmasiPembayaran; ?>
            </a>
            <p class="text-xs text-black">Konfirmasi Pembayaran</p>
        </div>
        <div class="flex flex-col items-center space-y-1 cursor-pointer">
            <a href="detail_pesanan_seller.php?status=diproses_penjual" class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center font-bold text-3xl text-black">
                <?php echo $siapDikirim; ?>
            </a>
            <p class="text-xs text-black">Siap Dikirim</p>
        </div>
        <div class="flex flex-col items-center space-y-1 cursor-pointer">
            <a href="detail_pesanan_seller.php?status=dikirim" class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center font-bold text-3xl text-black">
                <?php echo $sudahDikirim; ?>
            </a>
            <p class="text-xs text-black">Sudah Dikirim</p>
        </div>
        <div class="flex flex-col items-center space-y-1 cursor-pointer">
            <a href="detail_pesanan_seller.php?status=selesai" class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center font-bold text-3xl text-black">
                <?php echo $selesai; ?>
            </a>
            <p class="text-xs text-black">Selesai</p>
        </div>
        <div class="flex flex-col items-center space-y-1 cursor-pointer">
            <a href="detail_pesanan_seller.php?status=dibatalkan" class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center font-bold text-3xl text-black">
                <?php echo $pembatalan; ?>
            </a>
            <p class="text-xs text-black">Pembatalan</p>
        </div>
        <div class="flex flex-col items-center space-y-1 cursor-pointer">
            <a href="detail_pesanan_seller.php?status=ulasan_perlu_dibalas" class="bg-gray-300 rounded-md w-16 h-16 flex items-center justify-center font-bold text-3xl text-black">
                <?php echo $ulasanPerluDibalas; ?>
            </a>
            <p class="text-xs text-black">Ulasan Perlu Dibalas</p>
        </div>
    </div>

    <hr class="border-gray-300"/>

    <div class="bg-gray-600 rounded-md p-6 flex justify-between max-w-full text-gray-300">
     <div class="flex flex-col items-center space-y-1">
      <img alt="Icon of a product box in light gray on dark gray background" class="w-8 h-8" height="32" src="https://storage.googleapis.com/a1aa/image/08ff550c-2134-45b6-3314-cc27296441a4.jpg" width="32"/>
      <p class="font-bold text-xs text-gray-300">Produk</p>
     </div>
     <div class="flex flex-col items-center space-y-1">
      <img alt="Icon of a wallet in light gray on dark gray background" class="w-8 h-8" height="32" src="https://storage.googleapis.com/a1aa/image/59e6859c-098d-4597-ed88-2a77a16c2d10.jpg" width="32"/>
      <p class="font-bold text-xs text-gray-300">Wallet</p>
     </div>
     <div class="flex flex-col items-center space-y-1">
      <img alt="Icon of a bar chart in light gray on dark gray background" class="w-8 h-8" height="32" src="https://storage.googleapis.com/a1aa/image/1c7928a8-bc5c-40a7-baca-17abe1ffc970.jpg" width="32"/>
      <p class="font-bold text-xs text-gray-300">Ferforma</p>
     </div>
    </div>

    <div class="bg-gray-300 rounded-md h-28 flex items-center justify-center">
     <img alt="Large placeholder image icon in gray box" class="w-16 h-16" height="64" src="https://storage.googleapis.com/a1aa/image/0161db74-ca3e-4940-04f1-f6f4b59f88b2.jpg" width="64"/>
    </div>
   </section>
  </main>

  <footer class="bg-gray-600 text-gray-200">
   <div class="max-w-7xl mx-auto px-6 py-10 grid grid-cols-1 sm:grid-cols-4 gap-8 border-b border-gray-400">
    <div>
     <p class="font-bold text-lg mb-3">Logo</p>
     <p class="text-xs max-w-[160px]">Lorem ipsum sit dolor amet</p>
    </div>
    <div>
     <p class="font-bold mb-2">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
    </div>
    <div>
     <p class="font-bold mb-2">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
    </div>
    <div>
     <p class="font-bold mb-2">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
     <p class="text-xs">Lorem ipsum</p>
    </div>
   </div>
   <div class="flex items-center justify-between px-6 py-4 bg-gray-700">
    <p class="text-xs">© 2025 - Lorem ipsum</p>
    <div class="flex space-x-8">
     <div class="w-10 h-10 rounded-full bg-gray-300"></div>
     <div class="w-10 h-10 rounded-full bg-gray-300"></div>
     <div class="w-10 h-10 rounded-full bg-gray-300"></div>
    </div>
   </div>
  </footer>
 </body>
</html>