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

    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        header("Location: manage_products.php?msg=Product+deleted+successfully");
    } else {
        header("Location: manage_products.php?msg=Error+deleting+product");
    }

    $stmt->close();
    $conn->close();
    exit();
}
header("Location: manage_products.php");
exit();
