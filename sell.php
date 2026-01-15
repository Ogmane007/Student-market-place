<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if (isset($_POST['sell'])) {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $contact = $_POST['contact'];
    $user_id = $_SESSION['user_id'];
    $image_name = '';

    // --- IMAGE UPLOAD LOGIC START ---
    if (!empty($_FILES["book_image"]["name"])) {
        $target_dir = "uploads/";
        $image_name = time() . "_" . basename($_FILES["book_image"]["name"]);
        $target_file = $target_dir . $image_name;

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["book_image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["book_image"]["tmp_name"], $target_file)) {
                // Image successfully uploaded
            } else {
                $message = "Sorry, there was an error uploading your file.";
                $image_name = ''; // Reset image name if upload failed
            }
        } else {
            $message = "File is not a valid image.";
            $image_name = '';
        }
    }
    // --- IMAGE UPLOAD LOGIC END ---

    // SECURE QUERY USING PREPARED STATEMENT
    $sql = "INSERT INTO products (user_id, title, price, description, contact_info, image) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    // 'i' for integer (user_id), 's' for string (title, desc, contact, image), 'd' for double (price)
    $stmt->bind_param("isdsss", $user_id, $title, $price, $desc, $contact, $image_name);

    if ($stmt->execute()) {
        $message = "<p class='success-message'>Book listed successfully! <a href='index.php'>View Listings</a> or <a href='dashboard.php'>Manage Listing</a></p>";
    } else {
        $message = "<p class='error-message'>Database Error: Could not list book.</p>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell a Textbook</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<nav>
    <a href="index.php"><h1>Student Marketplace</h1></a>
    <?php if(isset($_SESSION['user_id'])): ?>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
    <?php endif; ?>
</nav>

<div class="container auth-form-container">
    <h2>Sell a Textbook</h2>
    <?php echo $message; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Book Title</label>
        <input type="text" name="title" required>
        <label>Price (R)</label>
        <input type="number" name="price" step="0.01" required>
        <label>Condition/Description</label>
        <textarea name="description" placeholder="E.g., Mint condition, used once..."></textarea>
        <label>Contact Info (Email or Phone)</label>
        <input type="text" name="contact" required>
        
        <label>Book Cover Image:</label>
        <input type="file" name="book_image" required>
        
        <button type="submit" name="sell">Post Ad</button>
    </form>
</div>

</body>
</html>