<?php
    //Include the script that contains the function create_database()
    include 'Backend_scripts/server_status.php';
    include 'Backend_scripts/api.php';
    
    //Server status
    $host = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    $isLoggedIn = false;

    //Function from the api.php that checks the existance of the database and if not exists, creates it
    create_database();

    //Database connection that selects the database "gym_management"
    $conn = new mysqli($host, $username_db, $password_db, $dbname);
    //Get all the available services and save them at the variable result
    $sql = "SELECT * FROM services";
    $result = $conn->query($sql);
    //Close the connection
    $conn->close(); 
    
    //Check if the cookie with value user_id is setted
    if (isset($_COOKIE['username'])) {
        $isLoggedIn = true;
    }

?>


<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome to Ignite Gym</title>
        <style>
            .container {
                flex: 1;
            }
            .content {
                text-align: center;
                padding: 50px 20px;
            }
            .content h2 {
                font-size: 2em;
                margin-bottom: 20px;
                color: white;
                font-size: 50px;
            }
            .content p {
                font-size: 1.2em;
                margin-bottom: 40px;
                color: white;
                text-align: center; 
                background: linear-gradient(to right, #f39c12, #e74c3c);
                font-family: 'Arial', sans-serif;
                line-height: 1.6;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
                padding: 10px 15px;
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
                max-width: 700px;
                margin: 100px auto;
            }
            .features {
                display: flex;
                margin-top: 20px;
                padding-bottom: 20px;
                overflow: hidden;
                justify-content: center;
            }
            .feature {
                display: flex;
                background-color: #C0C0C0;
                border: 1px solid white;
                border-radius: 8px;
                margin: 10px;
                padding: 20px;
                text-align: center;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                width: 400px;
            }
            .feature-image {
                width: 200px;
                height: 200px;
                object-fit: cover;
            }
            .sub-container {
                margin-right: 10px;
            }

            /* Responsive Design */
            @media (max-width: 950px) {
                .features{
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }
                .feature{
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }
            }


        </style>
    </head>

    <?php  
        //Include the header.php to show the header
        include 'Backend_scripts/header.php';
    ?>

    <body>

        <div class="container">
            <div class="content">
                <br>
                <h2>Your Fitness Journey Starts Here</h2>
                <img src="Images/logo.png" alt="Gym Management Logo">
                <p>At Ignite Gym, we offer state-of-the-art equipment, expert trainers, and a welcoming community to help you achieve your health goals. Let us support your journey to a healthier, stronger you.</p>

                <?php  
                //Show special news and discounts if the user is logged in
                if($isLoggedIn){
                    include 'Backend_scripts/discounts.php';
                }
                ?>
            </div>
        </div>
        <div class="features">
                <div class="feature">
                    <div class="sub-container">
                        <h3>Modern Equipment</h3>
                        <br><br>
                        <p>Access the latest fitness machines and tools designed for effective workouts.</p>
                    </div>
                    <img class="feature-image" src="Images/features 1.png">
                </div>
                <div class="feature">
                    <div class="sub-container">
                        <h3>Personal Training</h3>
                        <br><br>
                        <p>Work with our experienced trainers for personalized fitness plans.</p>
                    </div>
                    <img class="feature-image" src="Images/features 2.png">
                </div>
                <div class="feature">
                    <div class="sub-container">
                        <h3>Exclusive Classes</h3>
                        <br><br>
                        <p>Join yoga, pilates, spinning, and other group classes for all fitness levels.</p>
                    </div>
                    <img class="feature-image" src="Images/features 3.png">
                </div>
            </div>
    </body>

    <?php  
        //Include the footer.php to show the footer
        include 'Backend_scripts/footer.php';
    ?>
    
</html>
