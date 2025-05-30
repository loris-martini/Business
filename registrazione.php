<?php

include 'connessione.php';
include 'funzioni.php';

session_start();
$_SESSION['logged'] = false;
$nome = $cognome = $mail = $password = $telefono = $genere = $residenza = $data = "";
$nomeErr = $cognomeErr = $mailErr = $passwordErr = $telefonoErr = $residenzaErr = $dataErr = $message = "";
$dangerNome = $dangerCognome = $dangerMail = $dangerPassword = $dangerTelefono = $dangerResidenza = $dangerData = "";
$isFormValid = true;
$pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+={}|;:,.<>?\/-]).{8,}$/';  //password

if (isset($_POST['submit']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $nome =         filtro_testo($_POST['nome']);
    $cognome =      filtro_testo($_POST['cognome']);
    $mail =         filtro_testo($_POST['mail']);
    $password =     $_POST['password'];
    $telefono =     filtro_testo($_POST['telefono']);
    $genere =       filtro_testo($_POST['genere']);
    $residenza =    filtro_testo($_POST['residenza']);
    $data =         filtro_testo($_POST['data']);

    $_SESSION['local']['nome'] = $nome;
    $_SESSION['local']['cognome'] = $cognome;
    $_SESSION['local']['mail'] = $mail;
    $_SESSION['local']['telefono'] = $telefono;
    $_SESSION['local']['genere'] = $genere;
    $_SESSION['local']['residenza'] = $residenza;
    $_SESSION['local']['data'] = $data;

    if(empty($nome)){
        $nomeErr = "Nome obbligatorio";
        $dangerNome = 'error-box';
        $isFormValid = false;
    }
    if(empty($cognome)){
        $cognomeErr = "Cognome obbligatorio";
        $dangerCognome = 'error-box';
        $isFormValid = false;
    }
    if(empty($mail)){
        $mailErr = "Mail obbligatoria";
        $dangerMail = 'error-box';
        $isFormValid = false;
    }
    if(empty($password)){
        $passwordErr = "Password obbligatoria";
        $dangerPassword = 'error-box';
        $isFormValid = false;
    }elseif (!preg_match($pattern, $password)) { 
        $passwordErr = "La password deve contenere almeno 8 caratteri, una lettera maiuscola, una lettera minuscola, un numero e un carattere speciale";
        $dangerPassword = 'error-box';
        $isFormValid = false;
    }
    if(empty($telefono)){
        $telefonoErr = "telefono obbligatorio";
        $dangerTelefono = 'error-box';
        $isFormValid = false;
    }


    if ($isFormValid) {
 
    try{    
        $nome =         @mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['nome']))));
        $cognome =      @mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['cognome']))));
        $mail =         @mysqli_real_escape_string($db_conn, strtolower(filtro_testo($_POST['mail'])));
        $password =     password_hash(@mysqli_real_escape_string($db_conn, filtro_testo($_POST['password'])), PASSWORD_BCRYPT);
        $telefono =     @mysqli_real_escape_string($db_conn, filtro_testo($_POST['telefono']));
        $genere =       @mysqli_real_escape_string($db_conn, filtro_testo($_POST['genere']));
        $residenza =    @mysqli_real_escape_string($db_conn, filtro_testo($_POST['residenza']));
        $data =         @mysqli_real_escape_string($db_conn, filtro_testo($_POST['data']));

        $campi = ['nome', 'cognome', 'mail', 'password', 'numero_telefono'];
        $segnaposti = ['?', '?', '?', '?', '?'];
        $valori = [$nome, $cognome, $mail, $password, $telefono];

        if ($genere !== null && $genere !== '') {
            $campi[] = 'genere';
            $segnaposti[] = '?';
            $valori[] = $genere;
        }
        if ($residenza !== null && $residenza !== '') {
            $campi[] = 'residenza';
            $segnaposti[] = '?';
            $valori[] = $residenza;
        }
        if ($data !== null && $data !== '') {
            $campi[] = 'data_nascita';
            $segnaposti[] = '?';
            $valori[] = $data;
        }


        $query = "INSERT INTO utenti (" . implode(", ", $campi) . ") VALUES (" . implode(", ", $segnaposti) . ")";

        $stmt = mysqli_prepare($db_conn, $query);

        $tipi = str_repeat('s', count($valori));
        $valoriArray = array_values($valori);
        mysqli_stmt_bind_param($stmt, $tipi, ...$valoriArray);

        if(mysqli_stmt_execute($stmt)){
            $message = "Registrzaione effettuata con successo!";
            $nome = $cognome = $mail = $password = $telefono = $genere = $residenza = $data = "";
            $nomeErr = $cognomeErr = $mailErr = $passwordErr = $telefonoErr = $residenzaErr = $dataErr = $message = "";
            $dangerNome = $dangerCognome = $dangerMail = $dangerPassword = $dangerTelefono = $dangerResidenza = $dangerData = "";
            session_unset();
            header("Location: login.php");
            exit();
        }else{
            die("Errore nel db: " . mysqli_stmt_error($stmt));
            exit();
        }
    }catch(Exception $ex){
        $message = mysqli_error($db_conn);
        if (@mysqli_errno($db_conn) == 1062) { 
            if (strpos(mysqli_error($db_conn), 'mail') !== false) {
                $mailErr = "La Mail è già stata registrata";
                $dangerMail = 'error-box';
            }
        }

        if (@mysqli_errno($db_conn) == 1644) {
            if (strpos(mysqli_error($db_conn), 'entrambi') !== false) {
                $message = "Nome e Cognome non validi";
                $dangerCognome = 'error-box';
                $dangerNome = 'error-box';
            }else{
                if (strpos(mysqli_error($db_conn), 'cognome') !== false) {
                    $cognomeErr = "Il Cognome non è valido";
                    $dangerCognome = 'error-box';
                }elseif (strpos(mysqli_error($db_conn), 'nome') !== false) {
                    $nomeErr = "Il Nome non è valido";
                    $dangerNome = 'error-box';
                }
            }
            if (strpos(mysqli_error($db_conn), 'data') !== false) {
                $dataErr = "Data di nascita non valida";
                $dangerData = 'error-box';
            }
            if(strpos(mysqli_error($db_conn), 'numero') !== false){
                $telefonoErr = "Numero non valido";
                $dangerTelefono = 'error-box';
            }
            if(strpos(mysqli_error($db_conn), 'mail') !== false){
                $mailErr = "Mail non valida";
                $dangerMail = 'error-box';
            }
            if(strpos(mysqli_error($db_conn), 'password') !== false){
                $passwordErr = "La password deve contenere almeno una lettera maiuscola, una minuscola, un numero e un carattere speciale";
                $dangerPassword = 'error-box';
            }   
        }
    }
    }
}

