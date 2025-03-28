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
    }

    // Cancellazione dei dati dell'utente
    if (isset($_POST['delete']) && $_SERVER["REQUEST_METHOD"] == "POST") {
        $query = "DELETE FROM utenti WHERE mail = ?";
        $stmt = mysqli_prepare($db_conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']['mail']);
        mysqli_stmt_execute($stmt);

        session_destroy();
        header("Location: index.php");    
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
        <h2>Benvenuto, <?= $user['nome'] . " " . $user['cognome']; ?>!</h2>
        <p>Gestisci i tuoi dati e prenotazioni.</p>
    </section>

    <section class="user-info">
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <table>
                <tr>
                    <td>Nome</td>
                    <td><input type="text" name="nome" value="<?=isset($user['nome']) ? htmlspecialchars($user['nome']) : ""?>"></td>
                </tr>
                <tr>
                    <td>Cognome</td>
                    <td><input type="text" name="cognome" value="<?=isset($user['cognome']) ? htmlspecialchars($user['cognome']) : ""?>"></td>
                </tr>
                <tr>
                    <td>Mail</td>
                    <td><input type="text" name="mail" value="<?=isset($user['mail']) ? htmlspecialchars($user['mail']) : ""?>" readonly></td>
                </tr>
                <tr>
                    <td>Telefono</td>
                    <td><input type="text" name="telefono" value="<?=isset($user['numero_telefono']) ? htmlspecialchars($user['numero_telefono']) : ""?>"></td>
                </tr>
                <tr>
                    <td>Genere</td>
                    <td><select class="form-control" name="genere">
                        <option value="">Seleziona il genere</option>
                        <option value="M" <?= (isset($user['genere']) && $user['genere'] == 'M') ? 'selected' : ''; ?>>Maschio</option>
                        <option value="F" <?= (isset($user['genere']) && $user['genere'] == 'F') ? 'selected' : ''; ?>>Femmina</option>
                    </select></td>
                </tr>
                <tr>
                    <td>Indirizzo</td>
                    <td><input type="text" name="residenza" value="<?=isset($user['residenza']) ? htmlspecialchars($user['residenza']) : ""?>"></td>
                </tr>
                <tr>
                    <td>Data di Nascita</td>
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
