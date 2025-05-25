<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../pages/login.php");
    exit;
}

$buyer_id = $_SESSION['id'];
$action = $_GET['action'] ?? '';
$pesanan_id = intval($_GET['pesanan_id'] ?? 0);

if ($pesanan_id <= 0) {
    $_SESSION['error_message'] = "ID Pesanan tidak valid.";
    header("Location: buyer_orders.php");
    exit;
}

$conn->begin_transaction();

try {
    if ($action === 'confirm_received') {
        // Update status pesanan di tabel pesanan menjadi 'selesai'
        $stmt_order = $conn->prepare("UPDATE pesanan SET status = 'selesai' WHERE pesanan_id = ? AND buyer_id = ?");
        $stmt_order->bind_param("ii", $pesanan_id, $buyer_id);
        $stmt_order->execute();

        if ($stmt_order->affected_rows > 0) {
            $_SESSION['success_message'] = "Pesanan #" . $pesanan_id . " berhasil dikonfirmasi telah diterima.";
        } else {
            throw new Exception("Gagal mengkonfirmasi penerimaan pesanan atau pesanan tidak ditemukan.");
        }
    }

    $conn->commit();
    header("Location: buyer_orders.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Aksi gagal: " . $e->getMessage();
    header("Location: buyer_orders.php");
    exit;
}
?>