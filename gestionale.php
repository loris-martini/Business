<?php
    include 'connessione.php';
    include 'funzioni.php';

    session_start();

    if(isset($_SESSION['user'])){
        $servizio = $date = $time = $message = '';
        $isFormValid = true;

        $query = "SELECT mail, nome, cognome FROM barbieri";
        $result = mysqli_query($db_conn, $query);

        if (isset($_POST['submit']) && $_SERVER["REQUEST_METHOD"] == "POST") {
            $date = filtro_testo($_POST['date']);   
            $time = filtro_testo($_POST['time']);

            if(empty($date) || empty($time)){
                $isFormValid = false;
                $message = "Tutti i campi sono obbligatori.";
            }else{
                $date       = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['date']));
                $time       = @mysqli_real_escape_string($db_conn, filtro_testo($_POST['time']));
                $servizio   = @mysqli_real_escape_string($db_conn, ucwords(strtolower(filtro_testo($_POST['service']))));

                $query = "INSERT INTO appuntamenti (servizio, data_app, ora_inizio, fk_cliente) VALUES (?, ?, ?, ?)";

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
