<?php

    include 'server_status.php';
    
    //Server status
    $host = $GLOBALS['host'];
    $dbname = $GLOBALS['dbname'];
    $username_db = $GLOBALS['username'];
    $password_db = $GLOBALS['password'];

    //Connection to the database
    $conn = new mysqli($host, $username_db, $password_db, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //Get the data from the discounts table
    $stmt = $conn->prepare("SELECT * FROM discounts");
    $stmt->execute();
    $resultDiscounts = $stmt->get_result();

    //Get the data from the news table
    $stmt = $conn->prepare("SELECT * FROM news");
    $stmt->execute();
    $resultNews = $stmt->get_result();

    $slides = '';
    if ($resultDiscounts->num_rows > 0) {
        while ($row = $resultDiscounts->fetch_assoc()) {
            $startDate = new DateTime($row['start_date']);
            $endDate = new DateTime($row['end_date']);
            $currentDate = new DateTime('today');
            
            if($startDate <= $currentDate && $endDate >= $currentDate){
                $imageData = !empty($row['image_data']) ? 'data:image/jpeg;base64,' . base64_encode($row['image_data']) : '';
                $slides .= "
                    <div class='unique-slide' style='background-image: url(\"$imageData\");'>
                        <h3>" . $row['title'] . "</h3>
                        <p>" . $row['description'] . "</p>
                    </div>
                ";
            }
            
        }
    }
    if ($resultNews->num_rows > 0) {
        while ($row = $resultNews->fetch_assoc()) {
            $imageData = !empty($row['image_data']) ? 'data:image/jpeg;base64,' . base64_encode($row['image_data']) : '';
            $slides .= "
                <div class='unique-slide' style='background-image: url(\"$imageData\");'>
                    <h3>" . $row['title'] . "</h3>
                    <p>" . $row['description'] . "</p>
                </div>
            ";
        }
    } else {
        $slides = "<div class='unique-slide'>No discounts or news available</div>";
    }

    //Close the connections
    $conn->close();
?>

<style>
    .unique-slideshow-container {
        font-family: Arial, sans-serif;
        margin: 0 auto; 
        padding: 0;s
        background: #f8f8f8;
        max-width: 1200px; 
    }
    .unique-slideshow-container .slideshow {
        width: 100%;
        overflow: hidden;
        position: relative;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    .unique-slideshow-container .slides {
        display: flex;
        transition: transform 0.5s ease-in-out;
        width: 100%;
    }
    .unique-slideshow-container .unique-slide {
        min-width: 100%;
        height: 100%; /* Ensure the slide takes the full height of the slideshow */
        box-sizing: border-box;
        text-align: center;
        padding: 10px; 
        background-size: 100% 100%; /* Stretches the image completely in width and height */
        background-position: center;
        background-repeat: no-repeat;
        color: #fff;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
    }


    .unique-slideshow-container .unique-slide h3 {
        margin: 5px 0;
        font-size: 1.2em;
    }
    .unique-slideshow-container .unique-slide p {
        font-size: 0.9em; 
    }
    .unique-slideshow-container .nav-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        border: none;
        padding: 8px; 
        cursor: pointer;
        font-size: 1.2em; 
    }
    .unique-slideshow-container .prev {
        left: 5px; 
    }
    .unique-slideshow-container .next {
        right: 5px; 
    }
</style>



<div class="unique-slideshow-container">
    <div class="slideshow">
        <div class="slides" id="unique-slides">
            <?= $slides; ?>
        </div>
        <button class="nav-button prev" onclick="changeSlide(-1)">&#10094;</button>
        <button class="nav-button next" onclick="changeSlide(1)">&#10095;</button>
    </div>
</div>

<script>
    let currentIndex = 0;

    function changeSlide(direction) {
        const slides = document.getElementById('unique-slides');
        const slideCount = slides.children.length;
        currentIndex = (currentIndex + direction + slideCount) % slideCount;
        slides.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    // Auto-slide every 3 seconds
    setInterval(() => changeSlide(1), 5000);
</script>
