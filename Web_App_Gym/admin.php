<?php

    include 'Backend_scripts/server_status.php';
        
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];



    //Check if the cookie with value user_id is setted
    if (isset($_COOKIE['username']) && $_COOKIE['userRole']== "admin") {
        
        $message = "";

        // Create connection to MySQL server
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        //Query to get the users on request
        $stmt = $conn->prepare("SELECT * FROM users WHERE on_request = 'True'");    
        $stmt->execute();
        $resultUsersOnRequest = $stmt->get_result();

        //Query to get the users
        $stmt = $conn->prepare("SELECT * FROM users");    
        $stmt->execute();
        $resultUsers = $stmt->get_result();

        //Query to get the services
        $stmt = $conn->prepare("SELECT * FROM services");    
        $stmt->execute();
        $resultServices = $stmt->get_result();

        //Query to get the trainers
        $stmt = $conn->prepare("SELECT * FROM trainers");    
        $stmt->execute();
        $resultTrainers = $stmt->get_result();

        //Query to get the discounts
        $stmt = $conn->prepare("SELECT * FROM discounts");    
        $stmt->execute();
        $resultDiscounts = $stmt->get_result();
        
        //Query to get the news
        $stmt = $conn->prepare("SELECT * FROM news");    
        $stmt->execute();
        $resultNews = $stmt->get_result();
        
    }

    else{
        header('Location: access_denied.html');
        exit();
    }
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel</title>
        <style>
            /* Container */
            #container {
                flex: 1;
                padding: 20px;
                width: 100%;
                margin: 20px auto;
            }
            h1{
                font-size: 40px;
                color: white;
                text-align: center; 
                background: linear-gradient(to right,rgb(18, 243, 232),rgb(211, 223, 45));
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
                padding: 10px 15px;
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                margin: 50px auto;
            }
            h2, h3 {
                color: white;
                text-align: center;
                margin: 20px 0;
            }

            /* Table Styling */
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                background-color: white;
            }

            th, td {
                border: 1px solid #ddd;
                padding: 10px;
                resize: horizontal; 
                overflow: auto;
                text-align: center;
            }

            th {
                background-color: #2575fc;
                color: white;
                font-weight: bold;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            tr:hover {
                background-color: #e6f7ff;
            }

            /* Input and Button */
            input[type="text"],
            input[type="email"],
            input[type="number"],
            textarea,
            select {
                padding: 10px;
                margin: 5px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
                width: 100%;
            }
            textarea{
                height: 100px;
                width: 200px;
            }
            button {
                padding: 10px 15px;
                border: none;
                border-radius: 5px;
                background-color: #a9a9a9;
                color: white;
                cursor: pointer;
                font-size: 14px;
                transition: background-color 0.3s;
            }

            button:hover {
                background-color: #1a5bdb;
            }
            .accept {
                background-color: #28a745; /* Green */
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .accept:hover {
                background-color: #218838; /* Darker Green */
            }

            .delete {
                background-color: #dc3545; /* Red */
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .delete:hover {
                background-color: #c82333; /* Darker Red */
            }

            .edit {
                background-color: #007bff; /* Blue */
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .edit:hover {
                background-color: #0056b3; /* Darker Blue */
            }

            .insert {
                background-color: #6f42c1; /* Purple */
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .insert:hover {
                background-color: #5a32a5; /* Darker Purple */
            }

        </style>
    </head>

    <?php  
        //Include the header.php to show the header
        include 'Backend_scripts/header.php';
    ?>

    <body>
        <div id="container">
            <h1>Admin Panel</h1>

            <!-- Users on request -->
            <h2>Users on Request</h2>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>Address</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <tbody>
                <?php while($userOnRequest = $resultUsersOnRequest->fetch_assoc()){
                    echo "<tr>
                        <td>".$userOnRequest['id']."</td>
                        <td>".$userOnRequest['username']."</td>
                        <td>".$userOnRequest['name']."</td>
                        <td>".$userOnRequest['surname']."</td>
                        <td>".$userOnRequest['email']."</td>
                        <td>".$userOnRequest['phone']."</td>
                        <td>".$userOnRequest['country']."</td>
                        <td>".$userOnRequest['city']."</td>
                        <td>".$userOnRequest['address']."</td>
                        <td>".$userOnRequest['role']."</td>
                        <td>
                            <form action='Backend_scripts/request.php' method='POST'>
                                <input type='hidden' name='id' value=".$userOnRequest['id'].">
                                <button class='accept' name='action' value='acceptUser'>Accept</button>
                                <button class='delete' name='action' value='rejectUser'>Reject</button>
                            </form>
                        </td>
                    </tr>";
                }?>
                </tbody>
            </table>

            <!-- Edit Users -->
            <h2>Users</h2>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>Address</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <tbody>
                    <?php  
                        while ($user = $resultUsers->fetch_assoc()) {
                            //Edit only users that is accepted
                            if($user['on_request'] == "False"){
                                //Generate unique IDs for country and city dropdowns
                                $countryId = 'country_' . $user['id'];
                                $cityId = 'city_' . $user['id'];
                                echo "<tr><form action='Backend_scripts/request.php' method='POST' onsubmit='return validateUserForm(this)'>
                                            <td><input type='text' name='id' value='" . $user['id'] . "' readonly></td>
                                            <td><input type='text' name='username' placeholder='" . $user['username'] . "' value='" . $user['username'] . "' required></td>
                                            <td><input type='text' name='name' placeholder='" . $user['name'] . "' value='" . $user['name'] . "' required></td>
                                            <td><input type='text' name='surname' placeholder='" . $user['surname'] . "' value='" . $user['surname'] . "' required></td>
                                            <td><input type='email' name='email' placeholder='" . $user['email'] . "' value='" . $user['email'] . "' required></td>
                                            <td><input type='number' name='phone' placeholder='" . $user['phone'] . "' value='" . $user['phone'] . "' required></td>

                                            <script src='Backend_scripts/get_countries_cities.js'></script>
                                            <script>
                                            document.addEventListener('DOMContentLoaded', () => {
                                                setupCountryCitySelectors('".$countryId."', '".$cityId."');
                                                    });
                                            </script>

                                            <td>
                                                <select id='$countryId' class='country' name='country' required>
                                                    <option value='" . $user['country'] . "' selected>" . $user['country'] . "</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select id='$cityId' class='city' name='city' required>
                                                    <option value='" . $user['city'] . "' selected>" . $user['city'] . "</option>
                                                </select>
                                            </td>
                                            <td><input type='text' name='address' value='" . $user['address'] . "' placeholder='" . $user['address'] . "' required></td>
                                            <td>
                                                <select id='day' name='role' required>
                                                    <option value='' disabled selected>".$user['role']."</option>
                                                    <option value='member'>member</option>
                                                    <option value='admin'>admin</option>
                                                </select>
                                            </td>
                                            <td><button type='submit' class='edit' name='action' value='editUser'>Edit</button>
                                            <button class='delete' name='action' value='deleteUser'>Delete</button></td>
                                    </form></tr>";
                            }
                        }
                    ?>
                </tbody>
            </table>

            <!-- Edit Services -->
            <h2>Services</h2>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Max Capacity</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Trainer ID</th>
                    <th>Actions</th>
                </tr>
                <tbody>
                <?php  
                    while ($service = $resultServices->fetch_assoc()) {
                        echo "<tr><form action='Backend_scripts/request.php' method='POST' onsubmit='return validateServiceForm(this)'>
                                <td><input type='text' name='id' value='" . $service['id'] . "' readonly></td>
                                <td><input type='text' name='name' placeholder='" . $service['name'] . "' value='" . $service['name'] . "' required></td>
                                <td><input type='text' name='description' placeholder='" . $service['description'] . "' value='" . $service['description'] . "' required></td>
                                <td><input type='text' name='type' placeholder='" . $service['type'] . "' value='" . $service['type'] . "' required></td>
                                <td><input type='number' min='0' step='0.01' name='price' placeholder='" . $service['price'] . "' value='" . $service['price'] . "' required></td>
                                <td><input type='number' min='1' name='max_capacity' placeholder='" . $service['max_capacity'] . "' value='" . $service['max_capacity'] . "' required></td>
                                <td><select id='day' name='day'>
                                        <option value='' disabled selected>".$service['day']."</option>
                                        <option value='Monday'>Monday</option>
                                        <option value='Tuesday'>Tuesday</option>
                                        <option value='Wednesday'>Wednesday</option>
                                        <option value='Thursday'>Thursday</option>
                                        <option value='Friday'>Friday</option>
                                        <option value='Saturday'>Saturday</option>
                                        <option value='Sunday'>Sunday</option>
                                    </select></td>";
                                $stmt = $conn->prepare("SELECT id FROM trainers WHERE service_id = ?");
                                $stmt->bind_param("i", $service['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while($row = $result->fetch_assoc()){
                                    $trainerID = $row['id'];  
                                }
                                echo"<td><input type='time' name='time' value='" . $service['time'] . "' required></td>";
                                if(empty($trainerID)){
                                    echo"<td><input type='number' min='1' name='trainerID' placeholder='No ID defined' required></td>";
                                }
                                else{
                                    echo"<td><input type='number' min='1' name='trainerID' placeholder='" . $trainerID . "' value='" . $trainerID . "' required></td>";
                                }
                                echo"<td>
                                    <button class='edit' name='action' value='editService'>Edit</button>
                                    <button class='delete' name='action' value='deleteService'>Delete</button>
                                </td>
                            </form></tr>";
                    }
                    ?>

                </tbody>
            </table>

            <!-- Insert Service -->
            <h2>Add a Service</h2>
            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Max Capacity</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Trainer ID</th>
                    <th>Action</th>
                </tr>
                <tbody>
                    <tr><form action='Backend_scripts/request.php' method='POST' onsubmit='return validateServiceForm(this)'>
                    <td><input type='text' name='name' required></td>
                    <td><textarea name='description' required></textarea></td>
                    <td><input type='text' name='type' required></td>
                    <td><input type='number' min='0' step='0.01' name='price' required></td>
                    <td><input type='number' min='1' name='max_capacity' required></td>
                    <td>
                        <select name='day' required>
                            <option value='Monday'>Monday</option>
                            <option value='Tuesday'>Tuesday</option>
                            <option value='Wednesday'>Wednesday</option>
                            <option value='Thursday'>Thursday</option>
                            <option value='Friday'>Friday</option>
                            <option value='Saturday'>Saturday</option>
                            <option value='Sunday'>Sunday</option>
                        </select>
                    </td>
                    <td><input type='time' name='time' required></td>
                    <td><input type='number' min='1' name='trainerId' required></td>
                    <td><button class='insert' name='action' value='addService'>Insert</button></td>
                    </form></tr>

                </tbody>
            </table>
            
            <!-- Edit Trainer -->
            <h2>Trainers</h2>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Service ID</th>
                    <th>Actions</th>
                </tr>
                <tbody>
                <?php  
                    while ($trainer = $resultTrainers->fetch_assoc()) {
                        echo "<tr><form action='Backend_scripts/request.php' method='POST' onsubmit='return validateTrainerForm(this)'>
                                <td><input type='text' name='id' placeholder='" . $trainer['id'] . "' value='" . $trainer['id'] . "' readonly></td>
                                <td><input type='text' name='name' placeholder='" . $trainer['name'] . "' value='" . $trainer['name'] . "' required></td>
                                <td><input type='text' name='surname' placeholder='" . $trainer['surname'] . "' value='" . $trainer['surname'] . "' required></td>
                                <td><input type='number' min='1' name='service_id' placeholder='" . $trainer['service_id'] . "' value='" . $trainer['service_id'] . "' required></td>
                                <td>
                                    <button class='edit' name='action' value='editTrainer'>Edit</button>
                                    <button class='delete' name='action' value='deleteTrainer'>Delete</button>
                                </td>
                            </form></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <!-- Insert Trainer -->
             <h2>Add a Trainer</h2>
            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Service ID</th>
                    <th>Action</th>
                </tr>
                <tbody>
                    <tr><form action='Backend_scripts/request.php' method='POST'>
                    <td><input type='text' name='name' required></td>
                    <td><input type='text' name='surname' required></td>
                    <td><input type='number' name='service_id' min='0' placeholder="0 for no service assignment" required></td>
                    <td><button class='insert' name='action' value='addTrainer'>Insert</button></td>
                    </form></tr>
                </tbody>
            </table>


            <!-- Edit Discounts-->
            <h2>Discounts</h2>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const datePickers = document.querySelectorAll('.datePicker');
                    const currentDate = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format

                    datePickers.forEach(datePicker => {
                        datePicker.addEventListener('change', (event) => {
                            const selectedDate = event.target.value;

                            // Check if the selected date is in the past
                            if (selectedDate < currentDate) {
                                alert("The selected date is in the past. Please choose a valid date.");
                                event.target.style.backgroundColor = '#F88379'; // Highlight in red
                                event.target.style.border = '3px solid'; // Add visible border
                            } else {
                                event.target.style.backgroundColor = ''; // Reset background color
                                event.target.style.border = ''; // Reset border
                            }
                        });
                    });
                });
            </script>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Discount Percentage</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Service ID</th>
                    <th>Actions</th>
                </tr>
                <tbody>
                <?php  
                    while ($discount = $resultDiscounts->fetch_assoc()) {
                        $imageData = !empty($discount['image_data']) ? 'data:image/png;base64,' . base64_encode($discount['image_data']) : ''; // Ensure $discount is used here

                        echo "<tr><form action='Backend_scripts/request.php' method='POST' enctype='multipart/form-data' onsubmit='return validateDiscountForm(this)'>
                                <td><input type='text' name='id' placeholder='" . $discount['id'] . "' value='" . $discount['id'] . "' readonly></td>
                                <td><input type='text' name='title' placeholder='" . $discount['title'] . "' value='" . $discount['title'] . "' required></td>
                                <td><textarea name='description' placeholder='" . $discount['description'] . "' required>" . $discount['description'] . "</textarea></td>
                                <td>
                                    <p>Current Image:</p>
                                    <div style='background-image: url(\"$imageData\"); background-size: cover; background-position: center; width: 100%; height: 150px; margin: 0px;'></div>
                                    <p>New Image:</p>
                                    <input type='file' name='image' accept='image/png' required>
                                </td>
                                <td><input type='number' min='0' step='0.01' name='discount_percentage' placeholder='" . $discount['discount_percentage'] . "' value='" . $discount['discount_percentage'] . "' required></td>";

                                $current_date = date('Y-m-d'); // Get the current date
                                $highlight_class_end = ''; // Initialize an empty string for the class
                                $highlight_class_start = ''; // Initialize an empty string for the class

                                // Check if the end date has passed
                                if (!empty($discount['end_date']) && $discount['end_date'] < $current_date) {
                                    $highlight_class_end = "title='The current date has passed' style='background-color:#F88379; border: 3px solid;'"; // Add your highlight style here
                                }

                                // Check if the start date has passed
                                if (!empty($discount['start_date']) && $discount['start_date'] < $current_date) {
                                    $highlight_class_start = "title='The current date has passed' style='background-color:#FD8535; border: 3px solid;'"; // Add your highlight style here
                                }

                        echo "<td><input class='datePicker' type='date' name='start_date' value='" . $discount['start_date'] . "' required $highlight_class_start></td>
                            <td><input class='datePicker' type='date' name='end_date' value='" . $discount['end_date'] . "' required $highlight_class_end></td>
                            <td><input type='number' min='1' step='1' name='service_id' placeholder='" . $discount['service_id'] . "' value='" . $discount['service_id'] . "' required></td>
                            <td>
                                <button class='edit' name='action' value='editDiscount'>Edit</button>
                            
                            </form>
                            <form action='Backend_scripts/request.php' method='POST'>
                                <input type='hidden' name='id' value='" . $discount['id'] . "'>
                                <button class='delete' name='action' value='deleteDiscount'>Delete</button>
                                </td>
                            </form>
                            </tr>";
                    }
                ?>
                </tbody>
            </table>

            <!-- Insert Discount-->
            <h2>Add a Discount</h2>
            <table border="1">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Discount Percentage</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Service ID</th>
                    <th>Action</th>
                </tr>
                <tbody>
                    <tr><form action='Backend_scripts/request.php' method='POST' enctype="multipart/form-data" onsubmit='return validateDiscountForm(this)'>
                    <td><input type='text' name='title' required></td>
                    <td><textarea name='description' required></textarea></td>
                    <td><input type='file' name='image' accept='image/png' required></td>
                    <td><input type='number' min='0' step='0.01' name='discount_percentage'required></td>
                    <td><input class='datePicker' type='date' name=start_date min='1' required></td>
                    <td><input class='datePicker' type='date' name=end_date min='1' required></td>                        
                    <td><input type='number' min='1' name='serviceId' required></td>
                    <td><button class='insert' name='action' value='addDiscount'>Insert</button></td>
                    </form></tr>
                </tbody>
            </table>

            <!-- Edit News -->
            <h2>News</h2>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
                <tbody>
                <?php
                    while ($new = $resultNews->fetch_assoc()) {
                        $imageData = !empty($new['image_data']) ? 'data:image/png;base64,' . base64_encode($new['image_data']) : ''; // Ensure $discount is used here
                        echo "<tr><form action='Backend_scripts/request.php' method='POST' enctype='multipart/form-data' onsubmit='return validateNewForm(this)'>
                                <td><input type='text' name='id' placeholder='" . $new['id'] . "' value='" . $new['id'] . "' readonly></td>
                                <td><input type='text' name='title' placeholder='" . $new['title'] . "' value='" . $new['title'] . "' required></td>
                                <td><textarea name='description' placeholder='" . $new['description'] . "' required>" . $new['description'] . " </textarea></td>
                                <td>
                                    <p>Current Image:</p>
                                    <div style='background-image: url(\"$imageData\"); background-size: cover; background-position: center; width: 100%; height: 150px; margin: 0px;'></div>
                                    <p>New Image:</p>
                                    <input type='file' name='image' accept='image/png' required>
                                </td>
                                <td>
                                    <button class='edit' name='action' value='editNew'>Edit</button>
                            </form>
                            <form action='Backend_scripts/request.php' method='POST'>
                                <input type='hidden' name='id' value='" . $new['id'] . "'>
                                <button class='delete' name='action' value='deleteNew'>Delete</button>
                            </form>
                            </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Insert News-->
            <h2>Add a New</h2>
            <table border="1">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
                <tbody>
                    <tr><form action='Backend_scripts/request.php' method='POST' enctype="multipart/form-data" onsubmit='return validateNewForm(this)'>
                    <td><input type='text' name='title' required></td>
                    <td><textarea name='description' required></textarea></td>
                    <td><input type='file' name='image' accept='image/png' required></td>
                    <td><button class='insert' name='action' value='addNew'>Insert</button></td>
                    </form></tr>
                </tbody>
            </table>

        </div>

        <script>
           // Get today's date in the format 'YYYY-MM-DD'
            const today = new Date().toISOString().split('T')[0];

            // Select all elements with the class 'datePicker' and set the min attribute
            const datePickers = document.querySelectorAll('.datePicker');
            datePickers.forEach(picker => {
                picker.setAttribute('min', today);
            });

        </script>

    </body>

    <?php
        //Include the footer.php to show the footer
        include "Backend_scripts/footer.php";
    ?>

<script>

    function validateUserForm(form) {
        // Validate name and surname (only letters)
        const name = form.elements['name'].value.trim();
        const surname = form.elements['surname'].value.trim();
        const nameRegex = /^[a-zA-Z]+$/;

        if (!nameRegex.test(name) || !nameRegex.test(surname)) {
            alert("First Name and Last Name must contain only letters.");
            return false;
        }

        // Validate username (min 5 characters)
        const username = form.elements['username'].value.trim();
        if (username.length < 5) {
            alert("Username must be at least 5 characters long.");
            return false;
        }

        // Validate phone (exactly 10 digits)
        const phone = form.elements['phone'].value.trim();
        const phoneRegex = /^\d{10}$/;

        if (!phoneRegex.test(phone)) {
            alert("Phone number must be exactly 10 digits.");
            return false;
        }

        // Validate email (basic email pattern)
        const email = form.elements['email'].value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        // Validate country and city
        const country = form.elements['country'].value;
        const city = form.elements['city'].value;

        if (country === "") {
            alert("Please select a country.");
            return false;
        }
        if (city === "") {
            alert("Please select a city.");
            return false;
        }

        // Validate address (not empty)
        const address = form.elements['address'].value.trim();
        if (address === "") {
            alert("Address cannot be empty.");
            return false;
        }

        // Validate role
        const role = form.elements['role'].value;
        if (role !== "member" && role !== "admin") {
            alert("Please select a valid role.");
            return false;
        }

        // If all validations pass
        return true;
    }

    function validateServiceForm(form) {
        // Validate service name
        const name = form.elements['name'].value.trim();
        if (!name) {
            alert("Service name is required.");
            return false;
        }

        // Validate service description
        const description = form.elements['description'].value.trim();
        if (!description) {
            alert("Service description is required.");
            return false;
        }

        // Validate service type
        const type = form.elements['type'].value.trim();
        if (!type) {
            alert("Service type is required.");
            return false;
        }

        // Validate price (non-negative number)
        const price = form.elements['price'].value;
        if (price === "" || isNaN(price) || parseFloat(price) < 0) {
            alert("Price must be a non-negative number.");
            return false;
        }

        // Validate max capacity (positive integer)
        const maxCapacity = form.elements['max_capacity'].value;
        if (maxCapacity === "" || isNaN(maxCapacity) || parseInt(maxCapacity) <= 0) {
            alert("Max capacity must be a positive integer.");
            return false;
        }

        // Validate time (not empty)
        const time = form.elements['time'].value.trim();
        if (!time) {
            alert("Time is required.");
            return false;
        }

        // Validate trainer ID (positive integer)
        const trainerID = form.elements['trainerID'].value;
        if (trainerID === "" || isNaN(trainerID) || parseInt(trainerID) <= 0) {
            alert("Trainer ID must be a positive integer.");
            return false;
        }

        // If all validations pass
        return true;
    }

    function validateTrainerForm(form) {
        // Validate trainer name
        const name = form.elements['name'].value.trim();
        if (!name) {
            alert("Trainer name is required.");
            return false;
        }

        // Validate trainer surname
        const surname = form.elements['surname'].value.trim();
        if (!surname) {
            alert("Trainer surname is required.");
            return false;
        }

        // Validate service ID (positive integer)
        const serviceId = form.elements['service_id'].value;
        if (serviceId === "" || isNaN(serviceId) || parseInt(serviceId) <= 0) {
            alert("Service ID must be a positive integer.");
            return false;
        }

        // If all validations pass
        return true;
    }

    function validateDiscountForm(form) {
        // Validate discount title
        const title = form.elements['title'].value.trim();
        if (!title) {
            alert("Discount title is required.");
            return false;
        }

        // Validate discount description
        const description = form.elements['description'].value.trim();
        if (!description) {
            alert("Discount description is required.");
            return false;
        }

        // Validate image (only PNG files allowed)
        const image = form.elements['image'].value.trim();
        if (!image) {
            alert("An image must be uploaded.");
            return false;
        } else {
            const validImageExtensions = ['png'];
            const fileExtension = image.split('.').pop().toLowerCase();
            if (!validImageExtensions.includes(fileExtension)) {
                alert("Only PNG images are allowed.");
                return false;
            }
        }

        // Validate discount percentage (number between 0 and 100)
        const discountPercentage = form.elements['discount_percentage'].value;
        if (
            discountPercentage === "" ||
            isNaN(discountPercentage) ||
            parseFloat(discountPercentage) < 0 ||
            parseFloat(discountPercentage) > 100
        ) {
            alert("Discount percentage must be a number between 0 and 100.");
            return false;
        }

        // Validate start date
        const startDate = form.elements['start_date'].value.trim();
        if (!startDate) {
            alert("Start date is required.");
            return false;
        }

        // Validate end date
        const endDate = form.elements['end_date'].value.trim();
        if (!endDate) {
            alert("End date is required.");
            return false;
        }

        // Validate date range
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert("Start date cannot be after the end date.");
            return false;
        }

        // Validate service ID (positive integer)
        const serviceId = form.elements['service_id'].value;
        if (serviceId === "" || isNaN(serviceId) || parseInt(serviceId) <= 0) {
            alert("Service ID must be a positive integer.");
            return false;
        }

        // If all validations pass
        return true;
    }

    function validateNewForm(form) {
        // Validate title
        const title = form.elements['title'].value.trim();
        if (!title) {
            alert("News title is required.");
            return false;
        }

        // Validate description
        const description = form.elements['description'].value.trim();
        if (!description) {
            alert("News description is required.");
            return false;
        }

        // Validate image (if provided)
        const image = form.elements['image'].value.trim();
        if (image) {
            const validImageExtensions = ['png'];
            const fileExtension = image.split('.').pop().toLowerCase();
            if (!validImageExtensions.includes(fileExtension)) {
                alert("Only PNG images are allowed for the news.");
                return false;
            }
        }

        // If all validations pass
        return true;
    }
    
</script>


</html>