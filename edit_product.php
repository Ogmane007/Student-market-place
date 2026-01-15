<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. Get Product ID and Validate Ownership
$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];
$message = '';
$product = null;

if (!$product_id || !is_numeric($product_id)) {
    header("Location: dashboard.php?message=Invalid product ID.");
    exit();
}

// Fetch existing product data for pre-filling the form and verifying ownership
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: dashboard.php?message=Listing not found or you do not own it.");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();


// 2. Handle Form Submission (UPDATE)
if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $contact = $_POST['contact'];
    $current_image = $product['image'];
    $new_image_name = $current_image;
    
    // --- IMAGE UPLOAD/REPLACEMENT LOGIC ---
    if (!empty($_FILES["book_image"]["name"])) {
        $target_dir = "uploads/";
        $new_image_name = time() . "_" . basename($_FILES["book_image"]["name"]);
        $target_file = $target_dir . $new_image_name;

        if (move_uploaded_file($_FILES["book_image"]["tmp_name"], $target_file)) {
            // Delete old image if it exists and is not the default
            if ($current_image && file_exists($target_dir . $current_image)) {
                unlink($target_dir . $current_image);
            }
            $message .= "Image updated successfully. ";
        } else {
            $message .= "Error uploading new image. Retaining old image. ";
            $new_image_name = $current_image; // Keep old image name
        }
    }

    // SECURE UPDATE QUERY
    $sql_update = "UPDATE products SET title = ?, price = ?, description = ?, contact_info = ?, image = ? WHERE id = ? AND user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    // 'sdsssi' for string, double, string, string, string, integer (id), integer (user_id)
    $stmt_update->bind_param("sdsssii", $title, $price, $desc, $contact, $new_image_name, $product_id, $user_id);

    if ($stmt_update->execute()) {
        // Fetch the updated data to refresh the form
        $product['title'] = $title;
        $product['price'] = $price;
        $product['description'] = $desc;
        $product['contact_info'] = $contact;
        $product['image'] = $new_image_name;

        $message = "Listing updated successfully!";
    } else {
        $message = "Database Error: Could not update listing.";
    }
    $stmt_update->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($product['title']); ?></title>
    <link rel="stylesheet" href="css/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<nav>
    <a href="index.php"><h1>Student Marketplace</h1></a>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container auth-form-container">
    <h2>Edit Listing: <?php echo htmlspecialchars($product['title']); ?></h2>
    <a href="dashboard.php">&larr; Back to Dashboard</a>

    <?php if ($message): ?>
        <p class="<?php echo strpos($message, 'success') !== false ? 'success-message' : 'error-message'; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Book Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required>
        
        <label>Price (R)</label>
        <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        
        <label>Condition/Description</label>
        <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
        
        <label>Contact Info (Email or Phone)</label>
        <input type="text" name="contact" value="<?php echo htmlspecialchars($product['contact_info']); ?>" required>
        
        <label>Current Image:</label>
        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Book Image" style="max-width: 100%; height: auto; margin-bottom: 10px; border-radius: 5px;">
        
        <label>Replace Image (Optional):</label>
        <input type="file" name="book_image">
        
        <button type="submit" name="update">Update Listing</button>
    </form>
</div>

</body>
</html>