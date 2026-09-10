<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN Bites | Burgers & Quesadillas</title>
    <meta name="description" content="BBN Bites">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="header-footer/header.css">
    <link rel="stylesheet" href="header-footer/footer.css">
</head>
<body>

<?php include 'header-footer/header.php'; ?>

<main>
    <section class="hero" id="home">
        <img class="hero-food hero-burger" src="assets/hero-burger.png" alt="" aria-hidden="true">
        <img class="hero-food hero-quesadilla" src="assets/hero-quesadilla.png" alt="" aria-hidden="true">
        <img class="hero-fries fries-left" src="assets/fries-left.png" alt="" aria-hidden="true">
        <img class="hero-fries fries-right" src="assets/fries-right.png" alt="" aria-hidden="true">
        <div class="hero-content">
            <h1>BBN BITES</h1>
            <p class="hero-subtitle">BURGERS &amp; QUESADILLAS</p>
            <p class="tagline">WHERE EVERY BITE, HITS RIGHT</p>
            <div class="hero-actions">
                <a class="btn btn-ordernow" href="#menu">ORDER NOW</a>
                <a class="btn btn-menu" href="#menu">MENU</a>
            </div>
        </div>
    </section>

    <section class="menu-section" id="menu">
        <div class="container">
            <div class="section-heading">
                <h2>BEST SELLERS</h2>
                <!-- <span></span> -->
            </div>

            <div class="product-grid">
                <article class="product-card">
                    <div class="product-image"><img src="assets/biggie-burger.jpg" alt="Biggie Burger"></div>
                    <h3>BIGGIE BURGER</h3>
                </article>
                <article class="product-card">
                    <div class="product-image"><img src="assets/classic-quesadilla.jpg" alt="Classic Quesadilla"></div>
                    <h3>CLASSIC QUESADILLA</h3>
                </article>
                <article class="product-card">
                    <div class="product-image"><img src="assets/bacon-burger.jpg" alt="Bacon Burger"></div>
                    <h3>BACON BURGER</h3>
                </article>
                <article class="product-card">
                    <div class="product-image"><img src="assets/spinach-chicken-quesadilla.jpg" alt="Spinach Chicken Quesadilla"></div>
                    <h3>SPINACH CHICKEN<br>QUESADILLA</h3>
                </article>
            </div>

            <div class="center-action">
                <a class="btn btn-viewall" href="pages/menu.php">VIEW ALL MENU</a>
            </div>

            <div class="section-heading extras-heading">
                <h2>EXTRAS</h2>
                <!-- <span></span> -->
            </div>

            <div class="product-grid">
                <article class="product-card">
                    <div class="product-image"><img src="assets/lumpiang-shanghai.jpg" alt="Lumpiang Shanghai"></div>
                    <h3>LUMPIANG SHANGHAI</h3>
                </article>
                <article class="product-card">
                    <div class="product-image"><img src="assets/chicksilog.jpg" alt="Chicksilog"></div>
                    <h3>CHICKSILOG</h3>
                </article>
                <article class="product-card">
                    <div class="product-image"><img src="assets/liemposilog.jpg" alt="Liempo Silog"></div>
                    <h3>LIEMPOSILOG</h3>
                </article>
                <article class="product-card">
                    <div class="product-image"><img src="assets/hungariansilog.jpg" alt="Hungarian Silog"></div>
                    <h3>HUNGARIANSILOG</h3>
                </article>
            </div>

            <div class="center-action">
                <a class="btn btn-viewall" href="pages/menu.php">VIEW ALL MENU</a>
            </div>
        </div>
    </section>

    <section class="mission-section" id="mission">
        <div class="container mission-wrap">
            <div class="mission-copy">
                <h2>OUR MISSION</h2>
                <p>
                    To serve freshly made, delicious, and affordable snacks that bring
                    people together, delivering quality, great taste, and friendly service
                    in every bite while creating memorable snack experiences for every customer.
                </p>
                <a class="btn btn-outline" href="pages/mission.php">LEARN MORE</a>
            </div>
            <div class="mission-image">
                <img src="assets/mission-burger.jpg" alt="BBN Bites burger being prepared">
            </div>
        </div>
    </section>

    <section class="hours-section" id="about">
        <div class="container">
            <img class="store-banner" src="assets/store-banner.jpg" alt="BBN Bites food">
            <h2>STORE HOURS</h2>
            <div class="hours">
                <div><span>Monday - Friday</span><strong>10:00 AM - 9:00 PM</strong></div>
                <div><span>Saturday</span><strong>03:00 PM - 10:00 PM</strong></div>
                <div><span>Sunday</span><strong>CLOSED</strong></div>
            </div>
        </div>
    </section>
</main>

<?php include 'header-footer/footer.php'; ?>

</body>
</html>