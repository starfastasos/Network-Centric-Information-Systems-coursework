<?php
    // Expire the user_login cookie
    setcookie('username', '', time() - 3600, "/");

    // Redirect to the homepage or login page
    header('Location: ../index.php');
    exit();
?>
