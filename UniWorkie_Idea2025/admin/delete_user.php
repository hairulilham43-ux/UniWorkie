<?php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $conn = getDBConnection();

    // Prevent admin from deleting themselves or other admins
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    if($res && $res['role'] === 'admin'){
        header("Location: manage_users.php?msg=Cannot+delete+admin+account");
        exit();
    }

    // Delete user (buyer or seller)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        header("Location: " . ($_GET['role'] === 'seller' ? "manage_sellers.php" : "manage_users.php") . "?msg=User+deleted+successfully");
    } else {
        header("Location: " . ($_GET['role'] === 'seller' ? "manage_sellers.php" : "manage_users.php") . "?msg=Error+deleting+user");
    }

    $stmt->close();
    $conn->close();
    exit();
}
header("Location: manage_users.php");
exit();
