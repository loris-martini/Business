<?php
include 'connessione.php';
include 'funzioni.php';

if (isset($_GET['servizio'])) {
    $servizio = mysqli_real_escape_string($db_conn, filtro_testo($_GET['servizio']));
    
    // Recupera i barbieri che offrono il servizio specificato
    $query = "SELECT u.mail, u.nome, u.cognome 
              FROM utenti u
              JOIN barbiere_servizio bs ON u.mail = bs.fk_barbiere
              WHERE bs.fk_servizio = ? AND u.ruolo = 'BARBIERE'";

    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $servizio);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $barbers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $barbers[] = $row;
    }

    echo json_encode($barbers);
}
?>
