<?php

    include 'server_status.php';
    
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];
    
    
    // Create connection
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the form was submitted
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
        
        $action = (string) $_POST['action'];
        
        if(isset($_POST['id'])){
            $id = (int) $_POST['id'];
        }

        //User
        if ($action === 'acceptUser') {
            $stmt = $conn->prepare("UPDATE users SET on_request = 'False' WHERE id = ?");
            $stmt->bind_param('i', $id);
            if($stmt->execute()){
                echo "<script>alert('Users with id = " . $id . " has been accepted successfully.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                echo "<script>alert('There was a problem accepting the user with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                exit();
            }
        } 
        elseif ($action === 'rejectUser') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $id);
            if($stmt->execute()){
                echo "<script>alert('User with id = " . $id . " has been rejected successfully.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                echo "<script>alert('There was a problem rejecting the user with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                exit();
            }
        } 
        elseif ($action === 'editUser') {

            if(empty($_POST['username']) || empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['country']) || empty($_POST['city']) || empty($_POST['address']) || empty($_POST['role'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('Invalid email format.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif (!preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
                echo "<script>alert('Phone number must be exactly 10 digits.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                // Check if username or email already exists
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss",$_POST['username'],$_POST['email']);
                $stmt->execute();
                $result = $stmt->get_result();
    
                if ($result->num_rows > 1) {
                    echo "<script>alert('Username or email already exists.'); window.location.href = '../admin.php';</script>";
                    exit();
                } 

                else{
                    $stmt = $conn->prepare("UPDATE users SET username = ?, name = ?, surname = ?, email = ?, phone = ?, country = ?, city = ?, address = ? ,role = ? WHERE id = ?");
                    $stmt->bind_param('ssssissssi', $_POST['username'], $_POST['name'], $_POST['surname'], $_POST['email'], $_POST['phone'], $_POST['country'], $_POST['city'], $_POST['address'],$_POST['role'], $id);
                    if($stmt->execute()){
                        echo "<script>alert('User with id = " . $id . " has been updated successfully.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                    else{
                        echo "<script>alert('There was a problem updating the user with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                }
            }
        }
        elseif ($action === 'deleteUser'){
            
            //Remove the appointmens of that user and update the capacithy of the services that has enrolled
            $stmt = $conn->prepare("SELECT service_id, reserved_at FROM reservations WHERE user_id = ?");
            $stmt->bind_param('i',$_POST['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $conn = new mysqli($servername, $username_db, $password_db, $dbname);
                updateCapacity($id, $row['reserved_at'], $row['service_id'], $conn);
            }
            //Delete the reservations from the table
            $stmt = $conn->prepare("DELETE FROM reservations WHERE user_id = ?");
            $stmt->bind_param('i',$id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i',$id);
            if($stmt->execute()){
                echo "<script>alert('User with id = " . $id . " has been deleted successfully.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                echo "<script>alert('There was a problem deleting the user with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                exit();
            }
        } 

        //Service
        elseif ($action === 'editService') {

            if(empty($_POST['name']) || empty($_POST['description']) || empty($_POST['type']) || empty($_POST['price']) || empty($_POST['max_capacity']) || empty($_POST['day']) || empty($_POST['time'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif(!(float)$_POST['price'] > 0){
                echo "<script>alert('The price must be greater than 0.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif(!(int)$_POST['max_capacity'] > 0){
                echo "<script>alert('The max capacity must be greater than 0.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif (!in_array($_POST['day'], ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])) {
                echo "<script>alert('Invalid day selected.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{

                $stmt = $conn->prepare("SELECT day FROM services WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                //Check if the day changed
                $dayChanged = FALSE;
                $day_message = "";
                if($_POST['day'] != $row['day']){
                    $dayChanged = TRUE;
                    $day_message = "Since the day of the service has been changed, the appointments that already has reserved will be valid for the previous day value.";
                }

                // Check if name of the service alrady exists
                $stmt = $conn->prepare("SELECT * FROM services WHERE name = ?");
                $stmt->bind_param("s",$_POST['name']);
                $stmt->execute();
                $result = $stmt->get_result();
    
                if ($result->num_rows > 1) {
                    echo "<script>alert('Name already exists.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                else{
                    //Check if there is a trainer with the id that the admin entered
                    $stmt = $conn->prepare("SELECT * FROM trainers WHERE id = ?");
                    $stmt->bind_param("i", $_POST['trainerID']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    //If there is the trainer
                    if($result->num_rows > 0){
                        //Update the services table
                        $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, type = ?, price = ?, max_capacity = ?, day = ?, time = ? WHERE id = ?");
                        $stmt->bind_param('sssdissi', $_POST['name'], $_POST['description'], $_POST['type'], $_POST['price'], $_POST['max_capacity'], $_POST['day'], $_POST['time'],$id);
                        $stmt->execute();
                        //Update the trainers table
                        $stmt = $conn->prepare("UPDATE trainers SET service_id = ? WHERE id = ?");
                        $stmt->bind_param('ii', $id, $_POST['trainerID']);

                        if($stmt->execute()){
                            echo "<script>alert('Service with id = " . $id . " has been updated successfully. $day_message'); window.location.href = '../admin.php';</script>";
                            exit();
                        }
                        else{
                            echo "<script>alert('There was a problem updating the service with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                            exit();
                        }
                    }
                    else{
                        echo "<script>alert('There is no trainer with this id. Please select another id.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                }
            }
        } 
        elseif ($action === 'deleteService'){
            //Delete the reservations from the table
            $stmt = $conn->prepare("DELETE FROM reservations WHERE service_id = ?");
            $stmt->bind_param('i',$id);
            $stmt->execute();

            //Update the trainers table
            $stmt = $conn->prepare("UPDATE trainers SET service_id = 0 WHERE service_id = ?");
            $stmt->bind_param('i',$id);
            $stmt->execute();
            
            //Delete the discounts for that service
            $stmt = $conn->prepare("DELETE FROM discounts WHERE service_id = ?");
            $stmt->bind_param('i',$id);
            $stmt->execute();

            //Delete the service from the services table
            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            $stmt->bind_param('i',$id);
            if($stmt->execute()){
                echo "<script>alert('Service with id = " . $id . " has been deleted successfully.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                echo "<script>alert('There was a problem deleting the service with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                exit();
            }
        } 
        elseif ($action === 'addService') {

            if(empty($_POST['name']) || empty($_POST['description']) || empty($_POST['type']) || empty($_POST['price']) || empty($_POST['max_capacity']) || empty($_POST['day']) || empty($_POST['time'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif(!(float)$_POST['price'] > 0){
                echo "<script>alert('The price must be greater than 0.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif(!(int)$_POST['max_capacity'] > 0){
                echo "<script>alert('The max capacity must be greater than 0.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                // Check if name of the service alrady exists
                $stmt = $conn->prepare("SELECT * FROM services WHERE name = ?");
                $stmt->bind_param("s",$_POST['name']);
                $stmt->execute();
                $result = $stmt->get_result();
    
                if ($result->num_rows > 0) {
                    echo "<script>alert('Name already exists.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                else{
                    //Check if there is a trainer with the id that the admin entered
                    $stmt = $conn->prepare("SELECT * FROM trainers WHERE id = ?");
                    $stmt->bind_param("i", $_POST['trainerId']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    //If there is the trainer
                    if($result->num_rows > 0){ 
                        $stmt = $conn->prepare("INSERT INTO services (name, description, type, price, max_capacity, day, time) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param('sssdiss', $_POST['name'], $_POST['description'], $_POST['type'], $_POST['price'], $_POST['max_capacity'], $_POST['day'], $_POST['time']);
                            if($stmt->execute()){
                                echo "<script>alert('Service has been inserted successfully.'); window.location.href = '../admin.php';</script>";
                                exit();
                            }
                            else{
                                echo "<script>alert('There was a problem inserting the service); window.location.href = '../admin.php';</script>";
                                exit();
                            }
                    }
                    else{
                        echo "<script>alert('There is no trainer with this id. Please select another id.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                }
            }
        } 

        //Trainer
        elseif ($action === 'editTrainer') {

            if(empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['service_id'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                //Check if there is a service with the id that the admin entered
                $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
                $stmt->bind_param("i", $_POST['service_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                //If there is the service
                if($result->num_rows > 0){ 
                        $stmt = $conn->prepare("UPDATE trainers SET name = ?, surname = ?, service_id = ? WHERE id = ?");
                        $stmt->bind_param('ssii', $_POST['name'], $_POST['surname'], $_POST['service_id'],$id);
                        if($stmt->execute()){
                            echo "<script>alert('Trainer with id = " . $id . " has been updated successfully.'); window.location.href = '../admin.php';</script>";
                            exit();
                        }
                        else{
                            echo "<script>alert('There was a problem updating the trainer with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                            exit();
                        }
                }
                else{
                    echo "<script>alert('There is no service with this id, please try another one.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
            }

        }
        elseif ($action === 'deleteTrainer'){
            $stmt = $conn->prepare("DELETE FROM trainers WHERE id = ?");
            $stmt->bind_param('i',$_POST['id']);
            if($stmt->execute()){
                echo "<script>alert('Trainer with id = " . $id . " has been deleted successfully.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                echo "<script>alert('There was a problem deleting the trainer with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                exit();
            }
        }
        elseif ($action === 'addTrainer') {
            if(empty($_POST['name']) || empty($_POST['surname']) || !isset($_POST['service_id'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
           else{
                //Check if there is already the a trainer for that service
                $stmt = $conn->prepare("SELECT * FROM trainers WHERE service_id = ? AND service_id > 0");
                $stmt->bind_param("i", $_POST['service_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows > 0){ 
                    echo "<script>alert('There is already a trainer for that service.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                else{
                    $stmt = $conn->prepare("INSERT INTO trainers (name, surname, service_id) VALUES (?, ?, ?)");
                    $stmt->bind_param("ssi", $_POST['name'], $_POST['surname'], $_POST['service_id']);
                    if($stmt->execute()){
                        echo "<script>alert('Trainer has been inserted successfully.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                    else{
                        echo "<script>alert('There was a problem insering the trainer with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                }
           }
        }

        //Discount
        elseif ($action === 'editDiscount') {

            if(empty($_POST['title']) || empty($_POST['description']) || empty($_POST['discount_percentage']) || empty($_POST['start_date']) || empty($_POST['end_date']) || empty($_POST['service_id']) || empty($_FILES['image'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            elseif(!((float)$_POST['discount_percentage'] > 0 && (float)$_POST['discount_percentage'] <= 100)){
                echo "<script>alert('The discount percentage must be between 0 and 100.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                $startDate = new DateTime($_POST['start_date']);
                $endDate = new DateTime($_POST['end_date']);
                $today = new DateTime('today'); // Get today's date at midnight

                if($startDate < $today || $endDate < $today || $startDate > $endDate){
                    echo "<script>alert('The start and end date must be after the today date and start date must be before the end date.'); window.location.href = '../admin.php';</script>";
                    exit();
                }

                else{
                    //Check if there already the discount with this title
                    $stmt = $conn->prepare("SELECT * FROM discounts WHERE title = ?");
                    $stmt->bind_param("s", $_POST['title']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    //If there is the a discount with the same name
                    if($result->num_rows > 1){ 
                        echo "<script>alert('There is already a discount with this title.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                    else{
                        //Check if there is a discount with the service that the admin entered
                        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
                        $stmt->bind_param("i", $_POST['service_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        //If there is the service
                        if($result->num_rows > 0){ 
                            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                                $stmt = $conn->prepare("UPDATE discounts SET title = ? , description = ?, discount_percentage = ? , start_date = ?, end_date = ?, service_id = ? WHERE id = ?");
                                $stmt->bind_param("ssdssii", $_POST['title'], $_POST['description'], $_POST['discount_percentage'], $_POST['start_date'], $_POST['end_date'], $_POST['service_id'], $id);
                            }
                            else{
                                $image = $_FILES['image']['tmp_name']; // Read the file data
                                $imgData = file_get_contents($image);
                                $stmt = $conn->prepare("UPDATE discounts SET title = ? , description = ?, image_data = ?, discount_percentage = ? , start_date = ?, end_date = ?, service_id = ? WHERE id = ?");
                                $stmt->bind_param("sssdssii", $_POST['title'], $_POST['description'], $imgData, $_POST['discount_percentage'], $_POST['start_date'], $_POST['end_date'], $_POST['service_id'], $id);
                            }
                            
                            if($stmt->execute()){
                                echo "<script>alert('Discount with id = " . $id . " has been updated successfully.'); window.location.href = '../admin.php';</script>";
                                exit(); 
                            }
                            else{
                                echo "<script>alert('There was a problem updating the discount with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                                exit();
                            }
                        }
                        else{
                            echo "<script>alert('There is no service with this id. Please select another id.'); window.location.href = '../admin.php';</script>";
                            exit();
                        }
                    }
                }
            }
        }
        elseif ($action === 'deleteDiscount') {
            
            if(empty($_POST['id'])){
                echo "<script>alert('An id of a discount must be defined.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                $stmt = $conn->prepare("DELETE FROM discounts WHERE id = ?");
                $stmt->bind_param('i',$id);
                if($stmt->execute()){
                    echo "<script>alert('Discount with id = " . $id . " has been deleted successfully.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                else{
                    echo "<script>alert('There was a problem deleting the discount with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                    exit();
                }
            }
        } 
        elseif ($action === 'addDiscount') {

            if(empty($_POST['title']) || empty($_POST['description']) || empty($_FILES['image']) || empty($_POST['discount_percentage']) || empty($_POST['start_date']) || empty($_POST['end_date']) || empty($_POST['serviceId'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            
            else{

                $startDate = new DateTime($_POST['start_date']);
                $endDate = new DateTime($_POST['end_date']);
                $today = new DateTime('today'); // Get today's date at midnight
                
                if($startDate < $today || $endDate < $today || $startDate > $endDate){
                    echo "<script>alert('The start and end date must be after the today date and start date must be before the end date.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                 // File size limit (2MB)
                $maxFileSize = 2 * 1024 * 1024; // 2MB
                if ($_FILES['image']['size'] > $maxFileSize) {
                    echo "<script>alert('The uploaded file is too large. Maximum allowed size is 2MB.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                
                if(!((float)$_POST['discount_percentage'] > 0 && (float)$_POST['discount_percentage'] <= 100)){
                    echo "<script>alert('The discount percentage must be between 0 and 100.'); window.location.href = '../admin.php';</script>";
                    exit();
                }

                else{
                    //Check if there already the discount with this title
                    $stmt = $conn->prepare("SELECT * FROM discounts WHERE title = ?");
                    $stmt->bind_param("s", $_POST['title']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    //If there is there is no discount on that service
                    if($result->num_rows == 0){ 
                        $image = $_FILES['image']['tmp_name']; // Read the file data
                        $imgData = file_get_contents($image);
                        $stmt = $conn->prepare("INSERT INTO discounts (title, description, image_data, discount_percentage, start_date, end_date, service_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssdssi", $_POST['title'], $_POST['description'], $imgData, $_POST['discount_percentage'], $_POST['start_date'], $_POST['end_date'], $_POST['serviceId']);
                        if($stmt->execute()){
                            echo "<script>alert('Discount has been inserted successfully.'); window.location.href = '../admin.php';</script>";
                            exit(); 
                        }
                        else{
                            echo "<script>alert('There was a problem inserting the discount.'); window.location.href = '../admin.php';</script>";
                            exit();
                        }
                    }
                    else{
                        echo "<script>alert('There is already discount on that service.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                }
            }
        }

        //New
        elseif ($action === 'editNew') {
            if(empty($_POST['title']) || empty($_POST['description']) || empty($_FILES['image'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                //Check if there is already the title of the new
                $stmt = $conn->prepare("SELECT * FROM news WHERE title = ?");
                $stmt->bind_param("s", $_POST['title']);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows > 1){ 
                    echo "<script>alert('There is already a new with this title.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                else{
                    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                        $stmt = $conn->prepare("UPDATE news SET title = ? , description = ? WHERE id = ?");
                        $stmt->bind_param("ssi", $_POST['title'], $_POST['description'], $id);    
                    }
                    else{
                        $image = $_FILES['image']['tmp_name']; // Read the file data
                        $imgData = file_get_contents($image); 
                        $stmt = $conn->prepare("UPDATE news SET title = ? , description = ?, image_data = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $_POST['title'], $_POST['description'], $imgData, $id);
                    }
                    
                    
                    if($stmt->execute()){
                        echo "<script>alert('New with id = " . $id . " has been updated successfully.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                    else{
                        echo "<script>alert('There was a problem updating the new with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                }
            }
        }
        elseif ($action === 'deleteNew') {
            $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
            $stmt->bind_param('i',$_POST['id']);
            if($stmt->execute()){
                echo "<script>alert('New with id = " . $id . " has been deleted successfully.'); window.location.href = '../admin.php';</script>";
                exit();
            }
            else{
                echo "<script>alert('There was a problem deleting the new with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                exit();
            }
        } 
        elseif ($action === 'addNew') {
            if(empty($_POST['title']) || empty($_POST['description']) || empty($_FILES['image'])){
                echo "<script>alert('All fields must have a value.'); window.location.href = '../admin.php';</script>";
                exit();
            }
           else{
                // File size limit (2MB)
                $maxFileSize = 2 * 1024 * 1024; // 2MB
                if ($_FILES['image']['size'] > $maxFileSize) {
                    echo "<script>alert('The uploaded file is too large. Maximum allowed size is 2MB.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                //Check if there is already the title of the new
                $stmt = $conn->prepare("SELECT * FROM news WHERE title = ?");
                $stmt->bind_param("s", $_POST['title']);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows > 0){ 
                    echo "<script>alert('There is already a new with this title.'); window.location.href = '../admin.php';</script>";
                    exit();
                }
                else{
                    $image = $_FILES['image']['tmp_name'];
                    $imageData = file_get_contents($image);
                    $stmt = $conn->prepare("INSERT INTO news (title, description, image_data) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $_POST['title'], $_POST['description'], $imageData);
                    if($stmt->execute()){
                        echo "<script>alert('New with id = " . $id . " has been inserted successfully.'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                    else{
                        echo "<script>alert('There was a problem insering the new with id = " . $id . ".'); window.location.href = '../admin.php';</script>";
                        exit();
                    }
                }
           }
        }

        //Unknow Action
        else{
            echo "Unkonow Action";
            exit();
        }
    }
    else{
        header('Location: ../access_denied.html');
        exit();
    }

    function updateCapacity($userId, $reservedAt, $serviceId, $conn) {
        try {
            // Get the service details including current appointments
            $stmt = $conn->prepare("SELECT appointments FROM services WHERE id = ?");
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
    
                // Decode appointments JSON
                $appointments = json_decode($row['appointments'], true);
    
                // If the reserved date exists, decrement its capacity
                if (isset($appointments[$reservedAt]) && $appointments[$reservedAt] > 0) {
                    $appointments[$reservedAt] -= 1;
    
                    // If capacity reaches 0, optionally remove the entry
                    if ($appointments[$reservedAt] === 0) {
                        unset($appointments[$reservedAt]);
                    }
    
                    // Convert the updated appointments array back to JSON
                    $updatedAppointmentsJson = json_encode($appointments);
    
                    // Update the service with the new appointments JSON
                    $updateStmt = $conn->prepare("UPDATE services SET appointments = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $updatedAppointmentsJson, $serviceId);
                    $updateStmt->execute();
    
                    // Remove the reservation from the reservations table
                    $deleteStmt = $conn->prepare("DELETE FROM reservations WHERE user_id = ? AND service_id = ? AND reserved_at = ?");
                    $deleteStmt->bind_param("iis", $userId, $serviceId, $reservedAt);
                    $deleteStmt->execute();
    
                    return true; // Update successful
                } else {
                    return false; // No capacity to update or date not found
                }
            } else {
                return false; // Service not found
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
            return false;
        }
    }

?>