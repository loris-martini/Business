<?php
include 'connessione.php';
include 'funzioni.php';

if (!isset($_GET['barbiere']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$barbiere = mysqli_real_escape_string($db_conn, filtro_testo($_GET['barbiere']));
$date = mysqli_real_escape_string($db_conn, filtro_testo($_GET['date']));

$query = "SELECT ora_inizio FROM appuntamenti WHERE fk_barbiere = ? AND data_app = ?";
$stmt = mysqli_prepare($db_conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $barbiere, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$occupied_slots = [];
while ($row = mysqli_fetch_assoc($result)) {
    $occupied_slots[] = $row['ora_inizio'];
}

echo json_encode($occupied_slots);
?>