if (isset($_POST['reset']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $cognome = $mail = $password = $telefono = $genere = $residenza = $data = "";
    $nomeErr = $cognomeErr = $mailErr = $passwordErr = $telefonoErr = $residenzaErr = $dataErr = $message = "";
    $dangerNome = $dangerCognome = $dangerMail = $dangerPassword = $dangerTelefono = $dangerResidenza = $dangerData = "";
    unset($_SESSION['local']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style-index.css">
    <title>Registrati</title>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Registrati</h1>
        </div>
        <div id="menu"></div>
    </header>
    <main>
    <center>
    <div id="form-registration">
    <form class="row g-3" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
        <table>
            <tr>
                <td><label class="form-label title">Nome</label></td>
                <td>
                    <span class="error">* <?= $nomeErr;?></span>
                    <input type="text" class="form-control <?= $dangerNome;?>" name="nome" value="<?= isset($_SESSION['local']['nome']) ? $_SESSION['local']['nome'] : ''?>" placeholder="Nome">
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">Cognome</label></td>
                <td>
                    <span class="error">* <?= $cognomeErr;?></span>
                    <input type="text" class="form-control <?= $dangerCognome;?>" name="cognome" value="<?=isset($_SESSION['local']['cognome']) ? $_SESSION['local']['cognome'] : ''?>" placeholder="Cognome">
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">E-mail</label></td>
                <td>
                    <span class="error">* <?= $mailErr;?></span>
                    <input type="email" class="form-control <?= $dangerMail;?>" name="mail" value="<?=isset($_SESSION['local']['mail']) ? $_SESSION['local']['mail'] : ''?>" placeholder="Mail">
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">Password</label></td>
                <td>
                    <span class="error">* <?= $passwordErr;?></span>
                    <input type="password" class="form-control <?= $dangerPassword;?>" name="password" placeholder="Password">
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">Numero di Telefono</label></td>
                <td>
                    <span class="error">* <?= $telefonoErr;?></span>
                    <input type="text" class="form-control <?= $dangerTelefono;?>" name="telefono" value="<?=isset($_SESSION['local']['telefono']) ? $_SESSION['local']['telefono'] : ''?>" placeholder="Telefono">
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">Genere</label></td>
                <td>
                    <select class="form-control" name="genere">
                        <option value="">Seleziona il genere</option>
                        <option value="M" <?= (isset($_SESSION['local']['genere']) && $_SESSION['local']['genere'] == 'M') ? 'selected' : ''; ?>>Maschio</option>
                        <option value="F" <?= (isset($_SESSION['local']['genere']) && $_SESSION['local']['genere'] == 'F') ? 'selected' : ''; ?>>Femmina</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">Residenza</label></td>
                <td>
                    <span class="error"><?= $residenzaErr;?></span>
                    <input type="text" class="form-control <?= $dangerResidenza;?>" name="residenza" value="<?=isset($_SESSION['local']['residenza']) ? $_SESSION['local']['residenza'] : ''?>" placeholder="Via Mario Rossi 2">
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">Data di Nascita</label></td>
                <td>
                    <span class="error"><?= $dataErr;?></span>
                    <input type="date" class="form-control <?= $dangerData;?>" name="data" value="<?=isset($_SESSION['local']['data']) ? $_SESSION['local']['data'] : ''?>">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="text-danger"><?= $message; ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input class="btn" type="submit" name="submit" value="Registrati">
                    <input class="btn" type="submit" name="reset" value="Cancella">
                </td>
            </tr>
        </table>
    </form>

    </div>
    </center>
    </main>
    <footer>
        <p>&copy; 2025 Il Tuo Salone di Parrucchiere</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>