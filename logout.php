<?php
session_start(); // Start the session if one hasn't been started yet

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie (if it exists)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect the user to the home page or login page
header("Location: index.php"); 
exit; // Terminate script execution after redirect
?>