<?php
    include 'connessione.php';
    include 'funzioni.php';

    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    $query = "SELECT ruolo FROM utenti WHERE mail = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']['mail']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if($user['ruolo'] != 'BARBIERE'){
        header("Location: index.php");
        exit();
    }

    $query = "SELECT * 
        FROM appuntamenti a JOIN turni_barbieri t ON a.fk_turno = t.id_turno 
        WHERE t.fk_barbiere = ?";
        
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']['mail']);
    mysqli_stmt_execute($stmt);
    $resultAppuntamenti = mysqli_stmt_get_result($stmt);    
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
        <table class="appuntamenti">
            <tr>
                <td>Nome</td>
                <td>Contatto</td>
                <td>Servizio</td>
                <td>Data</td>
                <td>Ora</td>
            </tr>    
            <?php while($row = mysqli_fetch_assoc($resultAppuntamenti)){
                $query = "SELECT * FROM utenti WHERE mail = ?";
                $stmt = mysqli_prepare($db_conn, $query);
                mysqli_stmt_bind_param($stmt, "s", $row['fk_cliente']);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                
                $query = "SELECT nome, durata FROM servizi WHERE id_servizio = ?";
                $stmt = mysqli_prepare($db_conn, $query);
                mysqli_stmt_bind_param($stmt, "s", $row['fk_servizio']);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $servizio = mysqli_fetch_assoc($result);
                ?>
                <tr>
                    <td><?=$user['nome']?> <?=$user['cognome']?></td>
                    <td><?=$user['mail']?><br><?=$user['numero_telefono']?></td>
                    <td><?=$servizio['nome']?></td>
                    <td><?=$row['data_app']?></td>
                    <td><?=$row['ora_inizio']?> - <?=date("H:i", strtotime($row['ora_inizio']) + ($servizio['durata']) * 60)?></td>
                </tr>
            <?php } ?>

        </table>   
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
</body>
</html>
