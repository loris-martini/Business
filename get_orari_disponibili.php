<?php
include 'connessione.php';
include 'funzioni.php';

if (isset($_GET['barbiere']) && isset($_GET['data'])) {
    $barbiere = mysqli_real_escape_string($db_conn, $_GET['barbiere']);
    $data = mysqli_real_escape_string($db_conn, $_GET['data']);
    
    // Recupera gli orari disponibili per il barbiere nella data specificata
    $query = "SELECT o.ora_inizio, o.ora_fine, 
                     (CASE WHEN a.id IS NULL THEN 1 ELSE 0 END) AS available
              FROM orari_disponibili AS o
              LEFT JOIN appuntamenti AS a 
              ON o.ora_inizio = a.ora_inizio AND o.ora_fine = a.ora_fine AND a.fk_barbiere = ?
              WHERE o.data = ?";
    $stmt = mysqli_prepare($db_conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $barbiere, $data);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $slots = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $slots[] = [
            'time' => $row['ora_inizio'] . ' - ' . $row['ora_fine'],
            'available' => (bool)$row['available']
        ];
    }

    echo json_encode($slots);
}
?>
