<?php
    include 'Backend_scripts/server_status.php';
        
    //Server status
    $servername = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    //Check if the cookie with value user_id is setted
    if (isset($_COOKIE['username'])) {
        $userId = $_COOKIE['userID'];
    

        //Database connection and query to fetch services
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);
        //Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        //Query to get the reservation history of the user
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $reservationsResult = $stmt->get_result();


        //If the user presses the cancel button on a reservation, handle the request here
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Store the values from the POST message
            $reservationId = intval($_POST['reservation_id']);
            $userId = intval($_POST['user_id']);

            //Get the current week number (1–52) and year to track cancellations per week
            $currentWeek = date("W");
            $currentYear = date("Y");

            //Get the user's current cancellation count and the last recorded week/year
            $stmt = $conn->prepare("SELECT cancellations_at_that_week, cancellation_week, cancellation_year FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userResult = $stmt->get_result();

            if ($userResult->num_rows == 1) {
                $userRow = $userResult->fetch_assoc();

                //Reset the cancellation count if the current week or year differs from the last recorded
                if ($userRow['cancellation_week'] != $currentWeek || $userRow['cancellation_year'] != $currentYear) {
                    //Update the user's cancellation count to 0 and set the current week and year
                    $stmt = $conn->prepare("UPDATE users SET cancellations_at_that_week = 0, cancellation_week = ?, cancellation_year = ? WHERE id = ?");
                    $stmt->bind_param("iii", $currentWeek, $currentYear, $userId);
                    $stmt->execute();
                }

                //Get the updated cancellation count after resetting if needed
                $stmt = $conn->prepare("SELECT cancellations_at_that_week FROM users WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $userResult = $stmt->get_result();
                $userRow = $userResult->fetch_assoc();

                //Check if the user has not exceeded the weekly cancellation limit
                if ($userRow['cancellations_at_that_week'] < 2) {
                    //Get the reservation details to get the service ID and reservation date
                    $stmt = $conn->prepare("SELECT service_id, reserved_at FROM reservations WHERE id = ?");
                    $stmt->bind_param("i", $reservationId);
                    $stmt->execute();
                    $reservationResult = $stmt->get_result();

                    if ($reservationResult->num_rows == 1) {
                        $reservationRow = $reservationResult->fetch_assoc();
                        $serviceId = $reservationRow['service_id'];
                        $requestedDate = date("Y-m-d", strtotime($reservationRow['reserved_at'])); // Format the reservation date

                        //Get the service's appointments JSON to update capacity for the reservation date
                        $stmt = $conn->prepare("SELECT appointments FROM services WHERE id = ?");
                        $stmt->bind_param("i", $serviceId);
                        $stmt->execute();
                        $serviceResult = $stmt->get_result();

                        if ($serviceResult->num_rows == 1) {
                            $serviceRow = $serviceResult->fetch_assoc();
                            $appointments = json_decode($serviceRow['appointments'], true); // Decode the JSON to a PHP array

                            //Reduce the capacity for the requested date if it exists in the appointments
                            if (isset($appointments[$requestedDate])) {
                                $currentCapacity = $appointments[$requestedDate];
                                $appointments[$requestedDate] = max(0, $currentCapacity - 1); // Ensure capacity is not negative
                            }

                            //Convert the updated appointments array back to JSON and update the database
                            $updatedAppointmentsJson = json_encode($appointments);
                            $updateStmt = $conn->prepare("UPDATE services SET appointments = ? WHERE id = ?");
                            $updateStmt->bind_param("si", $updatedAppointmentsJson, $serviceId);
                            $updateStmt->execute();
                        }

                        //Remove the reservation from the database
                        $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
                        $stmt->bind_param("i", $reservationId);
                        $stmt->execute();

                        //Increment the user's cancellation count for the current week
                        $stmt = $conn->prepare("UPDATE users SET cancellations_at_that_week = cancellations_at_that_week + 1 WHERE id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();

                        //Notify the user of the successful cancellation and redirect to the history page
                        echo "<script>alert('Reservation successfully cancelled'); window.location.href='history.php';</script>";
                    } else {
                        //Notify the user if the reservation was not found
                        echo "<script>alert('Reservation not found.');</script>";
                    }
                } else {
                    //Notify the user if they have reached their weekly cancellation limit
                    echo "<script>alert('You have reached the maximum cancellation limit for this week.');</script>";
                }
            }
        }
        
        // Close the database connection
        $conn->close();
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
        <title>User Reservations</title>
        <style>
            .container {
                flex: 1;
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                width: 100%;
            }
            h1 {
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table th, table td {
                padding: 10px;
                border: 1px solid #ccc;
                text-align: center; /* Center-align all table contents */
            }
            table th {
                background-color: #f4f4f4;
            }
            form {
                margin: 0;
            }
            .cancel-btn {
                padding: 5px 10px;
                background-color: #f44336;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .cancel-btn:hover {
                background-color: #d32f2f;
            }
            .no-data {
                text-align: center;
                color: #666;
            }
    </style>
    </head>
    <?php
        //Include the script to show the header
        include 'Backend_scripts/header.php';
    ?>
    <body>
        <div class="container">
            <h1>Your Reservations</h1>

            <?php if ($reservationsResult->num_rows > 0){
                echo "<table>
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Reserved At</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>";
                        while($row = $reservationsResult->fetch_assoc()){
                            echo"<tr>
                                <td>". $row['service_name'] ."</td>
                                <td>". $row['reserved_at'] ."</td>
                                <td>". $row['price'] ." $ </td>
                                <td>";
                                    $reservationDateTime = new DateTime($row['reserved_at']);
                                    $currentDateTime = new DateTime();

                                    if ($reservationDateTime > $currentDateTime){
                                        echo "<form method='POST'>
                                            <input type='hidden' name='reservation_id' value=". $row['id']. ">
                                            <input type='hidden' name='user_id' value=". $userId .">
                                            <button type='submit' name='cancel_reservation' class='cancel-btn'>Cancel</button>
                                        </form>";
                                    }
                                echo"</td>
                            </tr>";
                        }
                    echo "</tbody>
                </table>";
            }
            else{
                echo "<p class='no-data'>No reservations found for your account.</p>";
            }?>
        </div>
    </body>
    <?php
        //Include the script to show the footer
        include 'Backend_scripts/footer.php';
    ?>
</html>
