<?php

    include 'Backend_scripts/server_status.php';
            
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    $discountExists = FALSE;

    //Create database connection
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    //Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get service ID from the URL
    if (isset($_GET['id'])) {
        $serviceId = $_GET['id'];

        // Prepare and execute the SQL query to get the service details
        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->bind_param("i", $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the service exists
        if ($result->num_rows > 0) {
            $service = $result->fetch_assoc();
            // Store service details into variables
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

            // Now, get the trainer details from the trainers table based on trainerID
            $sqlTrainer = "SELECT * FROM trainers WHERE id = ?";
            $stmtTrainer = $conn->prepare($sqlTrainer);
            $stmtTrainer->bind_param("i", $trainerID);
            $stmtTrainer->execute();
            $resultTrainer = $stmtTrainer->get_result();

            // Check if the trainer exists
            if ($resultTrainer->num_rows > 0) {
                $trainer = $resultTrainer->fetch_assoc();

                // Store trainer details into variables
                $trainerName = $trainer['name'];
                $trainerSurname = $trainer['surname'];
                $trainerService = $serviceName;
            } else {
                echo "<script>alert('Sorry but the trainer not found. Cannot reserve this service for now.');window.location.href = 'services.php';</script>";
                exit();
            }


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

        } 
        else {
            echo "<script>alert('Service not found.');window.location.href = 'services.php';</script>";
            exit();
        }

        
        
    } 
    else {
        header('Location: access_denied.html');
        exit();
    }

    //Close the connection
    $stmt->close();
    $stmtTrainer->close();
    $conn->close();
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
                    width: 800px;
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
            echo "<div class='service-details'>
                        <h2>". $serviceName ."</h2>
                        <p>". $serviceDescription ."</p>
                        <br>";
                        if(!$discountExists){
                            echo"<div style='font-size: 2.5em;font-weight: bold; color: #4CAF50'>" . $servicePrice . " $ </div>";     
                        }
                        else{
                            echo"<div style='font-size: 2.5em;font-weight: bold; color: orange'>" . $servicePrice . "
                            <span style='color:black;'> - </span>    <span style='color:red;'>" . $totalDiscount . "%</span> <span style='color:black;'> = </span> <span style='color:#4CAF50;'>".$finalPrice." $</span>
                            </div>";
                        }
                        echo"<br>
                            <div class='trainer'>
                                <h3>Trainer Information</h3>
                                <div class='trainer-info'>
                                    <p><strong>Name:</strong>". $trainerName ." ". $trainerSurname ."</p>
                                    <p><strong>Specialization:</strong>". $serviceName  ."</p>
                                </div>
                            </div>
                        </div>";
        ?>
    </body>


    <?php
            //Include the footer.php to show the footer
            include 'Backend_scripts/footer.php';
        ?>

</html>
