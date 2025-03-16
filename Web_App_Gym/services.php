<?php

    include 'Backend_scripts/server_status.php';
            
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    //Initialize variables
    $isLoggedIn = false;

    //Check if the user is logged in by looking for the 'user_login' cookie
    if (isset($_COOKIE['username'])) {
        $isLoggedIn = true;
        $username = $_COOKIE['username'];
    }

    //If the user press the search button, sernds a get meesge to the same page
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        //Store the values from the get message
        $serviceName = isset($_POST['service_name']) ? $_POST['service_name'] : '';
        $day = isset($_POST['day']) ? $_POST['day'] : '';
    }
    
    //Create database connection
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    //Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //Build the base query, we added WHERE 1 in order we can add more content to the query
    $query = "SELECT * FROM services WHERE 1";
    //Stores the parameters that it needs to be on the query
    $params = [];

    //If the user has selected a service name
    if (!empty($serviceName)) {
        $query .= " AND name = ?";
        $params[] = $serviceName;
    }
    //If the user has selected the day
    if (!empty($day)) {
        $query .= " AND day = ?";
        $params[] = $day;
    }

    //Prepare and execute the query
    $stmt = $conn->prepare($query);
    if ($stmt) {
        //If there are paremetres to add to the query
        if (!empty($params)) {
            // Generate the type string
            $typeString = '';
            foreach ($params as $param) {
                $typeString .= 's'; // Append 's' for each parameter
            }

            // Dynamically bind parameters based on their count
            switch (count($params)) {
                //If there is only one parapeter to bind
                case 1:
                    $stmt->bind_param($typeString, $params[0]);
                    break;
                //If there are two parapeters to bind
                case 2:
                    $stmt->bind_param($typeString, $params[0], $params[1]);
                    break;
                default:
                    echo "<script>alert('ERROR');</script>";
                    exit();
                    break;
            }
        }
        //Execute the query
        $stmt->execute();
        $results = $stmt->get_result();

    } 
    else {
        $results = [];
    }
    
    //Close the database connection
    $stmt->close();
    $conn->close();
    
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Search Services</title>
        <style>
            #container{
                flex: 1;
            }
            h1,h2 {
                text-align: center;
                color: white;
                margin-top: 40px;
            }

            form {
                max-width: 600px;
                margin: 20px auto;
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 400px;
            }

            form label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
                color: #333;
            }

            form input, form select {
                width: 100%;
                padding: 10px;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                border-radius: 4px;
                font-size: 16px;
            }

            form button {
                display: block;
                width: 100%;
                padding: 10px;
                background-color: #4CAF50;
                color: #fff;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            form button:hover {
                background-color: #45a049;
            }

            .services-container {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                padding: 20px;
            }

            .service-card {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 20px;
                transition: transform 0.3s ease-in-out;
            }

            .service-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            }

            .service-card h3 {
                font-size: 1.5em;
                margin-top: 0;
                color: #333;
            }

            .service-card p {
                font-size: 1em;
                color: #666;
                margin: 10px 0;
            }

            .service-card .price {
                font-size: 1.2em;
                font-weight: bold;
                color: #4CAF50;
            }

            .service-card .time {
                font-size: 1em;
                color: #777;
                margin-bottom: 10px;
            }

            .service-card .capacity {
                font-size: 1em;
                color: #777;
            }

            .service-card button {
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 1em;
                margin-top: 10px;
                transition: background-color 0.3s;
            }

            .service-card button:hover {
                background-color: #45a049;
            }
        </style>
    </head>

    <?php  
        //Include the header.php to show the header
        include 'Backend_scripts/header.php';
    ?>
    
    <body>
        <div id="container">
            <h1>Search for a Service</h1>
            <form method="POST" action="">

                <label for="service_name">Service Name:</label>
                <select id="service_name" name="service_name">
                    <option value="">-- Select Service --</option>
                    <option value="Morning Yoga">Morning Yoga</option>
                    <option value="Evening Pilates">Evening Pilates</option>
                    <option value="Zumba Fitness">Zumba Fitness</option>
                    <option value="Boxing Training">Boxing Training</option>
                    <option value="Kickboxing Class">Kickboxing Class</option>
                    <option value="Strength Training">Strength Training</option>
                    <option value="CrossFit">CrossFit</option>
                    <option value="Dance Workout">Dance Workout</option>
                    <option value="Running Club">Running Club</option>
                    <option value="HIIT Class">HIIT Class</option>
                </select>

                <label for="day">Day:</label>
                <select id="day" name="day">
                    <option value="">-- Select Day --</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>

                <button type="submit">Search</button>
            </form>

            <h2>Available Services</h2>
            <div class="services-container">
            <?php
                if (!empty($results)) {
                    while($service = $results->fetch_assoc()) { 
                        echo '<div class="service-card">';
                        echo '<h3>' . $service['name'] . '</h3>';
                        echo '<p>' . $service['description'] . '</p>';
                        echo '<div class="price">$' . $service['price'] . '</div>';
                        echo '<div class="time">Day: ' . $service['day'] . ' | Time: ' . $service['time'] . '</div>';
                        if ($isLoggedIn) {
                            echo '<button onclick="window.location.href=\'reserve.php?id=' . $service['id'] . '\'">Reserve</button>';
                        } else {
                            echo '<button onclick="window.location.href=\'view.php?id=' . $service['id'] . '\'">View</button>';
                        }
                        echo '</div>';
                    }
                } else {
                    echo '<p>No services available matching your criteria.</p>';
                }?>
            </div>
        </div>
        
    </body>

    <?php
        //Include the footer.php to show the footer
        include "Backend_scripts/footer.php";
    ?>

</html>
