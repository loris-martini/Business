<?php
include 'connessione.php';
include 'funzioni.php';
session_start();

if (!isset($_GET['id'])){
    die('autenticazione fallita');
    exit();
}

$id = filtro_testo($_GET['id']);

$query = "SELECT * FROM appuntamenti a JOIN servizi s ON a.id_appuntamento = s.id_servizio WHERE a.id_appuntamento = ?";
$stmt = mysqli_prepare($db_conn, $query);
mysqli_stmt_bind_param($stmt, 's', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appuntamento = mysqli_fetch_assoc($result);
if((!$appuntamento) || ($appuntamento['stato'] != 'IN_ATTESA') || empty($appuntamento['codice'])){
    die('autenticazione fallita');
    exit();
}

if (isset($_POST['submit']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $codice = filtro_testo($_POST['codice']);
    if($codice == $appuntamento['codice']){
        $query = "UPDATE appuntamenti SET stato = 'CONFERMATO', codice = NULL WHERE id_appuntamento = ?";
        $stmt = mysqli_prepare($db_conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        if(mysqli_stmt_affected_rows($stmt) > 0){
            die('Appuntamento confermato con successo!');
            exit();
        } else {
            die('autenticazione fallita');
            exit();
        }
    } else {
        die('Codice non valido.');
        header("Refresh:2");
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Appuntamento</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style-index.css">
</head>
<body>
    <main>
        <section>
            <h2>Conferma Appuntamento</h2>
            <center>
            <form id="form-registration" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . urlencode($_GET['id']); ?>" method="POST">
                <h1>Dettagli Appuntamento</h1>
                <table>
                    <tr>
                        <td><label>Data:</label></td>
                        <td><label><?=date("d/m/Y", strtotime($appuntamento['data_app']))?></label></td>
                    </tr>
                    <tr>
                        <td><label>Ora:</label></td>
                        <td><label><?=date("H:i", strtotime($appuntamento['ora_inizio']))?></label></td>
                    </tr>
                    <tr>
                        <td><label>Utente:</label></td>
                        <td><label><?=$appuntamento['fk_cliente']?></label></td>
                    </tr>
                    <tr>
                        <td><label>Servizio:</label></td>
                        <td><label><?=$appuntamento['nome']?></label></td>
                    </tr>
                </table>    
                <label class="form-label title">Codice utente: </label></td>
                <input type="text" name="codice" placeholder="000000" maxlength="6" required><br>
                <input class="btn" type="submit" name="submit" value="Conferma Appuntamento">
            </form>
            </center>
        </section>
    </main>    
</html>