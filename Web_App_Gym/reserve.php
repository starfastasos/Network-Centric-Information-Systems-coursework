<?php
    include 'Backend_scripts/server_status.php';
        
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    //Initialize the variables
    $isLoggedIn = false;
    $username = null;
    $discountExists = FALSE;
    $totalDiscount = 0;
    $finalPrice = 0;


    // Establish database connection
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    //Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //Check if the user is logged in by looking for the cookie
    if (isset($_COOKIE['username'])) {
        $isLoggedIn = true;
        $username = $_COOKIE['username'];
        $userId = $_COOKIE['userID'];

        //Get the id of the service from the GET request that user sented
        $serviceId = $_GET['id'];

        //Query that gets the service details
        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $servicesResult = $stmt->get_result();
        $service = $servicesResult->fetch_assoc();

        // Check if the service exists
        if ($servicesResult->num_rows > 0) {
            //Store service details insto variables
            $serviceName = $service['name'];
            $serviceDescription = $service['description'];
            $servicePrice = $service['price'];
            $serviceDay = $service['day'];
            $serviceTime = $service['time'];

            $stmt = $conn->prepare("SELECT id FROM trainers WHERE service_id = ?");
            $stmt->bind_param("i", $serviceId);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){
                $trainerID = $row['id'];  
            }

            //Query to get the trainer details
            $stmtTrainer = $conn->prepare("SELECT * FROM trainers WHERE id = ?");
            $stmtTrainer->bind_param("i", $trainerID);
            $stmtTrainer->execute();
            $trainerResult = $stmtTrainer->get_result();
            $rowTrainer = $trainerResult->fetch_assoc();
            
            //If there is a trainer, show the details
            if ($trainerResult->num_rows > 0) {

                //Check if there is any discount for that service
                $sql = "SELECT * FROM discounts WHERE service_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $serviceId);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows>0){
                    $totalDiscount = 0;
                    $finalPrice = 0;
                    $discountExists = FALSE;
                    while($row = $result->fetch_assoc()){
                        $startDate = new DateTime($row['start_date']);
                        $endDate = new DateTime($row['end_date']);
                        $currentDate = new DateTime('today');
            
                        if($startDate <= $currentDate && $endDate >= $currentDate){
                            $discountExists = TRUE;
                            $totalDiscount += $row['discount_percentage'];
                        }
                    }
                    if($discountExists){
                        $finalPrice = $servicePrice - (($totalDiscount / 100) * $servicePrice);
                    }
                    else{
                        $finalPrice = $servicePrice;
                    }
                }

                //If the user press the reserve button, he wil send a POST message to the same file
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve']) && isset($_POST['reservation_date'])) {
                    
                    $reservationDate = $_POST['reservation_date'];
                    $reservationPrice = $finalPrice;
                    $reservationTime = $serviceTime;
                    $reservedAt = $reservationDate . ' ' . $reservationTime;
                    
                    //Check if the date is in the past
                    $todayDay = date($serviceDay); 
                    if (strtotime($reservedAt) < time()) {
                    echo "<script>alert('The selected service time has already passed. Please choose another service.');</script>";
                    } 
                    //Check if the day of the week the user selected, matches with service day
                    elseif (date('l', strtotime($reservationDate)) !== $serviceDay) {
                        echo "<script>alert('The selected date does not match the service day. Please choose a valid date.');</script>";
                    }                
                    else {
                        try {
                                //Create the connection to the database
                                $conn = new mysqli($servername, $username_db, $password_db, $dbname);
                                
                                $updateResult = checkCapacity($reservationPrice, $reservationDate,$reservationTime,$conn,$serviceId,$serviceName,$userId);
                                //Check the capacity
                                if($updateResult == 1){
                                    echo "<script>alert('The reservation was successful.');window.location.href = 'index.php';</script>";
                                    exit();
                                }
                                else{
                                    echo "<script>alert('The reservation date is unavailable . Please choose another date.');</script>";
                                }
                            }
                            catch (Exception $e) {
                            echo "<script>alert('" . $e->getMessage() . "');</script>";
                        }
                    }
                }  
            }
            else{
                echo "<script>alert('Trainer for that service is not available. Cannot reserve this service for now.');window.location.href = 'services.php';</script>";
                exit();
            }
        }
        else{
            echo "<script>alert('Service not found.');window.location.href = 'services.php';</script>";
            exit();
        }
        // Close the statements and connection
        $stmt->close();
        $stmtTrainer->close();
        $conn->close();
    }
    else{
        header('Location: access_denied.html');
        exit();
    }

    function checkCapacity($requestedPrice, $requestedDate, $requestedTime, $conn, $serviceId, $servicename, $userId) {
        // Query to get the service data by ID
        $stmt = $conn->prepare("SELECT max_capacity, appointments FROM services WHERE id = ?");
        $stmt->bind_param("i", $serviceId); // Binding service ID
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
    
            // Fetch maximum capacity and appointments JSON
            $maxCapacity = $row["max_capacity"];
            $appointments = json_decode($row["appointments"], true);
    
            // Initialize appointments if it's NULL
            if (!$appointments) {
                $appointments = [];
            }
    
            // Get current capacity for the requested date, default to 0 if not set
            $currentCapacity = isset($appointments[$requestedDate]) ? $appointments[$requestedDate] : 0;
    
            // Check if capacity allows for the request
            if ($currentCapacity < $maxCapacity) {

                $stmt = $conn->prepare("SELECT appointments FROM services WHERE id = ?");
                $stmt->bind_param("i", $serviceId); // Binding service ID
                $stmt->execute();
                $result = $stmt->get_result();
                
                $appointments = json_decode($row['appointments'], true);

                // Update the capacity in the appointments array
                $appointments[$requestedDate] = $currentCapacity + 1;
    
                // Convert the updated appointments array back to JSON
                $updatedAppointmentsJson = json_encode($appointments);
    
                // Update the database
                $updateStmt = $conn->prepare("UPDATE services SET appointments = ? WHERE id = ?");
                $updateStmt->bind_param("si", $updatedAppointmentsJson, $serviceId);
                $updateStmt->execute();

                //Also insert the reservation to the reservations table to be more simple the retrive of the reservation for each user
                $insertStmt = $conn->prepare("INSERT INTO reservations (user_id, service_id, service_name, reserved_at, price) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->bind_param("iissd", $userId, $serviceId, $servicename, $requestedDate, $requestedPrice);
                $insertStmt->execute();
    
                return true; // Booking was successful
            } else {
                return false; // Capacity exceeded
            }
        } else {
            return false; // Service ID not found
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Service Details</title>
        <style>
                .service-details {
                    flex:1;
                    margin: 50px auto;
                    margin-top: 300px;
                    margin-bottom:500px;
                    background: white;
                    border-radius: 10px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    background-color: #fff;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    max-width: 800px;
                }

                .service-details h2 {
                    color: #333;
                }
                .service-details h3 {
                    font-size: 1.8em;
                    color: #333;
                }
                .service-details p {
                    font-size: 1.5em;
                    color: #666;
                }

                .service-details .price {
                    font-size: 1.5em;
                    font-weight: bold;
                    color: #4CAF50;
                }

                .service-details .info {
                    font-size: 1em;
                    color: #777;
                    margin-bottom: 10px;
                }

                .service-details label {
                    font-size: 1.5em;
                    color: #777;
                    margin-bottom: 10px;
                }

                .service-details input {
                    font-size: 1.5em;
                    color: #777;
                    margin-bottom: 10px;
                }

                .service-details .trainer {
                    font-size: 1.1em;
                    margin-top: 20px;
                }

                .service-details .trainer-info {
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    padding: 15px;
                    border-radius: 8px;
                }

                .service-details button {
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 1em;
                    margin-top: 20px;
                    transition: background-color 0.3s;
                }

                .service-details button:hover {
                    background-color: #45a049;
                }

                #calendar {
                    margin-top: 20px;
                }
                .discount_container {
                    display: flex;
                    justify-content: center;
                    align-items: center;    
                }
        </style>
    </head>

    <?php  
        //Include the header.php to show the header
        include 'Backend_scripts/header.php';
        if(!empty($message)){
            echo "<script>alert('" . $message . "');</script>";
        }
    ?>

    <body>
        <?php
            echo "<div class='container'>
                <div class='service-details'>
                    <h2>". $serviceName ."</h2>
                    <p>". $serviceDescription ."</p>";
                        if(!$discountExists){
                            echo"<div style='font-size: 2.5em;font-weight: bold; color: #4CAF50'>" . $servicePrice . " $ </div>";     
                        }
                        else{
                            echo"<div style='font-size: 2.5em;font-weight: bold; color: orange'>" . $servicePrice . "
                            <span style='color:black;'> - </span>    <span style='color:red;'>" . $totalDiscount . "%</span> <span style='color:black;'> = </span> <span style='color:#4CAF50;'>".$finalPrice." $</span>
                            </div>";
                        }
                    echo"
                    <br>
                    <p><strong>Date:</strong>". $serviceDay ."</p>
                    <br>
                    <p><strong>Time:</strong>". $serviceTime ."</p>

                    <div class='trainer'>
                        <h3>Trainer Information</h3>
                        <div class='trainer-info'>
                            <p><strong>Name:</strong>". $rowTrainer['name'] ." ".$rowTrainer['surname'] ."</p>
                            <p><strong>Specialization:</strong>". $serviceName  ."</p>
                        </div>
                    </div>";
                    if (!$isLoggedIn){
                        echo "<p>You must be logged in to make a reservation.</p>";
                    }
                    else{
                        echo "<form method='POST'>
                            <label for='reservation_date'>Select Reservation Date (Only ".$serviceDay."):</label>
                            <input type='date' id='reservation_date' name='reservation_date' >
                            <input type='hidden' name='price' value='".$finalPrice."'>
                            <button type='submit' name='reserve'>Reserve</button>
                        </form>";
                    }
                echo"</div>
                </div>";
        ?>
    </body>

    <script>
        // Get today's date in the format 'YYYY-MM-DD'
        const today = new Date().toISOString().split('T')[0];

        // Select the input element with the id 'reservation_date' and set the min attribute
        const datePicker = document.getElementById('reservation_date');
        if (datePicker) {
            datePicker.setAttribute('min', today);
        }
    </script>


    <?php
            //Include footer.php in order to show the footer 
            include 'Backend_scripts/footer.php'; 
        ?>

</html>
