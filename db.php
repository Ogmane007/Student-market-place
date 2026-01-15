<?php
// IMPORTANT: Get these specific values from your InfinityFree cPanel -> MySQL Databases section!
$host = 'sql100.infinityfree.com';   
$db   = 'if0_40637321_sibomabaso';
$user = 'if0_40637321';  
$pass = 'Fisolit2002';   
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>