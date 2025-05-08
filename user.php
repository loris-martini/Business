<?php
include 'connessione.php';
include 'funzioni.php';

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$message = '';
try {
    $query = "SELECT * FROM utenti WHERE mail = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']['mail']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }else{
        $message = "Utente non trovato!";
    }

    $query = "SELECT a.*, s.nome FROM appuntamenti a JOIN servizi s ON a.fk_servizio = s.id_servizio WHERE fk_cliente = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']['mail']);
    mysqli_stmt_execute($stmt);
    $resultApp = mysqli_stmt_get_result($stmt);

    if (isset($_POST['update']) && $_SERVER["REQUEST_METHOD"] == "POST") {
        $nome =         isset($_POST['nome']) && !empty($_POST['nome']) ? mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['nome'])))) : null;
        $cognome =      isset($_POST['cognome']) && !empty($_POST['cognome']) ? mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['cognome'])))) : null;
        $telefono =     isset($_POST['telefono']) && !empty($_POST['telefono']) ? mysqli_real_escape_string($db_conn, filtro_testo($_POST['telefono'])) : null;
        $genere =       isset($_POST['genere']) ? mysqli_real_escape_string($db_conn, filtro_testo($_POST['genere'])) : null;
        $residenza =    isset($_POST['residenza']) ? mysqli_real_escape_string($db_conn, filtro_testo($_POST['residenza'])) : null;
        $data =         isset($_POST['data_nascita']) ? mysqli_real_escape_string($db_conn, filtro_testo($_POST['data_nascita'])) : null;

        $campi = [];
        $valori = [];

        if($nome == null || $cognome == null || $telefono == null){
            $message = "Stai cancellando campi obbligatori.";
        }else{
            if ($nome !== null) {
                $campi[] = 'nome = ?';
                $valori[] = $nome;
            }
            if ($cognome !== null) {
                $campi[] = 'cognome = ?';
                $valori[] = $cognome;
            }
            if ($telefono !== null) {
                $campi[] = 'numero_telefono = ?';
                $valori[] = $telefono;
            }
            if ($genere !== null) {
                $campi[] = 'genere = ?';
                $valori[] = $genere;
            }
            if ($residenza !== null) {
                $campi[] = 'residenza = ?';
                $valori[] = $residenza;
            }
            if ($data !== null) {
                $campi[] = 'data_nascita = ?';
                $valori[] = $data;
            }
        }

        if (count($campi) > 0) {
            $query = "UPDATE utenti SET " . implode(", ", $campi) . " WHERE mail = ?";
            $stmt = mysqli_prepare($db_conn, $query);
            $types = str_repeat('s', count($valori)) . 's';
            $params = array_merge($valori, [$_SESSION['user']['mail']]);
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            if (mysqli_stmt_execute($stmt)) {
                foreach ($valori as $index => $valore) {
                    if ($campi[$index] == 'nome = ?') $_SESSION['user']['nome'] = $valore;
                    if ($campi[$index] == 'cognome = ?') $_SESSION['user']['cognome'] = $valore;
                    if ($campi[$index] == 'numero_telefono = ?') $_SESSION['user']['numero_telefono'] = $valore;
                    if ($campi[$index] == 'genere = ?') $_SESSION['user']['genere'] = $valore;
                    if ($campi[$index] == 'residenza = ?') $_SESSION['user']['residenza'] = $valore;
                    if ($campi[$index] == 'data_nascita = ?') $_SESSION['user']['data_nascita'] = $valore;
                }
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $message = "Errore nell'aggiornamento dei dati.";
            }
        }
    }

    // Logout dell'utente
    if (isset($_POST['logout']) && $_SERVER["REQUEST_METHOD"] == "POST") {
        session_destroy();
        header("Location: index.php");
        exit();
    }

    // Cancellazione dei dati dell'utente
    if (isset($_POST['delete']) && $_SERVER["REQUEST_METHOD"] == "POST") {
        $query = "DELETE FROM utenti WHERE mail = ?";
        $stmt = mysqli_prepare($db_conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']['mail']);
        mysqli_stmt_execute($stmt);

        session_destroy();
        header("Location: index.php");   
        exit(); 
    }


    // Cancellazione di un appuntamento
    if(isset($_POST['deleteApp']) && $_SERVER["REQUEST_METHOD"] == "POST"){
        $query = "DELETE FROM appuntamenti WHERE id_appuntamento = ?";
        $stmt = mysqli_prepare($db_conn, $query);
        mysqli_stmt_bind_param($stmt, "s", filtro_testo($_POST['id_appuntamento']));
        mysqli_stmt_execute($stmt);
        header("Location: user.php");
        exit();
    }
} catch (Exception $ex) {
    $message = "Errore SQL: " . mysqli_error($db_conn);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Utente</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style-user.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Area Utente</h1>
        </div>
        <div id="menu"></div>
    </header>
    <main>

    <section class="hero">
    <h2><?php if($_SESSION['user']['genere'] == "M"){?>Benvenuto,<?php }else if($_SESSION['user']['genere'] == "F"){?> Benvenuta, <?php }else{ ?> Benvenut* <?php };
            if($_SESSION['user']['ruolo'] == 'BARBIERE' || $_SESSION['user']['ruolo'] == 'ADMIN'){?>Sign. <?php }else{ ?> <?php } ?> <?=$_SESSION['user']['nome']?> <?=$_SESSION['user']['cognome']?>!</h2>
        <p>Gestisci i tuoi dati e prenotazioni.</p>
    </section>

    <?php if (mysqli_num_rows($resultApp) > 0) { ?>
        <h2>Appuntamenti:</h2>
        <section class="user-info">
            <table>
                <tr>
                    <td class="first">Servizio</td>
                    <td class="first">Data</td>
                    <td class="first">Ora</td>
                    <td class="first">Stato</td>
                </tr>
                <?php while($appuntamenti = mysqli_fetch_assoc($resultApp)){ 
                    $colore = '';
                    $testoStato = '';
                    switch($appuntamenti['stato']){
                        case "IN_ATTESA":
                            $colore = 'red';
                            $testoStato = 'Conferma tramite mail!';
                            break;
                        case "CONFERMATO":
                            $colore = 'green';
                            $testoStato = 'Confermato!';
                            break;
                        case "COMPLETATO":
                            $colore = 'blue';
                            $testoStato = 'Completato!';
                            break;
                        case "CANCELLATO":
                            $colore = 'gray';
                            $testoStato = 'Cancellato!';
                            break;
                    }
                ?>
                <tr>
                    <td><?=$appuntamenti['nome']?></td>
                    <td><?=$appuntamenti['data_app']?></td>
                    <td><?=$appuntamenti['ora_inizio']?></td>
                    <td style="color: <?=$colore?>; font-weight: bold;"><?=$testoStato?></td>
                    <td>
                        <?php if ($appuntamenti['stato'] != "CANCELLATO") { ?>
                            <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirm('Sei sicuro di voler cancellare questo appuntamento?');">
                                <input type="hidden" name="id_appuntamento" value="<?=$appuntamenti['id_appuntamento']?>">
                                <button type="submit" name="deleteApp">Cancella</button>
                            </form>
                        <?php } else { echo "-"; } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </section>
    <?php }elseif($user['ruolo'] == 'CLIENTE'){?>
        <h2>Nessun Appuntamento Registrato!</h2>
    <?php }?>

    <h2>Dati Personali:</h2>
    <section class="user-info">
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <table>
                <tr>
                    <td class="first">Nome</td>
                    <td><input type="text" name="nome" value="<?=isset($user['nome']) ? htmlspecialchars($user['nome']) : ""?>"></td>
                </tr>
                <tr>
                    <td class="first">Cognome</td>
                    <td><input type="text" name="cognome" value="<?=isset($user['cognome']) ? htmlspecialchars($user['cognome']) : ""?>"></td>
                </tr>
                <tr>
                    <td class="first">Mail</td>
                    <td><input type="text" name="mail" value="<?=isset($user['mail']) ? htmlspecialchars($user['mail']) : ""?>" readonly></td>
                </tr>
                <tr>
                    <td class="first">Telefono</td>
                    <td><input type="text" name="telefono" value="<?=isset($user['numero_telefono']) ? htmlspecialchars($user['numero_telefono']) : ""?>"></td>
                </tr>
                <tr>
                    <td class="first">Genere</td>
                    <td><select class="form-control" name="genere">
                        <option value="">Seleziona il genere</option>
                        <option value="M" <?= (isset($user['genere']) && $user['genere'] == 'M') ? 'selected' : ''; ?>>Maschio</option>
                        <option value="F" <?= (isset($user['genere']) && $user['genere'] == 'F') ? 'selected' : ''; ?>>Femmina</option>
                    </select></td>
                </tr>
                <tr>
                    <td class="first">Indirizzo</td>
                    <td><input type="text" name="residenza" value="<?=isset($user['residenza']) ? htmlspecialchars($user['residenza']) : ""?>"></td>
                </tr>
                <tr>
                    <td class="first">Data di Nascita</td>
                    <td><input type="date" name="data_nascita" value="<?=isset($user['data_nascita']) ? htmlspecialchars($user['data_nascita']) : ""?>"></td>
                </tr>
            </table>

            <?php if(!empty($message)){?>
            <div class="error-box"><?=$message; ?></div>
            <?php };?>

            <input type="submit" name="update" value="Salva Modifiche" class="btn">
        </form>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return confirmLogout();">
            <input class="btn" type="submit" name="logout" value="Log Out">
        </form>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return confirmDelete();">
            <input class="delete" type="submit" name="delete" value="Cancella Dati">
        </form>
    </section>

    <section id="contact">
        <h2>Contattaci</h2>
        <p>Email: info@tuosalone.com</p>
        <p>Telefono: +39 012 3456789</p>
    </section>
    </main>
    <footer>
        <p>&copy; 2025 Il Tuo Salone di Parrucchiere</p>
    </footer>
</body>
</html>
