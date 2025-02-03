<?php

require_once 'inc_db.php';
require_once 'inc_lib.php';
require_once 'inc_session.php';

// Use null coalescing operator to avoid "undefined array key" warnings.
$email = $_POST['email'] ?? '';
$pass  = $_POST['pass'] ?? '';

// Fetch the user row; if no row is found, default to an empty array.
$row = mysqli_fetch_assoc(execQuery("SELECT * FROM users WHERE email=\"" . mysqli_escape($email) . "\"")) ?: [];

// Check if the user is registered and provided the correct password.
if (!empty($row['email']) && !empty($row['pass']) && $row['pass'] === md5($pass)) {
    // Remember in session.
    $_SESSION['id']    = $row['id'];
    $_SESSION['email'] = $row['email'];
    $_SESSION['name']  = $row['fullName'];
    execQuery("UPDATE users SET lastLogin=NOW() WHERE id=" . $row['id']);
} else { // Logout.
    $_SESSION['id']    = 0;
    $_SESSION['email'] = '';
    $_SESSION['name']  = '';
}

// Send the file list.
require_once 'load.php';
?>
