<?php
    include 'connessione.php';
    include 'funzioni.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Tuo Salone di Parrucchiere</title>
    <link rel="stylesheet" href="./css/style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Il Tuo Salone</h1>
        </div>

        <div id="menu"></div>
    </header>

    <?php if(isset($_SESSION['logged']) && $_SESSION['logged']){?>
        <section id="home" class="hero">
            <h2>Benvenuto Stronzo</h2>
            <p>Prenota il tuo appuntamento comodamente online.</p>
        </section>
    <?php }else{ ?>
        <section id="home" class="hero">
            <h2>Benvenuto nel nostro salone!</h2>
            <p>Prenota il tuo appuntamento comodamente online.</p>
        </section>
    
    <?php }; ?>

    <section id="gallery">
        <h2>Galleria</h2>
        <div class="gallery-grid">
            <!--<img src="img1.jpg" alt="Taglio di capelli">
            <img src="img2.jpg" alt="Acconciatura elegante">
            <img src="img3.jpg" alt="Colorazione capelli">-->
        </div>
    </section>

    <section id="booking">
        <h2>Prenota il tuo appuntamento</h2>
        <form id="booking-form" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
            <label for="name">Nome:</label>
            <input type="text" name="name" required>

            <label for="service">Servizio:</label>
            <select name="service" required>
                <option value="taglio">Taglio</option>
            </select>

            <label for="date">Data e Ora:</label>
            <input type="date" name="date" required>
            <input type="time" name="time" required>

            <button type="submit">Prenota Appuntamento</button>
        </form>
    </section>

    <section id="contact">
        <h2>Contattaci</h2>
        <p>Email: info@tuosalone.com</p>
        <p>Telefono: +39 012 3456789</p>
    </section>

    <footer>
        <p>&copy; 2025 Il Tuo Salone di Parrucchiere</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
