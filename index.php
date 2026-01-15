<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Marketplace</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<nav>
    <a href="index.php"><h1>Student Marketplace</h1></a>
    <div>
    <?php if(isset($_SESSION['user_id'])): ?>
        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="sell.php">Sell Book</a> 
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
    </div>
</nav>

<div class="container">
    
    <form method="GET" action="index.php" class="search-form">
        <input type="text" name="search" placeholder="Search by title or description..." 
               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button type="submit">Search</button>
        <?php if (!empty($_GET['search'])): ?>
            <a href="index.php" class="clear-search">Clear</a>
        <?php endif; ?>
    </form>

    <h2>Available Textbooks</h2>
    
    <div class="grid">
        <?php
        $search_term = $_GET['search'] ?? '';
        $sql = "SELECT * FROM products ";
        $params = [];
        $types = '';

        if (!empty($search_term)) {
            $sql .= " WHERE title LIKE ? OR description LIKE ? ";
            $like_term = '%' . $search_term . '%';
            $params[] = $like_term; 
            $params[] = $like_term;
            $types .= 'ss';
        }
        
        $sql .= " ORDER BY created_at DESC";

        if (!empty($search_term)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Link the entire card to the detail view
                echo "<a href='view_product.php?id=" . $row['id'] . "' class='card-link'>"; 
                echo "<div class='card'>";
                
                // 1. IMAGE SECTION
                $imagePath = 'uploads/' . $row['image'];
                if (!empty($row['image']) && file_exists($imagePath)) {
                    echo "<img src='$imagePath' alt='Book Image' class='card-img-top'>";
                } else {
                    echo "<div style='height:220px; background:#e9ecef; display:flex; align-items:center; justify-content:center; color:#adb5bd; border-radius:12px 12px 0 0;'>No Image</div>";
                }

                // 2. TEXT CONTENT SECTION (Wrapper)
                echo "<div class='card-body'>";
                    echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                    echo "<div class='price'>R " . number_format($row['price'], 2) . "</div>";
                    echo "<p class='desc'>" . htmlspecialchars(substr($row['description'], 0, 80)) . (strlen($row['description']) > 80 ? '...' : '') . "</p>";
                    echo "<div class='contact'>Seller Contact Info</div>";
                echo "</div>"; // End card-body

                echo "</div>"; // End card
                echo "</a>"; // End card-link
            }
        } else {
            echo "<p>No books listed yet." . (!empty($search_term) ? " Try a different search term." : "") . "</p>";
        }
        ?>
    </div>
</div>

</body>
</html>