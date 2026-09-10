<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Contact Us</title>
    <meta name="description" content="Contact BBN Bites">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/contact.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
</head>
<body>

<?php include '../header-footer/header.php'; ?>

<main>
    <section class="contact-page">
        <div class="container">
            <div class="contact-title">
                <p class="contact-eyebrow">BBN BITES</p>
                <h1>CONTACT US</h1>
                <p>BURGERS &amp; QUESADILLAS</p>
            </div>

            <div class="contact-content">
                <div class="contact-info">
                    <h2>GET IN TOUCH</h2>
                    <p>
                        Have a question, suggestion, or want to know more about our food?
                        Send us a message and we will be happy to hear from you.
                    </p>

                    <div class="contact-details">
                        <div class="contact-detail">
                            <h3>EMAIL</h3>
                            <a href="mailto:bjadeconiemarie@gmail.com">bjadeconiemarie@gmail.com</a>
                        </div>

                        <div class="contact-detail">
                            <h3>PHONE</h3>
                            <a href="tel:+639618049468">+63 961 804 9468</a>
                        </div>

                        <div class="contact-detail">
                            <h3>LOCATION</h3>
                            <p>Bolisong, Manjuyod Negros Oriental</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form-wrapper">
                    <h2>SEND US A MESSAGE</h2>

                    <form class="contact-form" action="contact.php" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">FULL NAME</label>
                                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                            </div>

                            <div class="form-group">
                                <label for="email">EMAIL ADDRESS</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">PHONE NUMBER</label>
                                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                            </div>

                            <div class="form-group">
                                <label for="subject">SUBJECT</label>
                                <input type="text" id="subject" name="subject" placeholder="What is your message about?" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message">MESSAGE</label>
                            <textarea id="message" name="message" rows="7" placeholder="Write your message here..." required></textarea>
                        </div>

                        <button type="submit" class="btn contact-btn">SEND MESSAGE</button>
                    </form>
                </div>
            </div>

            <div class="contact-bottom">
                <h2>WE'D LOVE TO HEAR FROM YOU</h2>
                <p>
                    Whether you have feedback, questions, or simply want to say hello,
                    feel free to reach out to BBN Bites.
                </p>
                <a class="btn contact-menu-btn" href="menu.php">VIEW OUR MENU</a>
            </div>
        </div>
    </section>
</main>

<?php include '../header-footer/footer.php'; ?>

</body>
</html>