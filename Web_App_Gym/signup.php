<?php
    include 'Backend_scripts/server_status.php';
        
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    // Initialize variables
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['surname']) && 
        isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['phone']) 
        && isset($_POST['country']) && isset($_POST['city']) && isset($_POST['address']) && isset($_POST['role']) && count($_POST) == 10) {
        // Get input from the form
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $country = $_POST['country'];
        $city = $_POST['city'];
        $address = $_POST['address'];
        $role = $_POST['role'];

        //Validate input
        //Check if all the variables are setted
        if (
            empty($name) || empty($surname) || empty($username) || empty($password) ||
            empty($email) || empty($phone) || empty($country) || empty($city) || empty($address) ||
            !in_array($role, ['admin', 'member'])) {
            $message = 'All fields are required.';
        } 
        //Check the email
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format.';
        }
        //Check the phnone
        elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $message = 'Phone number must be exactly 10 digits.';
        } 
        else {
            try {        
            // Connect to MySQL without specifying a database
            $conn = new mysqli($servername, $username_db, $password_db, $dbname);

            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss",$username,$email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = 'Username or email already exists.';
            } 
            else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insert user into the database
                $stmt = $conn->prepare("
                    INSERT INTO users (name, surname, username, password, email, phone, country, city, address, role)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param('ssssssssss',$name,$surname,$username,$hashedPassword,$email,$phone,$country,$city,$address,$role);
                $stmt->execute();
                
                echo "<script>
                    alert('User registered successfully!');
                    window.location.href = 'index.php';
                </script>";
                exit();                
            }
            //Close the connection
            $stmt->close();
            $conn->close();

            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
            }
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['name'], $_POST['surname'], $_POST['username'], $_POST['password'], 
            $_POST['email'], $_POST['phone'], $_POST['country'], $_POST['city'], $_POST['address'], $_POST['role']) || count($_POST) !== 10)){
        
        header('Location: access_denied.html');
        exit();
    }

?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Registration</title>
        <style>
            .form-container {
                flex:1;
                max-width: 600px;
                margin: 50px auto;
                margin-top: 100px;
                padding: 20px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                width: 400px;
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
            }
        </style>
    </head>

    <?php  
        //Include the header.php to show the header
        include 'Backend_scripts/header.php';
    ?>

    <body>
        <div class="form-container">
            <h2>Register</h2>
            <?php 
                //If there are erros from the database, show them
                if(!empty($message)){
                    echo "<p style='color: red; texr-align: center;'>$message</p>";
            }?>
            <form action="signup.php" method="POST" onsubmit="return validateForm()">
                <label for="name">First Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="surname">Last Name:</label>
                <input type="text" id="surname" name="surname" required>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="phone">Phone (10 digits):</label>
                <input type="text" id="phone" name="phone" required>

                <script src="Backend_scripts/get_countries_cities.js"></script>
                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    setupCountryCitySelectors('country', 'city');
                        });
                </script>

                <label for="country">Country:</label>
                <select id="country" name="country" required>
                    <option value="">Select Country</option>
                </select>

                <label for="city">City:</label>
                <select id="city" name="city" required>
                    <option value="">Select City</option>
                </select>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>

                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                </select>

                <button type="submit">Register</button>
            </form>
        </div>

    
    
    
    <script>
            function validateForm() {
                // Validate name and surname (only letters)
                const name = document.getElementById('name').value.trim();
                const surname = document.getElementById('surname').value.trim();
                const nameRegex = /^[a-zA-Z]+$/;

                if (!nameRegex.test(name) || !nameRegex.test(surname)) {
                    alert("First Name and Last Name must contain only letters.");
                    return false;
                }

                // Validate username (min 5 characters)
                const username = document.getElementById('username').value.trim();
                if (username.length < 5) {
                    alert("Username must be at least 5 characters long.");
                    return false;
                }

                // Validate password (min 8 characters, 1 uppercase, 1 digit)
                const password = document.getElementById('password').value;
                const passwordRegex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

                if (!passwordRegex.test(password)) {
                    alert("Password must be at least 8 characters long and include at least 1 uppercase letter and 1 number.");
                    return false;
                }

                // Validate phone (exactly 10 digits)
                const phone = document.getElementById('phone').value.trim();
                const phoneRegex = /^\d{10}$/;

                if (!phoneRegex.test(phone)) {
                    alert("Phone number must be exactly 10 digits.");
                    return false;
                }

                // Validate email (basic email pattern)
                const email = document.getElementById('email').value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailRegex.test(email)) {
                    alert("Please enter a valid email address.");
                    return false;
                }

                // Validate country and city
                const country = document.getElementById('country').value;
                const city = document.getElementById('city').value;

                if (country === "") {
                    alert("Please select a country.");
                    return false;
                }
                if (city === "") {
                    alert("Please select a city.");
                    return false;
                }

                // Validate address (not empty)
                const address = document.getElementById('address').value.trim();
                if (address === "") {
                    alert("Address cannot be empty.");
                    return false;
                }

                // Validate role
                const role = document.getElementById('role').value;
                if (role !== "member" && role !== "admin") {
                    alert("Please select a valid role.");
                    return false;
                }

                // If all validations pass
                return true;
            }
    </script>

    </body>

    <?php
        include 'Backend_scripts/footer.php';
    ?>

</html>
