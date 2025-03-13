<?php
    include 'connessione.php';
    include 'funzioni.php';

    session_start();
    $servizio = $date = $time = $message = '';
    $isFormValid = true;

    if (isset($_POST['submit']) && $_SERVER["REQUEST_METHOD"] == "POST") {
        $date = filtro_testo($_POST['date']);   
        $time = filtro_testo($_POST['time']);

        if(empty($date)){
            $isFormValid = false;
        }if(empty($time)){
            $isFormValid = false;
        }

        if($isFormValid){
            $date       = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['date']));
            $time       = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['time']));
            $servizio   = @mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['service']))));
        }

        $query = "INSERT INTO tappuntamenti (servizio, data_app, ora_inizio, fk_cliente) VALUES (?, ?, ?, ?)";

        try{
            $stmt = mysqli_prepare($db_conn, $query);

            mysqli_stmt_bind_param($stmt, "ssss", $servizio, $date, $time, $_SESSION['user']['mail']);

            if(mysqli_stmt_execute($stmt)){
                $message = "Appuntamento prenotato con successo!";
                $servizio = $date = $time = '';
            }
        }catch(Exception $ex){
            $message = mysqli_error($db_conn);
        }
    }

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Tuo Salone di Parrucchiere</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style-index.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Il Tuo Salone</h1>
        </div>

        <div id="menu"></div>
    </header>
    <main>

    <?php if(isset($_SESSION['user'])){?>
        <section id="home" class="hero">
            <h2>Benvenuto, <?=$_SESSION['user']['nome']?> <?=$_SESSION['user']['cognome']?>!</h2>
            <p>Prenota il tuo appuntamento qua sotto!</p>
        </section>
    <?php }else{ ?>
        <section id="home" class="hero">
            <h2>Benvenuto nel nostro salone!</h2>
            <p>Prenota il tuo appuntamento registrandoti!</p>
        </section>
    
    <?php }; ?>

    <!--
    <section id="gallery">
        <h2>Galleria</h2>
        <div class="gallery-grid">
            <img src="img1.jpg" alt="Taglio di capelli">
            <img src="img2.jpg" alt="Acconciatura elegante">
            <img src="img3.jpg" alt="Colorazione capelli">
        </div>
    </section>
    -->

    <?php if(isset($_SESSION['user'])){?>
        <center>
            <h2>Prenota il tuo appuntamento</h2>
            <form class="row g-3" id="form-registration" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                <table>
                    <tr>
                        <td><label>Nome</label></td>
                        <td>
                            <input type="text" name="nome" value="<?= $_SESSION['user']['nome'] ?? '' ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td><label class="form-label title">Cognome</label></td>
                        <td>
                            <input type="text" name="cognome" value="<?= $_SESSION['user']['cognome'] ?? '' ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td><label class="form-label title">E-mail</label></td>
                        <td>
                            <input type="email" name="mail" value="<?= $_SESSION['user']['mail'] ?? '' ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td><label class="form-label title">Servizio</label></td>
                        <td>
                            <select name="service">
                                <option value="taglio">Taglio</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label>Data e Ora</label></td>
                        <td>
                            <section id="date-time">
                                <input type="date" name="date" required>
                                <input type="time" name="time" required>
                            </section>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="text-danger"><?= $message; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input class="btn" type="submit" name="submit" value="Prenota Appuntamento">
                        </td>
                    </tr>
                </table>
            </form>
        </center>
    <?php }else{ ?>
        <center>
        <br>
        <h2>Per prenotare devi prima registrarti!</h2>
        </center>
    <?php }; ?>
    </main>
    <section id="contact">
        <h2>Contattaci</h2>
        <p>Email: info@tuosalone.com</p>
        <p>Telefono: +39 012 3456789</p>
    </section>
        <br>
    <footer>
        <p>&copy; 2025 Il Tuo Salone di Parrucchiere</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
