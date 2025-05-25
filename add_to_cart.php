<?php
session_start();
include 'db_connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: pages/login.php"); // Arahkan ke halaman login
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengguna_id = $_SESSION['id']; // Ambil ID pengguna dari sesi
    $produk_id = isset($_POST['produk_id']) ? intval($_POST['produk_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    // Ambil size dan color dari input form (misalnya dari detail.php)
    $size = isset($_POST['size']) ? $_POST['size'] : ''; // Default kosong jika tidak dipilih
    $color = isset($_POST['color']) ? $_POST['color'] : ''; // Default kosong jika tidak dipilih

    if ($produk_id > 0 && $quantity > 0) {
        // Cek apakah produk dengan ID, size, dan color yang sama sudah ada di keranjang pengguna
        $stmt_check = $conn->prepare("SELECT * FROM keranjang WHERE pengguna_id = ? AND produk_id = ? AND size = ? AND color = ?");
        $stmt_check->bind_param("iiss", $pengguna_id, $produk_id, $size, $color);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Produk sudah ada, update quantity
            $row = $result_check->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;
            $stmt_update = $conn->prepare("UPDATE keranjang SET quantity = ? WHERE keranjang_id = ?");
            $stmt_update->bind_param("ii", $new_quantity, $row['keranjang_id']);
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Kuantitas produk berhasil diperbarui di keranjang!";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui kuantitas produk: " . $conn->error;
            }
        } else {
            // Produk belum ada, tambahkan sebagai item baru
            $stmt_insert = $conn->prepare("INSERT INTO keranjang (pengguna_id, produk_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("iiiss", $pengguna_id, $produk_id, $quantity, $size, $color);
            if ($stmt_insert->execute()) {
                $_SESSION['success_message'] = "Produk berhasil ditambahkan ke keranjang!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan produk ke keranjang: " . $conn->error;
            }
        }
        $stmt_check->close();
    } else {
        $_SESSION['error_message'] = "Produk ID atau Kuantitas tidak valid.";
    }
} else {
    $_SESSION['error_message'] = "Metode request tidak diizinkan.";
}

header("Location: cart.php"); // Arahkan ke halaman keranjang
exit();
?>