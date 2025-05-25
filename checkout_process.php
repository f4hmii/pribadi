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

// Tambahan: Ambil metode pembayaran dan total belanja dari form konfirmasi
$metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
$total_harga_pesanan = $_POST['total_belanja'] ?? 0; // Ambil total dari form konfirmasi

// Logika untuk checkout produk tunggal dari index.php (jika masih diperlukan)
// URL: checkout_process.php?produk_id=XXX&quantity=Y
// Perhatikan: Alur ini tidak akan melewati checkout_confirmation.php,
// jadi jika ingin fitur lengkap, harus lewat checkout_confirmation.php
if (isset($_GET['produk_id']) && isset($_GET['quantity'])) {
    $produk_ids[] = intval($_GET['produk_id']);
    $quantities[] = intval($_GET['quantity']);
    // Untuk alur checkout produk tunggal via GET, Anda mungkin perlu
    // mengambil metode pembayaran dan bukti pembayaran dari POST jika diimplementasikan
    // di detail.php, atau paksa metode tertentu dan tanpa bukti.
    // Untuk kesederhanaan, disarankan semua checkout melewati halaman konfirmasi.
}
// Logika untuk checkout dari keranjang (dari pages/checkout_confirmation.php)
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
        header("Location: cart.php"); // Kembali ke cart.php
        exit();
    }
}
// Jika tidak ada produk yang teridentifikasi untuk di-checkout
else {
    $_SESSION['error_message'] = "Tidak ada produk untuk di-checkout.";
    header("Location: index.php");
    exit();
}


// === Proses Upload Bukti Pembayaran ===
$bukti_pembayaran_url = null;
$target_file = null; // Inisialisasi untuk penanganan rollback

// Hanya proses upload jika file diunggah ATAU jika metode pembayarannya bukan COD
if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
    $target_dir = "uploads/bukti_pembayaran/"; // Pastikan folder ini ada dan writable
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Buat folder jika belum ada
    }

    $file_name = uniqid() . '_' . basename($_FILES['bukti_pembayaran']['name']);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validasi tipe file
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error_message'] = "Maaf, hanya file JPG, JPEG, PNG, GIF, & WEBP yang diperbolehkan.";
        header("Location: pages/checkout_confirmation.php");
        exit();
    }

    // Validasi ukuran file (misal: max 5MB)
    if ($_FILES['bukti_pembayaran']['size'] > 5000000) {
        $_SESSION['error_message'] = "Maaf, ukuran file terlalu besar. Maksimal 5MB.";
        header("Location: pages/checkout_confirmation.php");
        exit();
    }

    if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
        $bukti_pembayaran_url = $file_name;
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat mengunggah bukti pembayaran.";
        header("Location: pages/checkout_confirmation.php");
        exit();
    }
} else {
    // Jika tidak ada file diunggah
    if ($metode_pembayaran === 'cod') {
        $bukti_pembayaran_url = null; // Bukti tidak diperlukan untuk COD
    } else {
        // Jika metode bukan COD tapi tidak ada bukti pembayaran atau ada error upload
        $_SESSION['error_message'] = "Bukti pembayaran diperlukan.";
        header("Location: pages/checkout_confirmation.php");
        exit();
    }
}
// === Akhir Proses Upload Bukti Pembayaran ===


$conn->begin_transaction(); // Mulai transaksi untuk memastikan integritas data

try {
    // Buat pesanan baru di tabel `pesanan`
    // Status awal 'tertunda_pembayaran'
    $stmt_order = $conn->prepare("INSERT INTO pesanan (buyer_id, status, total_harga) VALUES (?, 'tertunda_pembayaran', ?)");
    $stmt_order->bind_param("id", $buyer_id, $total_harga_pesanan);
    $stmt_order->execute();
    $pesanan_id = $conn->insert_id; // Ambil ID pesanan yang baru dibuat

    // Tambahkan detail pesanan ke tabel `pesanan_detail` (stok belum dikurangi di sini)
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
            $nama_produk = $product_data['nama_produk'];

            $subtotal_produk = $harga_satuan * $quantity;
            // $total_harga_pesanan sudah diambil dari POST, tidak perlu diakumulasi ulang jika dari checkout_confirmation.php
            // Tapi jika ada kemungkinan dari jalur GET lain, maka tetap diakumulasi.
            // Untuk kesederhanaan, pastikan total_belanja dari checkout_confirmation sudah akurat.

            // Masukkan detail produk ke tabel `pesanan_detail`
            $stmt_order_detail = $conn->prepare("INSERT INTO pesanan_detail (pesanan_id, produk_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
            $stmt_order_detail->bind_param("iiid", $pesanan_id, $produk_id, $quantity, $harga_satuan);
            $stmt_order_detail->execute();

            // *** PENTING: STOK PRODUK TIDAK DIKURANGI DI SINI ***
            // Pengurangan stok akan terjadi setelah seller mengkonfirmasi pembayaran.

        } else {
            // Jika produk tidak ditemukan, batalkan transaksi
            throw new Exception("Produk dengan ID " . $produk_id . " tidak ditemukan.");
        }
    }

    // Update total harga di tabel `pesanan`
    // Ini penting jika total_harga_pesanan dihitung ulang di sini,
    // tapi jika sudah diambil dari POST `total_belanja`, ini mungkin redundan.
    // Namun tetap aman untuk memastikan total harga di pesanan akurat.
    $stmt_update_total = $conn->prepare("UPDATE pesanan SET total_harga = ? WHERE pesanan_id = ?");
    $stmt_update_total->bind_param("di", $total_harga_pesanan, $pesanan_id);
    $stmt_update_total->execute();


    // Masukkan data pembayaran ke tabel `pembayaran`
    $stmt_payment = $conn->prepare("INSERT INTO pembayaran (pesanan_id, metode_pembayaran, jumlah_pembayaran, bukti_pembayaran, status_pembayaran) VALUES (?, ?, ?, ?, 'pending')");
    $stmt_payment->bind_param("idsis", $pesanan_id, $metode_pembayaran, $total_harga_pesanan, $bukti_pembayaran_url);
    $stmt_payment->execute();

    // Hapus item dari keranjang setelah berhasil checkout (jika checkout dari keranjang)
    if (isset($_POST['checkout_cart'])) {
        $stmt_clear_cart = $conn->prepare("DELETE FROM keranjang WHERE pengguna_id = ?");
        $stmt_clear_cart->bind_param("i", $buyer_id);
        $stmt_clear_cart->execute();
    }

    $conn->commit(); // Commit transaksi jika semua berhasil
    $_SESSION['success_message'] = "Checkout berhasil! Pesanan Anda telah dibuat dengan ID #" . $pesanan_id . ". Menunggu konfirmasi pembayaran dari penjual.";
    header("Location: pages/order_confirmation.php?order_id=" . $pesanan_id); // Redirect ke halaman konfirmasi pesanan
    exit();

} catch (Exception $e) {
    $conn->rollback(); // Rollback transaksi jika ada kesalahan
    // Hapus file bukti pembayaran yang sudah terupload jika ada kesalahan
    if ($bukti_pembayaran_url && file_exists($target_file)) {
        unlink($target_file);
    }
    $_SESSION['error_message'] = "Checkout gagal: " . $e->getMessage();
    header("Location: pages/checkout_confirmation.php"); // Kembali ke halaman konfirmasi checkout dengan pesan error
    exit();
}
?>