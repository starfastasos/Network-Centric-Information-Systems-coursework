<?php
    // Check if the user is logged in by looking for the 'user_login' cookie
    $isLoggedIn = false;
    $username = "";
    $isAdmin = false;

    if (isset($_COOKIE['username'])) {
        $isLoggedIn = true;
        $username = $_COOKIE['username'];

        if(isset($_COOKIE['userRole']) && $_COOKIE['userRole']=="admin"){
            $isAdmin = true;
        }
    }

    function setBackground($imageUrl) {
        echo "<style>
        body {
            background-image: url('$imageUrl');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        </style>";
    }

?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        font-family: Arial, sans-serif;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        background-image: url('Images/background.png'); 
        background-size: cover;
        background-repeat: no-repeat; 
        background-position: center;
    }
        
    header {
        background: linear-gradient(135deg, #5A8DEE, #5DC560);
        color: #fff;
        padding: 10px 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%; 
        margin: 0; 
    }


    .nav-logo {
        display: flex;
        align-items: center;
        flex: 1; 
    }

    .nav-logo img {
        width: 90px;
        height: 100px;
        margin-right: 10px; 
    }


    .nav-links {
        display: flex;
        align-items: center;
        gap: 20px;
        flex: 2; 
        justify-content: center;
    }

    .nav-links a {
        text-decoration: none;
        color: #fff;
        font-size: 1.25rem;
        font-weight: bold;
        transition: color 0.3s ease;
        padding: 0 10px; 
    }

    .nav-links a:hover {
        color: #d4f4ff;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1; 
        justify-content: flex-end; 
    }

    .user-info span {
        font-size: 1rem; 
        font-weight: 500;
    }

    .nav-button {
        background-color: #fff;
        color: #5A8DEE;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: bold;
        font-size: 1rem;
    }

    .nav-button:hover {
        background-color: #5A8DEE;
        color: #fff;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        nav {
            flex-wrap: wrap;
        }

        .nav-links {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            margin-top: 10px;
        }

        .user-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            margin-top: 10px;
        }
    }
</style>

<header>
    <nav>
        <div class="nav-logo">
            <img src="Images/logo.png">
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="services.php">Services</a>
            <?php if($isAdmin): ?>
                <a href="admin.php">Admin Dashboard</a>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <?php if ($isLoggedIn): ?>
                <span>Welcome, <?php echo $username; ?>!</span>
                <button class="nav-button" onclick="window.location.href='Backend_scripts/logout.php'">Logout</button>
                <button class="nav-button" onclick="window.location.href='history.php'">👤</button>
            <?php else: ?>
                <button class="nav-button" onclick="window.location.href='login.php'">Login/Sign Up</button>
            <?php endif; ?>
        </div>
    </nav>
</header>
