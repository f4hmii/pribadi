<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['id'])) {
    header("Location: pages/login.php");
    exit();
}

$buyer_id = $_SESSION['id'];
$produk_ids = [];
$quantities = [];
$total_harga_pesanan = 0;
$metode_pembayaran = $_POST['metode_pembayaran'] ?? '';

// Logika untuk produk_ids dan quantities (seperti sebelumnya dari GET atau POST keranjang)
if (isset($_GET['produk_id']) && isset($_GET['quantity'])) {
    $produk_ids[] = intval($_GET['produk_id']);
    $quantities[] = intval($_GET['quantity']);
} elseif (isset($_POST['checkout_cart'])) {
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
} else {
    $_SESSION['error_message'] = "Tidak ada produk untuk di-checkout.";
    header("Location: index.php");
    exit();
}

// === Proses Upload Bukti Pembayaran ===
$bukti_pembayaran_url = null;
if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
    $target_dir = "uploads/bukti_pembayaran/"; // Pastikan folder ini ada dan writable
    $file_name = uniqid() . '_' . basename($_FILES['bukti_pembayaran']['name']);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validasi tipe file
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error_message'] = "Maaf, hanya file JPG, JPEG, PNG, GIF, & WEBP yang diperbolehkan.";
        header("Location: cart.php"); // Atau halaman checkout Anda
        exit();
    }

    // Validasi ukuran file (misal: max 5MB)
    if ($_FILES['bukti_pembayaran']['size'] > 5000000) {
        $_SESSION['error_message'] = "Maaf, ukuran file terlalu besar. Maksimal 5MB.";
        header("Location: cart.php");
        exit();
    }

    if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
        $bukti_pembayaran_url = $file_name;
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat mengunggah bukti pembayaran.";
        header("Location: cart.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Bukti pembayaran diperlukan.";
    header("Location: cart.php");
    exit();
}
// === Akhir Proses Upload Bukti Pembayaran ===


$conn->begin_transaction();

try {
    // 1. Buat pesanan baru di tabel `pesanan` dengan status 'tertunda_pembayaran'
    $stmt_order = $conn->prepare("INSERT INTO pesanan (buyer_id, status, total_harga) VALUES (?, 'tertunda_pembayaran', ?)");
    $stmt_order->bind_param("id", $buyer_id, $total_harga_pesanan);
    $stmt_order->execute();
    $pesanan_id = $conn->insert_id;

    // 2. Tambahkan detail pesanan dan hitung total harga
    foreach ($produk_ids as $index => $produk_id) {
        $quantity = $quantities[$index];

        $stmt_product = $conn->prepare("SELECT harga, stock, nama_produk, seller_id FROM produk WHERE produk_id = ?");
        $stmt_product->bind_param("i", $produk_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();
        $product_data = $result_product->fetch_assoc();

        if ($product_data) {
            $harga_satuan = $product_data['harga'];
            $stock_produk = $product_data['stock'];
            $nama_produk = $product_data['nama_produk'];
            $seller_id_produk = $product_data['seller_id']; // Ambil seller_id dari produk

            // Catatan: Pengurangan stok akan dilakukan SETELAH pembayaran dikonfirmasi oleh seller.
            // Saat ini, stok tidak langsung berkurang saat checkout.

            $subtotal_produk = $harga_satuan * $quantity;
            $total_harga_pesanan += $subtotal_produk;

            // Masukkan detail produk ke tabel `pesanan_detail`
            $stmt_order_detail = $conn->prepare("INSERT INTO pesanan_detail (pesanan_id, produk_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
            $stmt_order_detail->bind_param("iiid", $pesanan_id, $produk_id, $quantity, $harga_satuan);
            $stmt_order_detail->execute();

            // (Opsional) Jika perlu mencatat seller_id di tabel pesanan jika hanya 1 seller per pesanan
            // Jika pesanan bisa punya banyak seller, butuh tabel relasi many-to-many atau memecah pesanan.
            // Untuk kesederhanaan, asumsikan 1 pesanan bisa punya banyak produk dari seller yang sama atau biarkan seller_id di detail pesanan.
        } else {
            throw new Exception("Produk dengan ID " . $produk_id . " tidak ditemukan.");
        }
    }

    // 3. Update total harga di tabel `pesanan`
    $stmt_update_total = $conn->prepare("UPDATE pesanan SET total_harga = ? WHERE pesanan_id = ?");
    $stmt_update_total->bind_param("di", $total_harga_pesanan, $pesanan_id);
    $stmt_update_total->execute();

    // 4. Masukkan data pembayaran ke tabel `pembayaran`
    $stmt_payment = $conn->prepare("INSERT INTO pembayaran (pesanan_id, metode_pembayaran, jumlah_pembayaran, bukti_pembayaran, status_pembayaran) VALUES (?, ?, ?, ?, 'pending')");
    $stmt_payment->bind_param("idsi", $pesanan_id, $metode_pembayaran, $total_harga_pesanan, $bukti_pembayaran_url);
    $stmt_payment->execute();

    // 5. Hapus item dari keranjang setelah berhasil checkout (jika checkout dari keranjang)
    if (isset($_POST['checkout_cart'])) {
        $stmt_clear_cart = $conn->prepare("DELETE FROM keranjang WHERE pengguna_id = ?");
        $stmt_clear_cart->bind_param("i", $buyer_id);
        $stmt_clear_cart->execute();
    }

    $conn->commit();
    $_SESSION['success_message'] = "Checkout berhasil! Pesanan Anda telah dibuat dengan ID #" . $pesanan_id . ". Menunggu konfirmasi pembayaran dari penjual.";
    header("Location: pages/order_confirmation.php?order_id=" . $pesanan_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    // Hapus file bukti pembayaran yang sudah terupload jika ada kesalahan
    if ($bukti_pembayaran_url && file_exists($target_file)) {
        unlink($target_file);
    }
    $_SESSION['error_message'] = "Checkout gagal: " . $e->getMessage();
    header("Location: cart.php"); // Kembali ke halaman keranjang atau detail produk dengan pesan error
    exit();
}
?>