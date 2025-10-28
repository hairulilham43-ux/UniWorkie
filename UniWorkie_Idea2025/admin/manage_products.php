<?php
session_start();
require_once '../config.php';

// Redirect kalau bukan admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit();
}

$conn = getDBConnection();
$result = $conn->query("
SELECT p.id, p.title, p.category, p.price, 
           u.name AS seller_name
    FROM products p
    JOIN users u ON p.seller_id = u.id

");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products | PURE BATU PAHAT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ====== copy style dari dashboard buyer ====== */
        :root {
            --primary: #4A90E2;
            --primary-light: #6BA4E7;
            --primary-lighter: #EDF5FF;
            --primary-dark: #357ABD;
            --light-bg: #F8FBFF;
            --white: #ffffff;
            --text-dark: #2C3E50;
            --text-light: #546E7A;
            --shadow-color: rgba(74, 144, 226, 0.1);
            --gradient-start: #4A90E2;
            --gradient-end: #64B5F6;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0; padding: 0;
            display: flex;
            background-color: var(--light-bg);
            color: var(--text-dark);
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            height: 100vh;
            position: fixed;
            box-shadow: 4px 0 15px var(--shadow-color);
            padding-top: 20px;
        }
        .logo {
            color: var(--white);
            font-size: 1.75rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
        .nav-menu { list-style: none; padding: 0; margin: 0; }
        .nav-menu li a {
            display: flex; align-items: center;
            padding: 14px 25px;
            color: var(--white);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            opacity: 0.85;
        }
        .nav-menu li a:hover,
        .nav-menu li a.active {
            background: rgba(255,255,255,0.2);
            border-left-color: var(--white);
            opacity: 1;
        }
        .nav-menu li a i { margin-right: 12px; }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }
        .header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; padding: 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        .welcome {
            color: var(--primary);
            font-size: 1.75rem;
            font-weight: bold;
        }
        table {
            width: 100%; border-collapse: collapse;
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        th, td {
            padding: 12px 15px; text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: var(--primary);
            color: var(--white);
        }
        tr:hover { background: var(--primary-lighter); }
        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            text-decoration: none;
            margin-right: 5px;
        }
        .btn-warning {
            background: #FFC107; color: #000;
        }
        .btn-danger {
            background: #DC3545; color: #fff;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">PURE KPTM BP</div>
        <ul class="nav-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage_products.php" class="active"><i class="fas fa-box"></i> Manage Products</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage_sellers.php"><i class="fas fa-store"></i> Manage Sellers</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1 class="welcome">Manage Products</h1>
        </div>

        <!-- Product Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Seller</th><th>Category</th><th>Price</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['seller_name']) ?></td>
                    <td><?= $row['category'] ?></td>
                    <td>RM <?= number_format($row['price'],2) ?></td>
                    <td>
                        <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
