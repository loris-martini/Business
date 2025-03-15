<?php
include 'connessione.php';
include 'funzioni.php';

if (isset($_GET['salone'])) {
    $salone = mysqli_real_escape_string($db_conn, filtro_testo($_GET['salone']));
    
    // Recupera i servizi per il salone specificato tramite la tabella "propone"
    $query = "SELECT s.id_servizio, s.nome 
              FROM servizi s
              JOIN propone p ON s.id_servizio = p.fk_servizio
              WHERE p.fk_salone = ?";
    
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $salone);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $services = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }

    echo json_encode($services);
}
?>
