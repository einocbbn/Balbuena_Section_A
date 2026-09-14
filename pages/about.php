<?php
require_once '../database/customer_auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | About Us</title>
    <meta name="description" content="Learn more about BBN Bites">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/about.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
</head>
<body>

<?php include '../header-footer/header.php'; ?>

<main>
    <section class="about-page">
        <div class="container">
            <div class="about-title">
                <p class="about-eyebrow">BBN BITES</p>
                <h1>ABOUT US</h1>
                <p>BURGERS &amp; QUESADILLAS</p>
            </div>

            <div class="about-content">
                <div class="about-image">
                    <img src="../assets/mission-burger.jpg" alt="BBN Bites burger">
                </div>

                <div class="about-copy">
                    <h2>WHO WE ARE</h2>
                    <p>
                        BBN Bites is a food business that brings together delicious burgers,
                        quesadillas, and satisfying snacks made for every customer to enjoy.
                        We believe that good food is more than just a meal—it is something
                        that brings people together.
                    </p>
                    <p>
                        Our goal is to provide freshly made, flavorful, and affordable food
                        while giving our customers a friendly and enjoyable experience with
                        every visit.
                    </p>
                </div>
            </div>

            <div class="about-values">
                <div class="value-card">
                    <h3>QUALITY</h3>
                    <p>We aim to serve fresh and delicious food made with care and attention to every bite.</p>
                </div>

                <div class="value-card">
                    <h3>FLAVOR</h3>
                    <p>We create satisfying burgers, quesadillas, and snacks that are packed with great taste.</p>
                </div>

                <div class="value-card">
                    <h3>COMMUNITY</h3>
                    <p>We believe food is best enjoyed when shared with family, friends, and the community.</p>
                </div>
            </div>

            <div class="about-bottom">
                <h2>GATHER. SHARE. SAVOR.</h2>
                <p>
                    At BBN Bites, every bite is made to be enjoyed and shared.
                    We look forward to serving you and making every snack experience memorable.
                </p>
                <a class="btn about-btn" href="menu.php">VIEW OUR MENU</a>
            </div>
        </div>
    </section>
</main>

<?php include '../header-footer/footer.php'; ?>

</body>
</html>