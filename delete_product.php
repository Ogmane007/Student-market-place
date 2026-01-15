<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];
$redirect_url = "dashboard.php";

if (!$product_id || !is_numeric($product_id)) {
    header("Location: $redirect_url?message=Invalid product ID.");
    exit();
}

// 1. Fetch product data to get image name and verify ownership
$stmt_fetch = $conn->prepare("SELECT image FROM products WHERE id = ? AND user_id = ?");
$stmt_fetch->bind_param("ii", $product_id, $user_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();

if ($result->num_rows !== 1) {
    header("Location: $redirect_url?message=Listing not found or you do not own it.");
    exit();
}

$product = $result->fetch_assoc();
$stmt_fetch->close();

// 2. Delete the physical image file
$image_file = $product['image'];
$target_dir = "uploads/";

if ($image_file && file_exists($target_dir . $image_file)) {
    unlink($target_dir . $image_file);
}

// 3. Delete the record from the database
$stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
$stmt_delete->bind_param("ii", $product_id, $user_id);

if ($stmt_delete->execute()) {
    header("Location: $redirect_url?message=Listing deleted successfully!");
} else {
    header("Location: $redirect_url?message=Error deleting listing.");
}
$stmt_delete->close();
exit();
?>