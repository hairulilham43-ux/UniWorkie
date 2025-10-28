<?php
session_start();
require_once '../config.php';

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | PURE BATU PAHAT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ===== Copy dari dashboard buyer ===== */
        :root {
            --primary: #4A90E2;
            --primary-light: #6BA4E7;
            --primary-lighter: #EDF5FF;
            --primary-dark: #357ABD;
            --secondary: #64B5F6;
            --accent: #2196F3;
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
            margin: 0;
            padding: 0;
            display: flex;
            background-color: var(--light-bg);
            color: var(--text-dark);
            transition: background-color 0.3s ease;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            height: 100vh;
            position: fixed;
            box-shadow: 4px 0 15px var(--shadow-color);
            padding-top: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logo {
            color: var(--white);
            font-size: 1.75rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: fadeInDown 0.5s ease;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-menu li a {
            display: flex;
            align-items: center;
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
            transform: translateX(5px);
        }

        .nav-menu li a i {
            margin-right: 12px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .nav-menu li a:hover i {
            transform: scale(1.1);
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
            animation: fadeIn 0.5s ease;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-color);
            animation: slideDown 0.5s ease;
        }

        .welcome {
            color: var(--primary);
            font-size: 1.75rem;
            font-weight: bold;
            animation: fadeInLeft 0.5s ease;
        }

        .user-profile {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            background: var(--primary-lighter);
            border-radius: 30px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
            border: 2px solid var(--primary-light);
        }

        .card-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-top: 40px;
        }

        .card {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            width: 220px;
            text-align: center;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .card a {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            text-decoration: none;
            font-weight: 600;
            /* transition: background 0.3s ease; */
        }

        .card a:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        /* Simple animation */
        @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
        @keyframes fadeInLeft { from {opacity:0; transform:translateX(-20px);} to {opacity:1; transform:translateX(0);} }
        @keyframes slideDown { from {opacity:0; transform:translateY(-20px);} to {opacity:1; transform:translateY(0);} }
        @keyframes fadeInDown { from {opacity:0; transform:translateY(-20px);} to {opacity:1; transform:translateY(0);} }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">PURE KPTM BP</div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage_products.php"><i class="fas fa-box"></i> Manage Products</a></li>
            <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage_sellers.php"><i class="fas fa-store"></i> Manage Sellers</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1 class="welcome">Welcome, Admin!</h1>
        </div>

        <!-- Dashboard Content -->
        <h2>Admin Panel Overview</h2>
        <p>Here you can manage products, users, and sellers.</p>

        <div class="card-container">
        <!-- Register Admin Card -->
        <!-- Register Admin Card -->
        <div class="card">
            <i class="fas fa-user-shield"></i>
            <h3>Register Admin</h3>
            <a href="register_admin.php">Go</a>
        </div>

        <!-- Register User Card -->
        <div class="card">
            <i class="fas fa-user-plus"></i>
            <h3>Manage Product</h3>
            <a href="manage_products.php">Go</a>
        </div>

    </div>

    </div>
</body>
</html>
