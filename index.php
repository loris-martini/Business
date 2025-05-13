<?php
include 'connessione.php';
include 'funzioni.php';

session_start();  

if(isset($_SESSION['user']['ruolo']) && $_SESSION['user']['ruolo'] == 'BARBIERE') {
    header("Location: gestionale.php");
    exit();
}elseif(isset($_SESSION['user']['ruolo']) && $_SESSION['user']['ruolo'] == 'ADMIN') {
    header("Location: adminOnly.php");
    exit();
}    

$date = $time = $barbiere = $servizio = $salone = $message = '';

$query = "SELECT id_salone, indirizzo, nome FROM saloni";
$resultSaloni = mysqli_query($db_conn, $query);

if (isset($_POST['submit']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $date = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['date']));
    $time = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['time']));
    $time = $time . ":00";
    $barbiere = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['barbiere']));
    $servizio = @mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['service']))));
    $salone = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['salone']));

    if (empty($date) || empty($time) || empty($barbiere) || empty($servizio) || empty($salone) || empty($_SESSION['user']['mail'])) {
        if (empty($_SESSION['user']['mail'])) {
            $message = "Utente non autenticato";
        } else {
            $message = "Tutti i campi sono obbligatori.";
        }
    } else {
        try {
            mysqli_begin_transaction($db_conn);

            $giorno = date('l', strtotime($date));

            $query = "SELECT id_turno 
                      FROM turni_barbieri 
                      WHERE fk_barbiere = ? 
                      AND giorno = ? 
                      AND ora_inizio <= ? 
                      AND ora_fine > ?";
            $stmt = mysqli_prepare($db_conn, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $barbiere, $giorno, $time, $time);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && $row = mysqli_fetch_assoc($result)) {
                $idTurno = $row['id_turno'];

                $query = "INSERT INTO appuntamenti (fk_cliente, fk_turno, fk_servizio, data_app, ora_inizio) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($db_conn, $query);
                mysqli_stmt_bind_param($stmt, "siiss", $_SESSION['user']['mail'], $idTurno, $servizio, $date, $time);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_commit($db_conn);

                    // Invia l'email prima del reindirizzamento
                    $subject = "Conferma il tuo appuntamento!";
                    $emailMessage = "<h1>Conferma Appuntamento</h1>
                                    <p>Ciao " . htmlspecialchars($_SESSION['user']['nome']) . ",</p>
                                    <p>Il tuo appuntamento è stato confermato per <strong>" . date("d/m/Y", strtotime($date)) . " alle " . date("H:i", strtotime($time)) . "</strong>.</p>";
                    $_SESSION['message'] = sendMail($subject, $emailMessage, $barbiere, $_SESSION['user']['mail']);

                    // Reindirizza e interrompi l'esecuzione
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                    exit();
                } else {
                    mysqli_rollback($db_conn);
                    $message = "Errore durante l'inserimento";
                }
            } else {
                mysqli_rollback($db_conn);
                $message = "Barbiere non disponibile per l'orario selezionato";
            }
        } catch (Exception $ex) {
            mysqli_rollback($db_conn);
            $message = "Errore SQL: " . $ex->getMessage();
        }
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
            <h2><?php if($_SESSION['user']['genere'] == "M"){?>Benvenuto,<?php }else if($_SESSION['user']['genere'] == "F"){?> Benvenuta, <?php }else{ ?> Benvenut* <?php };
            if($_SESSION['user']['ruolo'] == 'BARBIERE' || $_SESSION['user']['ruolo'] == 'ADMIN'){?>Sign. <?php }else{ ?> <?php } ?> <?=$_SESSION['user']['nome']?> <?=$_SESSION['user']['cognome']?>!</h2>
            <p>Prenota il tuo appuntamento qua sotto!</p>
        </section>
    <?php }else{ ?>
        <section id="home" class="hero">
            <h2>Benvenuto nel nostro salone!</h2>
            <p>Prenota il tuo appuntamento registrandoti!</p>
        </section>
    <?php }; ?>

    <?php 
    
    try{ if(isset($_SESSION['user'])){?>
        <center>
            <h2>Prenota il tuo appuntamento</h2>
            <form class="row g-3" id="form-registration" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                <table>
                <span style="font-size: 13px;"><i>*se da problemi il form riavviare la pagina</i></span>
                    <!--INFO-->
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
                    <!--SALONE-->
                    <tr>
                        <td><label class="form-label title">Scegli il Salone</label></td>
                        <td>
                            <select id="salone" name="salone" required>
                                <option value="">Seleziona un salone</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultSaloni)) { ?>
                                    <option value='<?=$row['id_salone']?>'><?=$row['nome']?>: (<?=$row['indirizzo']?>)</option>
                                <?php };?>
                            </select>
                        </td>
                    </tr>
                    <!--SERVIZIO-->
                    <tr id="service-container">
                        <td><label class="form-label title">Servizio</label></td>
                        <td>
                            <select id="service" name="service" required></select>
                        </td>
                    </tr>
                    <!--BARBIERE-->
                    <tr id="barbiere-container">
                        <td><label class="form-label title">Scegli il barbiere</label></td>
                        <td>
                            <select id="barbiere" name="barbiere" required></select>
                        </td>
                    </tr>
                    <!--DATA E ORARI-->
                    <tr id="date-time">
                        <td><label>Data e Ora</label></td>
                        <td>
                            <section id="date-time">
                                <input type="date" id="date" name="date" required>
                                <div id="slots-container">
                                    <select id="slots" required></select>
                                </div>
                                <input type="hidden" id="selected-time" name="time">
                            </section>
                        </td>
                    </tr>
                    <!--ALTRO-->
                    <tr>
                        <td colspan="2">
                            <?php if(!empty($message)){ ?>
                                <span class="text-danger"><?=$message?></span>
                            <?php }; ?>
                        </td>
                    </tr>
                    <!-- PREZZO -->
                    <tr>
                        <td id="prezzo-container"></td>
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
    <?php }; 
    }catch (Exception $ex) {
        $message = mysqli_error($db_conn);
    }?>
    </main>

    <section id="contact">
        <h2>Contattaci</h2>
        <p>Email: info@tuosalone.com</p>
        <p>Telefono: +39 012 3456789</p>
    </section>
    
    <footer>
        <p>&copy; 2025 Il Tuo Salone di Parrucchiere</p>
    </footer>
    <script>
        window.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                alert("Prenotazione effettuata con successo!");
                // Rimuove il parametro dalla URL senza ricaricare la pagina
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>
