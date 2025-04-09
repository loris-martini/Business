<?php
include 'connessione.php';
include 'funzioni.php';

if (isset($_GET['barbiere']) && isset($_GET['data']) && isset($_GET['service'])) {
    $barbiere = mysqli_real_escape_string($db_conn, filtro_testo($_GET['barbiere']));
    $data = mysqli_real_escape_string($db_conn, filtro_testo($_GET['data']));
    $service = mysqli_real_escape_string($db_conn, filtro_testo($_GET['service']));

    $giorno = date('l', strtotime($data));

    // Recupera la durata del servizio
    $query = "SELECT durata FROM servizi WHERE id_servizio = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $service);
    mysqli_stmt_execute($stmt);
    $resultDurata = mysqli_stmt_get_result($stmt);
    $servizio = mysqli_fetch_assoc($resultDurata);
    $durataServizio = $servizio['durata']; // Durata in minuti

    // Recupera i turni del barbiere per il giorno selezionato
    $query = "SELECT id_turno, ora_inizio, ora_fine FROM turni_barbieri WHERE giorno = ? AND fk_barbiere = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $giorno, $barbiere);
    mysqli_stmt_execute($stmt);
    $resultOrario = mysqli_stmt_get_result($stmt);

    $slots = [];

    while ($row = mysqli_fetch_assoc($resultOrario)) {
        $orarioStart = strtotime($row['ora_inizio']);
        $orarioEnd = strtotime($row['ora_fine']);

        // Genera gli slot disponibili
        while ($orarioStart + ($durataServizio * 60) <= $orarioEnd) {
            $start = date("H:i", $orarioStart);
            $end = date("H:i", $orarioStart + ($durataServizio * 60));

            // Controlla se lo slot è disponibile
            $query_check = "SELECT COUNT(*) FROM appuntamenti 
                            WHERE fk_turno = ? 
                            AND data_app = ? 
                            AND (
                                (ora_inizio <= ? AND ADDTIME(ora_inizio, SEC_TO_TIME(? * 60)) > ?) OR 
                                (ora_inizio < ? AND ADDTIME(ora_inizio, SEC_TO_TIME(? * 60)) >= ?) OR
                                (ora_inizio >= ? AND ADDTIME(ora_inizio, SEC_TO_TIME(? * 60)) <= ?)
                            )";
            $stmt_check = mysqli_prepare($db_conn, $query_check);
            mysqli_stmt_bind_param($stmt_check, 'sssssssssss', 
                $row['id_turno'], $data, 
                $start, $durataServizio, $start, 
                $end, $durataServizio, $end, 
                $start, $durataServizio, $end
            );
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_bind_result($stmt_check, $count);
            mysqli_stmt_fetch($stmt_check);
            mysqli_stmt_close($stmt_check);

            $available = ($count == 0);

            $slots[] = [
                'start'     => $start,
                'end'       => $end,
                'available' => $available
            ];

            // Sposta l'orario di inizio avanti di un intervallo di durataServizio
            $orarioStart += $durataServizio * 60;
        }
    }

    echo json_encode($slots);
}
?>