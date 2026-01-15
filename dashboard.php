<?php
session_start();
include 'db.php';

// Must be logged in to view dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = $_GET['message'] ?? ''; // Display success/error messages from other actions

// Fetch only the products posted by the current user
$stmt = $conn->prepare("SELECT id, title, price, created_at FROM products WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<nav>
    <a href="index.php"><h1>Student Marketplace</h1></a>
    <div>
        <a href="index.php">Listings</a>
        <a href="sell.php">Sell Book</a> 
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2><?php echo htmlspecialchars($_SESSION['username']); ?>'s Dashboard</h2>

    <?php if ($message): ?>
        <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <h3>Your Active Listings (<?php echo $result->num_rows; ?>)</h3>

    <div class="dashboard-table-wrapper">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Posted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><a href="view_product.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
                        <td>R <?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn-action edit">Edit</a>
                            <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this listing?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">You have no active listings. <a href="sell.php">Start selling now!</a></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>