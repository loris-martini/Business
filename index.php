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
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Il Tuo Salone</h1>
        </div>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#services">Servizi</a></li>
                <li><a href="#gallery">Galleria</a></li>
                <li><a href="#booking">Prenota Appuntamento</a></li>
                <li><a href="#contact">Contatti</a></li>
            </ul>
        </nav>
    </header>

    <section id="home" class="hero">
        <h2>Benvenuto nel nostro salone!</h2>
        <p>Scopri i nostri servizi e prenota il tuo appuntamento comodamente online.</p>
    </section>

    <section id="services">
        <h2>I nostri servizi</h2>
        <div class="service-list">
            <div class="service">
                <h3>Taglio e Styling</h3>
                <p>Tagli moderni e stile su misura per te.</p>
            </div>
            <div class="service">
                <h3>Colorazione</h3>
                <p>Colori brillanti e duraturi per ogni esigenza.</p>
            </div>
            <div class="service">
                <h3>Trattamenti</h3>
                <p>Trattamenti rigeneranti per capelli sani e lucenti.</p>
            </div>
        </div>
    </section>

    <section id="gallery">
        <h2>Galleria</h2>
        <div class="gallery-grid">
            <img src="img1.jpg" alt="Taglio di capelli">
            <img src="img2.jpg" alt="Acconciatura elegante">
            <img src="img3.jpg" alt="Colorazione capelli">
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
</body>
</html>
