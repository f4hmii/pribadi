<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: ../pages/login.php");
    exit;
}

$seller_id = $_SESSION['id'];
$action = $_GET['action'] ?? '';
$pesanan_id = intval($_GET['pesanan_id'] ?? 0);

if ($pesanan_id <= 0) {
    $_SESSION['error_message'] = "ID Pesanan tidak valid.";
    header("Location: profile_seller.php");
    exit;
}

$conn->begin_transaction();

try {
    if ($action === 'confirm_payment') {
        // Update status pembayaran di tabel pembayaran
        $stmt_payment = $conn->prepare("UPDATE pembayaran SET status_pembayaran = 'confirmed' WHERE pesanan_id = ?");
        $stmt_payment->bind_param("i", $pesanan_id);
        $stmt_payment->execute();

        // Update status pesanan di tabel pesanan menjadi 'diproses_penjual'
        $stmt_order = $conn->prepare("UPDATE pesanan SET status = 'diproses_penjual' WHERE pesanan_id = ?");
        $stmt_order->bind_param("i", $pesanan_id);
        $stmt_order->execute();

        // STOK BARU DIKURANGI DI SINI SETELAH PEMBAYARAN DIKONFIRMASI
        $stmt_detail = $conn->prepare("SELECT produk_id, jumlah FROM pesanan_detail WHERE pesanan_id = ?");
        $stmt_detail->bind_param("i", $pesanan_id);
        $stmt_detail->execute();
        $result_detail = $stmt_detail->get_result();

        while ($item = $result_detail->fetch_assoc()) {
            $produk_id = $item['produk_id'];
            $jumlah = $item['jumlah'];

            $stmt_produk = $conn->prepare("SELECT stock FROM produk WHERE produk_id = ?");
            $stmt_produk->bind_param("i", $produk_id);
            $stmt_produk->execute();
            $produk_data = $stmt_produk->get_result()->fetch_assoc();
            $current_stock = $produk_data['stock'];

            if ($current_stock < $jumlah) {
                throw new Exception("Stok tidak cukup untuk produk ID: " . $produk_id);
            }

            $new_stock = $current_stock - $jumlah;
            $stmt_update_stock = $conn->prepare("UPDATE produk SET stock = ? WHERE produk_id = ?");
            $stmt_update_stock->bind_param("ii", $new_stock, $produk_id);
            $stmt_update_stock->execute();
        }

        $_SESSION['success_message'] = "Pembayaran pesanan #" . $pesanan_id . " berhasil dikonfirmasi. Stok produk telah diperbarui.";

    } elseif ($action === 'reject_payment') {
        // Update status pembayaran di tabel pembayaran
        $stmt_payment = $conn->prepare("UPDATE pembayaran SET status_pembayaran = 'rejected' WHERE pesanan_id = ?");
        $stmt_payment->bind_param("i", $pesanan_id);
        $stmt_payment->execute();

        // Update status pesanan di tabel pesanan menjadi 'dibatalkan'
        $stmt_order = $conn->prepare("UPDATE pesanan SET status = 'dibatalkan' WHERE pesanan_id = ?");
        $stmt_order->bind_param("i", $pesanan_id);
        $stmt_order->execute();

        $_SESSION['success_message'] = "Pembayaran pesanan #" . $pesanan_id . " berhasil ditolak. Pesanan dibatalkan.";

    } elseif ($action === 'mark_as_shipped') {
        // Update status pesanan di tabel pesanan menjadi 'dikirim'
        $stmt_order = $conn->prepare("UPDATE pesanan SET status = 'dikirim' WHERE pesanan_id = ?");
        $stmt_order->bind_param("i", $pesanan_id);
        $stmt_order->execute();

        $_SESSION['success_message'] = "Pesanan #" . $pesanan_id . " berhasil ditandai sebagai dikirim.";
    }

    $conn->commit();
    header("Location: detail_pesanan_seller.php?status=" . $status_filter);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Aksi gagal: " . $e->getMessage();
    header("Location: detail_pesanan_seller.php?status=" . $status_filter);
    exit;
}
?>