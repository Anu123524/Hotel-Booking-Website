    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Anup Shet">
    <title>Grand Vista | Premium Hotel Sanctuary</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <nav id="navbar">
        <a href="index.php" class="nav-brand">GRAND VISTA</a>
        <ul class="nav-links">
            <?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
?>
                <li><a href="index.php">Home</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>

    <script>
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>
