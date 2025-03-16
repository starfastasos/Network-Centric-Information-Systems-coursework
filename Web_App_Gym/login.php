<?php

    include 'Backend_scripts/server_status.php';
            
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    //Initialize variables
    $message = '';

    //If the user press the vutton to log in, sends a POST meassage to the same file
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password']) && count($_POST) == 2) {
        // Get input from the form
        $inputUsername = $_POST['username'];
        $inputPassword = $_POST['password'];

        try {
            //Database connection and query to fetch services
            $conn = new mysqli($servername, $username_db, $password_db, $dbname);
            //Check the connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            //Query to check if the username exists
            $stmt = $conn->prepare("SELECT id, username, password, role, on_request FROM users WHERE username = ?");
            //Bind the parameters with the variables
            $stmt->bind_param("s", $inputUsername);
            //Execute the query
            $stmt->execute();
            //Store the results
            $result = $stmt->get_result();

            //Check if there is only one user
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                if($user['on_request'] == "True"){
                    echo "<script>alert('Your request has been sent to administration team, and waiting for confirmation. Till then you can\'t login.'); window.location.href = 'login.php';</script>";
                    exit();
                }
                else{
                    //If the credentials are correct, create a cookie
                    if (password_verify($inputPassword, $user['password'])) {
                        $cookie_name_username = "username";
                        $cookie_value_username = $user['username'];    

                        $cookie_name_user_id = "userID";
                        $cookie_value_user_id = $user['id'];

                        $cookie_name_user_role = "userRole";
                        $cookie_value_user_role = $user['role'];

                        setcookie($cookie_name_username, $cookie_value_username, time() + (86400 * 30), "/");
                        setcookie($cookie_name_user_id, $cookie_value_user_id, time() + (86400 * 30), "/");
                        setcookie($cookie_name_user_role, $cookie_value_user_role, time() + (86400 * 30), "/");

                        //If the user is admin, redirect to admin.php
                        if($user['role'] == 'admin'){
                            header('Location: admin.php');
                            exit();   
                        }
                        //If the user is not admin, redirect to index.php
                        else{
                            header('Location: index.php');
                            exit();
                        }
                        exit();

                    } 
                    else {
                        //Invalid credentials
                        $message = "Invalid username or password.";
                    }
                }
            }
            else{
                $message = "No user found.";
            }
            // Close the statement and connection
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
    //If the POST is not "appropriate" shows the access denied file
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['username']) || !isset($_POST['password']) || count($_POST) != 2)) {
        header('Location: access_denied.html');
        exit();
    }

?>


<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <style>
            .form-container {
                flex:1;
                margin: 50px auto;
                padding: 20px;
                margin-top: 300px;
                margin-bottom:500px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                width: 400px;
                height: 500px;
            }
            .form-container h2 {
                text-align: center;
                margin-bottom: 20px;
            }
            .form-container label {
                display: block;
                margin: 10px 0 5px;
            }
            .form-container input, 
            .form-container select {
                width: 100%;
                padding: 10px;
                margin-bottom: 30px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            .form-container button {
                width: 100%;
                padding: 10px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .form-container button:hover {
                background-color: #45a049;
            }
            .form-container p {
                text-align: center;
                color: #666;
                margin-top:50px;
            }
            .form-container a {
                color: #4CAF50;
                text-decoration: none;
            }
            .form-container a:hover {
                text-decoration: underline;
            }
        </style>
    </head>

    <?php  
        //Include the header.php to show the header
        include 'Backend_scripts/header.php';
    ?>

    <body>
        <div class="form-container">
            <h2>Login</h2>
            <?php 
                //If there are erros from the database, show them
            if(!empty($message)){
                echo "<p style='color: red; texr-align: center; margin-top:10px;'>$message</p>";
            }?>
            <form action="login.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br><br>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>

                <button type="submit">Login</button>
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            </form>
        </div>
    </body>

    <?php
        //Include the script to show the header 
        include 'Backend_scripts/footer.php';
    ?>

</html>
