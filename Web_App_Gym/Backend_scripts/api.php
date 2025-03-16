<?php

    include 'server_status.php';

    function create_database(){

        $host = $GLOBALS['host'];
        $dbname = $GLOBALS['dbname'];
        $username_db = $GLOBALS['username'];
        $password_db = $GLOBALS['password'];

        try {
            // Connect to MySQL without specifying a database
            $conn = new mysqli($host, $username_db, $password_db);

            // Check if the database exists
            $result = $conn->query("SHOW DATABASES LIKE '$dbname'");
            $databaseExists = $result->fetch_assoc();

            
            if(!$databaseExists) {
                // Create a new database
                $conn->query("CREATE DATABASE $dbname");

                // Select the new database
                $conn->query("USE $dbname");

                // Create tables
                $createUsersTable = "
                    CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        surname VARCHAR(255) NOT NULL,  
                        username VARCHAR(50) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        email VARCHAR(100) NOT NULL UNIQUE,
                        phone VARCHAR(10) NOT NULL,
                        country VARCHAR(50) NOT NULL,
                        city VARCHAR(50) NOT NULL,
                        address VARCHAR(255) NOT NULL,
                        role ENUM('admin', 'member') NOT NULL,
                        on_request ENUM('True', 'False') NOT NULL DEFAULT 'True',
                        cancellations_at_that_week INT NOT NULL DEFAULT 0,
                        cancellation_week INT NOT NULL DEFAULT 0,
                        cancellation_year INT NOT NULL DEFAULT 0
                    )";
                    
                    $conn->query($createUsersTable);

                $createTrainersTable = "
                    CREATE TABLE IF NOT EXISTS trainers (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        surname VARCHAR(100) NOT NULL,
                        service_id INT NOT NULL
                    )";
                
                    $conn->query($createTrainersTable);

                $createServicesTable = "
                    CREATE TABLE IF NOT EXISTS services (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        description TEXT,
                        type VARCHAR(50) NOT NULL,
                        price DECIMAL(10,2) NOT NULL,
                        max_capacity INT NOT NULL,
                        day VARCHAR(50) NOT NULL,
                        time TIME NOT NULL,
                        appointments JSON DEFAULT NULL
                    )";
                    
                    $conn->query($createServicesTable);

                $createBookingsTable = "
                    CREATE TABLE IF NOT EXISTS reservations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        service_id INT NOT NULL,
                        service_name VARCHAR(100) NOT NULL,
                        reserved_at VARCHAR(50) NOT NULL,
                        price DECIMAL(10,2) NOT NULL
                    )";
                
                    $conn->query($createBookingsTable);

                
                    // Create the `discounts` table
                $createDiscountsTable = "
                    CREATE TABLE IF NOT EXISTS discounts (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        description TEXT NOT NULL,
                        image_data LONGBLOB NOT NULL,
                        discount_percentage DECIMAL(5, 2) NOT NULL,
                        start_date DATE NOT NULL,
                        end_date DATE NOT NULL,
                        service_id INT NOT NULL
                    )";
                
                
                $conn->query($createDiscountsTable);


                // Create the `discounts` table
                $createNewsTable = "
                CREATE TABLE IF NOT EXISTS news (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    image_data LONGBLOB NOT NULL
                )";
            
            
                $conn->query($createNewsTable);

                insert_data($conn);
            }    
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }

    }

    // Function to read an image as binary data
    function getImageData($imagePath) {
        return file_get_contents($imagePath);
    }

    //Function to insert data to the tables
    function insert_data($conn){

        $dbname = $GLOBALS['dbname'];
        $conn->select_db($dbname);

        //Insert the recordings
        $insertUsers = "
            INSERT INTO users (name, surname, username, password, email, phone, country, city, address, role, on_request) 
            VALUES 
                ('Admin', 'User', 'admin', '".password_hash("admin", PASSWORD_BCRYPT)."', 'admin@example.com', '1234567890', 'Greece', 'Athens', '123 Admin Street', 'admin', 'False')
            ";
        $conn->query($insertUsers);


        // Insert 10 trainers into the 'trainers' table
        $insertTrainers = "
            INSERT INTO trainers (name, surname, service_id)
            VALUES 
                ('John', 'Doe', 1),
                ('Jane', 'Smith', 2),
                ('Mark', 'Taylor', 3),
                ('Alice', 'Johnson', 4),
                ('Bob', 'White', 5),
                ('Sara', 'Davis', 6),
                ('Tom', 'Wilson', 7),
                ('Emily', 'Martin', 8),
                ('David', 'Clark', 9),
                ('Lucy', 'Garcia', 10)
            ";
        $conn->query($insertTrainers);


        // Insert 10 services into the 'services' table
        $insertServices = "
            INSERT INTO services (name, description, type, price, max_capacity, day, time)
            VALUES
                ('Morning Yoga', 'A peaceful morning yoga session', 'Yoga', 20.00, 20, 'Monday', '08:00:00'),
                ('Evening Pilates', 'A relaxing evening Pilates class', 'Pilates', 25.00, 15, 'Tuesday', '18:00:00'),
                ('Zumba Fitness', 'An energetic Zumba class', 'Fitness', 15.00, 25, 'Wednesday', '19:00:00'),
                ('Boxing Training', 'Intensive boxing workout', 'Boxing', 30.00, 10, 'Thursday', '09:00:00'),
                ('Kickboxing Class', 'A high-energy kickboxing workout', 'Kickboxing', 35.00, 12, 'Friday', '10:00:00'),
                ('Strength Training', 'Build strength with weights and machines', 'Strength Training', 40.00, 15, 'Monday', '11:00:00'),
                ('CrossFit', 'High-intensity CrossFit session', 'CrossFit', 50.00, 20, 'Tuesday', '12:00:00'),
                ('Dance Workout', 'Fun dance fitness class', 'Dance', 22.00, 30, 'Wednesday', '14:00:00'),
                ('Running Club', 'Group running for all levels', 'Running', 18.00, 40, 'Thursday', '06:00:00'),
                ('HIIT Class', 'High-Intensity Interval Training for full-body fitness', 'HIIT', 28.00, 25, 'Friday', '07:00:00')
            ";
        $conn->query($insertServices);


        //Insert 3 discounts to the discounts table
        $imageData = getImageData('Images/discounts.png');  // Read image as binary data
        $sql = "INSERT INTO discounts (title, description, image_data, discount_percentage, start_date, end_date, service_id) 
                VALUES ('New Year Offer', 'Celebrate the new year with a 20% discount!', ? ,20.00, '2024-12-23', '2025-02-18', 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $imageData);
        $stmt->execute();
        
        $sql = "INSERT INTO discounts (title, description, image_data, discount_percentage, start_date, end_date, service_id) 
        VALUES ('Black Friday Deal', 'Get 50% off during our Black Friday sale!', ?, 50.00, '2024-11-29', '2024-11-29', 3)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $imageData);
        $stmt->execute();

        $sql = "INSERT INTO discounts (title, description, image_data, discount_percentage, start_date, end_date, service_id) 
        VALUES ('Cyber Monday Offer', 'Exclusive online deals with 30% off!', ?, 30.00, '2025-01-15', '2024-03-02', 4)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $imageData);
        $stmt->execute();



        //Insert 4 news to the news table
        $imageData = getImageData('Images/news.png');  // Read image as binary data
        
        $sql = "INSERT INTO news (title, description, image_data) 
        VALUES ('Grand Opening', 'Join us for the grand opening of our state-of-the-art gym!', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $imageData);
        $stmt->execute();

        $sql = "INSERT INTO news (title, description, image_data) 
        VALUES ('New Yoga Classes', 'Discover tranquility with our new yoga classes starting next week!', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $imageData);
        $stmt->execute();

        $sql = "INSERT INTO news (title, description, image_data) 
        VALUES ('Personal Training', 'Achieve your fitness goals with our expert personal trainers.', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $imageData);
        $stmt->execute();

        $sql = "INSERT INTO news (title, description, image_data) 
        VALUES ('New Equipment', 'We’ve added brand-new equipment to elevate your workouts!', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $imageData);
        $stmt->execute();

        //Close the connection to the database
        $conn->close();
        $stmt->close();
    }

?>
