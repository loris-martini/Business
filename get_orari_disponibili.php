<?php
include 'connessione.php';
include 'funzioni.php';

header('Content-Type: application/json');

if (!isset($_GET['barbiere']) || !isset($_GET['data']) || !isset($_GET['service'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}

$barbiere = mysqli_real_escape_string($db_conn, filtro_testo($_GET['barbiere']));
$data = mysqli_real_escape_string($db_conn, filtro_testo($_GET['data']));
$service = mysqli_real_escape_string($db_conn, filtro_testo($_GET['service']));

// Validazione della data
if (!strtotime($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data non valida']);
    exit;
}

$giorno = date('l', strtotime($data));

// Recupera la durata del servizio richiesto
$query = "SELECT durata FROM servizi WHERE id_servizio = ?";
$stmt = mysqli_prepare($db_conn, $query);
mysqli_stmt_bind_param($stmt, 's', $service);
mysqli_stmt_execute($stmt);
$resultDurata = mysqli_stmt_get_result($stmt);

if (!$resultDurata || mysqli_num_rows($resultDurata) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Servizio non trovato']);
    exit;
}

$servizio = mysqli_fetch_assoc($resultDurata);
$durataServizio = (int)$servizio['durata']; // Durata in minuti del servizio richiesto

// Recupera i turni del barbiere per il giorno selezionato
$query = "SELECT tb.id_turno, tb.ora_inizio, tb.ora_fine, s.posti, s.id_salone
          FROM turni_barbieri tb
          JOIN saloni s ON tb.fk_salone = s.id_salone
          WHERE tb.giorno = ? AND tb.fk_barbiere = ?";
$stmt = mysqli_prepare($db_conn, $query);
mysqli_stmt_bind_param($stmt, 'ss', $giorno, $barbiere);
mysqli_stmt_execute($stmt);
$resultOrario = mysqli_stmt_get_result($stmt);

$slots = [];

while ($row = mysqli_fetch_assoc($resultOrario)) {
    $id_turno = $row['id_turno'];
    $posti_salone = $row['posti'];
    $id_salone = $row['id_salone'];
    $orarioStart = strtotime($row['ora_inizio']);
    $orarioEnd = strtotime($row['ora_fine']);

    // Genera gli slot disponibili
    while ($orarioStart + ($durataServizio * 60) <= $orarioEnd) {
        $start = date("H:i", $orarioStart);
        $end = date("H:i", $orarioStart + ($durataServizio * 60));

        // Controllo 1: Il barbiere è già occupato in questo slot?
        $query_barbiere = "SELECT COUNT(*) 
                           FROM appuntamenti a
                           JOIN servizi s ON a.fk_servizio = s.id_servizio
                           WHERE a.fk_turno = ? 
                           AND a.data_app = ? 
                           AND (
                               (a.ora_inizio <= ? AND ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata * 60)) > ?) OR 
                               (a.ora_inizio < ? AND ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata * 60)) >= ?) OR
                               (a.ora_inizio >= ? AND a.ora_inizio < ?)
                           )";
        $stmt1 = mysqli_prepare($db_conn, $query_barbiere);
        mysqli_stmt_bind_param($stmt1, 'ssssssss', 
            $id_turno, $data, 
            $start, $start, 
            $end, $end, 
            $start, $end
        );
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_bind_result($stmt1, $barbiere_occupato);
        mysqli_stmt_fetch($stmt1);
        mysqli_stmt_close($stmt1);

        if ($barbiere_occupato > 0) {
            $slots[] = [
                'start' => $start,
                'end' => $end,
                'available' => false
            ];
            $orarioStart += $durataServizio * 60;
            continue;
        }

        // Controllo 2: Quanti appuntamenti ci sono nel salone in questo slot?
        $query_count = "SELECT COUNT(*) 
                        FROM appuntamenti a 
                        JOIN turni_barbieri tb ON a.fk_turno = tb.id_turno
                        JOIN servizi s ON a.fk_servizio = s.id_servizio
                        WHERE tb.fk_salone = ? 
                        AND a.data_app = ? 
                        AND (
                            (a.ora_inizio <= ? AND ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata * 60)) > ?) OR 
                            (a.ora_inizio < ? AND ADDTIME(a.ora_inizio, SEC_TO_TIME(s.durata * 60)) >= ?) OR
                            (a.ora_inizio >= ? AND a.ora_inizio < ?)
                        )";
        $stmt2 = mysqli_prepare($db_conn, $query_count);
        mysqli_stmt_bind_param($stmt2, 'ssssssss', 
            $id_salone, $data, 
            $start, $start, 
            $end, $end, 
            $start, $end
        );
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_bind_result($stmt2, $salone_occupato);
        mysqli_stmt_fetch($stmt2);
        mysqli_stmt_close($stmt2);

        $isAvailable = ($salone_occupato < $posti_salone);

        $slots[] = [
            'start' => $start,
            'end' => $end,
            'available' => $isAvailable
        ];

        // Incrementa l'orario di inizio
        $orarioStart += $durataServizio * 60;
    }
}

mysqli_close($db_conn);
echo json_encode($slots);
?>
