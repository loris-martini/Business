<?php
include 'connessione.php';
include 'funzioni.php';

if (isset($_GET['barbiere']) && isset($_GET['data'])) {
    $barbiere =     mysqli_real_escape_string($db_conn, filtro_testo($_GET['barbiere']));
    $data =         mysqli_real_escape_string($db_conn, filtro_testo($_GET['data']));
    $service =      mysqli_real_escape_string($db_conn, filtro_testo($_GET['service']));

    $giorno = date('l', strtotime($data));
    
    // Recupera gli orari disponibili per il barbiere nella data specificata
    $query = "SELECT id_turno, ora_inizio, ora_fine FROM turni_barbieri WHERE giorno = ? AND fk_barbiere = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $giorno, $barbiere);
    mysqli_stmt_execute($stmt);
    $resultOrario = mysqli_stmt_get_result($stmt);

    // Recupera la durata del servizio
    $query = "SELECT durata FROM servizi WHERE id_servizio = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $service);
    mysqli_stmt_execute($stmt);
    $resultDurata = mysqli_stmt_get_result($stmt);
    $servizio = mysqli_fetch_assoc($resultDurata);

    // Inizializza un array per gli slot
    $slots = [];

    // Cicla sugli orari dei turni
    while ($row = mysqli_fetch_assoc($resultOrario)) {
        $orarioStart =  date("H:i", strtotime($row['ora_inizio']));
        $orarioEnd =    date("H:i", strtotime($row['ora_fine']));
        
        // Calcola gli slot da disponibilità in blocchi di durata servizio
        for ($i = 0; strtotime($orarioStart) <= strtotime($orarioEnd); $i++) {
            $durataStart = $servizio['durata'] * $i;
            $start = date("H:i", strtotime($orarioStart . " + $durataStart minutes"));
            $durataEnd = $durataStart + $servizio['durata'];
            $end = date("H:i", strtotime($start . " + $durataEnd minutes"));

            // Controlla se lo slot è già prenotato
            $query_check = "SELECT COUNT(*) FROM appuntamenti 
                            WHERE fk_turno = ? 
                            AND data_app = ? 
                            AND ora_inizio < ? 
                            AND ora_fine > ?";
            $stmt_check = mysqli_prepare($db_conn, $query_check);
            mysqli_stmt_bind_param($stmt_check, 'ssss', $row['id_turno'], $data, $end, $start);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_bind_result($stmt_check, $count);
            mysqli_stmt_fetch($stmt_check);

            // Aggiungi lo slot all'array se disponibile
            $available = ($count == 0);  // Se non ci sono conflitti, lo slot è disponibile
            $slots[] = [
                'start'     => $start,
                'end'       => $end,
                'available' => $available
            ];
        }
    }

    echo json_encode($slots);
}
?>
