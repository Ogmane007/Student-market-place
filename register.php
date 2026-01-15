<?php 
include 'db.php'; 

$message = '';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    // SECURE QUERY USING PREPARED STATEMENT
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    
    if ($stmt->execute()) {
        $message = "Registration successful! <a href='login.php'>Login here</a>";
    } else {
        // Handle duplicate email error (Error code 1062)
        if ($conn->errno == 1062) {
             $message = "Error: This email address is already registered.";
        } else {
             $message = "Error: Could not register user.";
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<nav>
    <a href="index.php"><h1>Student Marketplace</h1></a>
    <div>
        <a href="login.php">Login</a>
    </div>
</nav>

<div class="container auth-form-container">
    <h2>Create Account</h2>
    <?php if ($message): ?>
        <p class="success-message"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
</div>

</body>
</html>