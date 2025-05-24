    <?php
    // session_start();
    // if (!isset($_SESSION['admin_logged_in'])) {
    //     header("Location: ../login.php");
    //     exit;
    // }

    // Contoh data dummy — ganti dengan query dari database
    $total_users = 7;
    $total_barang = 14;
    $pending_pembayaran = 0;
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Admin Dashboard - MOVR</title>
        <!-- <link rel="stylesheet" href="../assets/style.css"> Ganti jika perlu -->
        <style>
            body {
                margin: 0;
                font-family: 'Segoe UI', sans-serif;
                background-color: #f5f5f5;
            }
            .sidebar {
                width: 220px;
                background-color:rgb(121, 122, 135);
                color: white;
                position: fixed;
                top: 0;
                bottom: 0;
                padding: 20px;
            }
            .sidebar h2 {
                font-size: 20px;
                margin-bottom: 30px;
            }
            .sidebar img {
                
                width: 130px;
                margin-bottom: 15px;
            }
            .sidebar a {
                display: block;
                padding: 10px;
                color: white;
                text-decoration: none;
                margin-bottom: 10px;
                border-radius: 5px;
            }
            .sidebar a:hover, .sidebar a.active {
                background-color:rgb(80, 81, 82);
            }
            .main {
                margin-left: 240px;
                padding: 30px;
            }
            .cards {
                display: flex;
                gap: 20px;
                margin-top: 20px;
            }
            .card {
                flex: 1;
                padding: 20px;
                border-radius: 10px;
                color: white;
                font-size: 18px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .card h3 {
                margin: 0 0 10px 0;
                font-size: 20px;
            }
            .bg-red {
                background-color:rgb(110, 110, 112);
            }
            .bg-orange {
                background-color: #ff8800;
            }
            .icon {
                font-size: 24px;
                margin-right: 10px;
                vertical-align: middle;
            }
        </style>
    </head>
    <body>

    <div class="sidebar">
        <img src="../uploads/MOVR[logo].png" alt="Logo" />
        <h2>Admin Dashboard</h2>
        <a href="#" class="active">Dashboard</a>
        <a href="kelola_user.php">Kelola User</a>
        <a href="kelola_produk.php">Kelola Produk</a>
        <a href="kelola_pembayaran.php">Kelola Pembayaran</a>
        <a href="category_admin.php">Category</a>
         <a href="verifikasi_produk.php">Verifikasi Produk</a>
        <!-- <a href="buat_pengguna.php">Buat Pengguna</a>
        <a href="notifikasi.php">Notification</a> -->
        <a href="../index.php">Logout</a>
    </div>

    <div class="main">
        <h1>Dashboard</h1>

        <!-- <div class="cards">
            <div class="card bg-red">
                <h3><span class="icon">👥</span>Total Users</h3>
                <p><?= $total_users ?></p>
            </div>
            <div class="card bg-red">
                <h3><span class="icon">🛒</span>Total barang yang dijual</h3>
                <p><?= $total_barang ?></p>
            </div>
            <div class="card bg-orange">
                <h3><span class="icon">⏳</span>Pembayaran Pending</h3>
                <p><?= $pending_pembayaran ?></p>
            </div>
        </div> -->
    </div>

    </body>
    </html> 
