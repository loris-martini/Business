<?php
    include 'connessione.php';
    include 'funzioni.php';

    session_start();

    if(isset($_SESSION['user'])){
        $servizio = $date = $time = $message = '';
        $isFormValid = true;

        // Recupero barbieri e saloni per il form
        $query = "SELECT mail, nome, cognome FROM barbieri";
        $resultBarbieri = mysqli_query($db_conn, $query);

        $query = "SELECT indirizzo, nome FROM saloni";
        $resultSaloni = mysqli_query($db_conn, $query);

        if (isset($_POST['submit']) && $_SERVER["REQUEST_METHOD"] == "POST") {
            $date = filtro_testo($_POST['date']);
            $time = filtro_testo($_POST['time']);
            $barbiere = filtro_testo($_POST['barbiere']);

            if(empty($date) || empty($time)){
                $isFormValid = false;
                $message = "Tutti i campi sono obbligatori.";
            }else{
                $date = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['date']));
                $time = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['time']));
                $servizio = @mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['service']))));

                $query = "INSERT INTO appuntamenti (servizio, data_app, ora_inizio, fk_cliente, fk_barbiere, fk_salone) VALUES (?, ?, ?, ?, ?, ?)";

                try{
                    $stmt = mysqli_prepare($db_conn, $query);

                    mysqli_stmt_bind_param($stmt, "ssssss", $servizio, $date, $time, $_SESSION['user']['mail'], $barbiere, $_POST['salone']);

                    if(mysqli_stmt_execute($stmt)){
                        $message = "Appuntamento prenotato con successo!";
                        $servizio = $date = $time = '';
                    }
                } catch (Exception $ex) {
                    $message = mysqli_error($db_conn);
                }
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
            <h2>Benvenuto, <?=$_SESSION['user']['nome']?> <?=$_SESSION['user']['cognome']?>!</h2>
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
                                <option value="">Seleziona un barbiere</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultSaloni)) { ?>
                                    <option value='<?=$row['indirizzo']?>'><?=$row['nome']?>: (<?=$row['indirizzo']?>)</option>
                                <?php };?>
                            </select>
                        </td>
                    </tr>
                    <!--SERVIZIO-->
                    <tr id="service-container" style="display:none;">
                        <td><label class="form-label title">Servizio</label></td>
                        <td>
                            <select id="service" name="service" required>
                                <option value="">Seleziona un servizio</option>
                            </select>
                        </td>
                    </tr>
                    <!--BARBIERE-->
                    <tr id="barbiere-container" style="display:none;">
                        <td><label class="form-label title">Scegli il barbiere</label></td>
                        <td>
                            <select id="barbiere" name="barbiere" required>
                                <option value="">Seleziona un barbiere</option>
                            </select>
                        </td>
                    </tr>
                    <!--DATA E ORARI-->
                    <tr id="date-time" style="display:none;">
                        <td><label>Data e Ora</label></td>
                        <td>
                            <section id="date-time">
                                <input type="date" id="date" name="date" required>
                                <div id="slots-container" style="display:none;">
                                    <div id="slots"></div>
                                </div>
                                <input type="hidden" id="selected-time" name="time">
                            </section>
                        </td>
                    </tr>
                    <!--ALTRO-->
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
</html>
