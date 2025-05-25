<?php
session_start();
include 'db_connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: pages/login.php"); // Arahkan ke halaman login
    exit();
}

$buyer_id = $_SESSION['id'];
$produk_ids = [];
$quantities = [];

// Logika untuk checkout produk tunggal dari index.php
// URL: checkout_process.php?produk_id=XXX&quantity=Y
if (isset($_GET['produk_id']) && isset($_GET['quantity'])) {
    $produk_ids[] = intval($_GET['produk_id']);
    $quantities[] = intval($_GET['quantity']);
}
// Logika untuk checkout dari keranjang (dari cart.php dengan tombol "Checkout Semua")
// Mengambil semua item dari tabel keranjang untuk pengguna yang login
elseif (isset($_POST['checkout_cart'])) {
    $cart_query = $conn->prepare("SELECT k.produk_id, k.quantity FROM keranjang k WHERE k.pengguna_id = ?");
    $cart_query->bind_param("i", $buyer_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();

    if ($cart_result->num_rows > 0) {
        while ($row = $cart_result->fetch_assoc()) {
            $produk_ids[] = $row['produk_id'];
            $quantities[] = $row['quantity'];
        }
    } else {
        $_SESSION['error_message'] = "Keranjang Anda kosong.";
        header("Location: cart.php");
        exit();
    }
}
// Jika tidak ada produk yang teridentifikasi untuk di-checkout
else {
    $_SESSION['error_message'] = "Tidak ada produk untuk di-checkout.";
    header("Location: index.php");
    exit();
}

$total_harga_pesanan = 0;
$conn->begin_transaction(); // Mulai transaksi untuk memastikan integritas data

try {
    // Buat pesanan baru di tabel `pesanan`
    // Status awal 'tertunda', total_harga akan diupdate nanti
    $stmt_order = $conn->prepare("INSERT INTO pesanan (buyer_id, status, total_harga) VALUES (?, 'tertunda', ?)");
    $stmt_order->bind_param("id", $buyer_id, $total_harga_pesanan); // total_harga diisi 0 sementara
    $stmt_order->execute();
    $pesanan_id = $conn->insert_id; // Ambil ID pesanan yang baru dibuat

    // Tambahkan detail pesanan ke tabel `pesanan_detail` dan kurangi stok produk
    foreach ($produk_ids as $index => $produk_id) {
        $quantity = $quantities[$index];

        // Ambil harga produk dan stok dari tabel `produk`
        $stmt_product = $conn->prepare("SELECT harga, stock, nama_produk FROM produk WHERE produk_id = ?");
        $stmt_product->bind_param("i", $produk_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();
        $product_data = $result_product->fetch_assoc();

        if ($product_data) {
            $harga_satuan = $product_data['harga'];
            $stock_produk = $product_data['stock'];
            $nama_produk = $product_data['nama_produk'];

            // Cek stok produk
            if ($stock_produk < $quantity) {
                // Jika stok tidak cukup, batalkan transaksi dan berikan pesan error
                throw new Exception("Stok produk '" . htmlspecialchars($nama_produk) . "' tidak mencukupi. Stok tersedia: " . $stock_produk);
            }

            $subtotal_produk = $harga_satuan * $quantity;
            $total_harga_pesanan += $subtotal_produk;

            // Masukkan detail produk ke tabel `pesanan_detail`
            $stmt_order_detail = $conn->prepare("INSERT INTO pesanan_detail (pesanan_id, produk_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
            $stmt_order_detail->bind_param("iiid", $pesanan_id, $produk_id, $quantity, $harga_satuan);
            $stmt_order_detail->execute();

            // Kurangi stok produk di tabel `produk`
            $new_stock = $stock_produk - $quantity;
            $stmt_update_stock = $conn->prepare("UPDATE produk SET stock = ? WHERE produk_id = ?");
            $stmt_update_stock->bind_param("ii", $new_stock, $produk_id);
            $stmt_update_stock->execute();

        } else {
            // Jika produk tidak ditemukan, batalkan transaksi
            throw new Exception("Produk dengan ID " . $produk_id . " tidak ditemukan.");
        }
    }

    // Update total harga di tabel `pesanan` setelah semua detail ditambahkan
    $stmt_update_total = $conn->prepare("UPDATE pesanan SET total_harga = ? WHERE pesanan_id = ?");
    $stmt_update_total->bind_param("di", $total_harga_pesanan, $pesanan_id);
    $stmt_update_total->execute();

    // Hapus item dari keranjang setelah berhasil checkout (jika checkout dari keranjang)
    if (isset($_POST['checkout_cart'])) {
        $stmt_clear_cart = $conn->prepare("DELETE FROM keranjang WHERE pengguna_id = ?");
        $stmt_clear_cart->bind_param("i", $buyer_id);
        $stmt_clear_cart->execute();
    }

    $conn->commit(); // Commit transaksi jika semua berhasil
    $_SESSION['success_message'] = "Checkout berhasil! Pesanan Anda telah dibuat dengan ID #" . $pesanan_id;
    header("Location: pages/order_confirmation.php?order_id=" . $pesanan_id); // Redirect ke halaman konfirmasi pesanan
    exit();

} catch (Exception $e) {
    $conn->rollback(); // Rollback transaksi jika ada kesalahan
    $_SESSION['error_message'] = "Checkout gagal: " . $e->getMessage();
    header("Location: cart.php"); // Kembali ke halaman keranjang atau detail produk dengan pesan error
    exit();
}
?>