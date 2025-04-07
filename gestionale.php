<?php
    include 'connessione.php';
    include 'funzioni.php';

    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    $mail = $_SESSION['user']['mail'];

    // Recupera i turni futuri del barbiere ordinati per giorno e ora
    $queryTurni = "SELECT * FROM turni_barbieri WHERE fk_barbiere = ? ORDER BY FIELD(giorno, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ora_inizio";
    $stmt = mysqli_prepare($db_conn, $queryTurni);
    mysqli_stmt_bind_param($stmt, "s", $mail);
    mysqli_stmt_execute($stmt);
    $resultTurni = mysqli_stmt_get_result($stmt);

    $turni = [];
    while ($row = mysqli_fetch_assoc($resultTurni)) {
        $turni[] = $row;
    }

    // Raggruppa i turni per giorno della settimana
    $giorni_turno = [];
    foreach ($turni as $turno) {
        $giorni_turno[$turno['giorno']][] = $turno;
    }

    // Mappa giorni settimana per date
    $giorniSettimana = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $oggi = strtotime('monday this week');
    $giorniDisponibili = [];

    $settimanaCorrente = 0;
    while (count($giorniDisponibili) < 5) {
        foreach ($giorniSettimana as $giorno) {
            if (isset($giorni_turno[$giorno])) {
                $data = strtotime("$giorno +$settimanaCorrente week", $oggi);
                $giorniDisponibili[] = [
                    'giorno_settimana' => $giorno,
                    'data' => date('Y-m-d', $data),
                    'turni' => $giorni_turno[$giorno]
                ];
                if (count($giorniDisponibili) == 5) break;
            }
        }
        $settimanaCorrente++;
    }

    // Recupera tutti gli appuntamenti futuri
    $queryApp = "SELECT a.*, s.nome AS nome_servizio, s.durata FROM appuntamenti a
                 JOIN turni_barbieri t ON a.fk_turno = t.id_turno
                 JOIN servizi s ON a.fk_servizio = s.id_servizio
                 WHERE t.fk_barbiere = ?";

    $stmt = mysqli_prepare($db_conn, $queryApp);
    mysqli_stmt_bind_param($stmt, "s", $mail);
    mysqli_stmt_execute($stmt);
    $resultApp = mysqli_stmt_get_result($stmt);

    $appuntamenti = [];
    while ($row = mysqli_fetch_assoc($resultApp)) {
        $appuntamenti[] = $row;
    }

    // Calcola l'intervallo orario globale per la colonna degli orari
    $global_ora_inizio = strtotime('23:59');
    $global_ora_fine = strtotime('00:00');

    foreach ($giorniDisponibili as $giorno) {
        $turni = $giorno['turni'];
        $inizio = strtotime(min(array_column($turni, 'ora_inizio')));
        $fine = strtotime(max(array_column($turni, 'ora_fine')));
        if ($inizio < $global_ora_inizio) $global_ora_inizio = $inizio;
        if ($fine > $global_ora_fine) $global_ora_fine = $fine;
    }

    $slot_count = ($global_ora_fine - $global_ora_inizio) / 3600;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Settimanale</title>
    <link rel="stylesheet" href="./css/style-gestionale.css">
</head>
<body>
    <h1>Agenda Settimanale</h1>

    <div class="calendar-container" id="calendarContainer">
        <!-- Colonna ore 
        <div class="hour-column">
            <?php
                // Calcolo del range di orari più esteso tra tutti i giorni
                $global_ora_inizio = strtotime('23:59');
                $global_ora_fine = strtotime('00:00');
                foreach ($giorniDisponibili as $giorno) {
                    $tmp_start = strtotime(min(array_column($giorno['turni'], 'ora_inizio')));
                    $tmp_end = strtotime(max(array_column($giorno['turni'], 'ora_fine')));
                    if ($tmp_start < $global_ora_inizio) $global_ora_inizio = $tmp_start;
                    if ($tmp_end > $global_ora_fine) $global_ora_fine = $tmp_end;
                }

                $slot_count = ($global_ora_fine - $global_ora_inizio) / 3600;
                for ($i = 0; $i < $slot_count; $i++): ?>
                    <div class="hour-block">
                        <?= date("H:i", $global_ora_inizio + $i * 3600) ?>
                    </div>
            <?php endfor; ?>
        </div> -->

        <!-- Colonne giorni -->
        <div class="calendar" style="grid-template-columns: 80px repeat(5, 1fr);">
           <!-- Colonna degli orari -->
            <div class="day-column">
                <div class="day-header">Orari</div>
                <div class="day-content">
                    <?php for ($i = 0; $i < $slot_count; $i++): ?>
                        <div class="slot">
                            <?= date("H:i", $global_ora_inizio + $i * 3600) ?> - <?= date("H:i", $global_ora_inizio + ($i+1) * 3600) ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Colonne per i 5 giorni con turni -->
            <?php foreach ($giorniDisponibili as $giorno): ?>
                <?php
                    $turni = $giorno['turni'];
                    $ora_inizio_giorno = strtotime(min(array_column($turni, 'ora_inizio')));
                    $ora_fine_giorno = strtotime(max(array_column($turni, 'ora_fine')));
                ?>
                <div class="day-column">
                    <div class="day-header">
                        <?= $giorno['giorno_settimana'] ?> <br>
                        <?= date("d/m", strtotime($giorno['data'])) ?>
                    </div>

                    <!-- Contenitore slot + appuntamenti -->
                    <div class="day-content">
                        <?php for ($i = 0; $i < $slot_count; $i++): ?>
                            <div class="slot">
                                <!-- Slot vuoto -->
                            </div>
                        <?php endfor; ?>

                        <!-- Appuntamenti -->
                        <?php foreach ($appuntamenti as $app): ?>
                            <?php if ($app['data_app'] == $giorno['data']): ?>
                                <?php

                                    $slot_height = 60;  // Altezza di un singolo slot (60px per 1 ora)
                                    // Ottieni l'ora di inizio del primo turno per il giorno
                                    $ora_inizio_turno = strtotime($giorno['turni'][0]['ora_inizio']); // Inizio del primo turno del giorno
                                    
                                    // Calcoliamo l'orario di inizio dell'appuntamento
                                    $startApp = strtotime($app['ora_inizio']);
                                    $offset_top_minutes = ($startApp - $global_ora_inizio) / 60; // in minuti
                                    $top = $offset_top_minutes * $slot_height / 60; // in px


                                    // Calcoliamo l'altezza in base alla durata dell'appuntamento
                                    $duration_min = $app['durata'];
                                    $height = ($duration_min / 60) * $slot_height; // in px


                                    // Limitiamo l'altezza per non sforare la fine della giornata
                                    $end_of_day = 24 * 60;  // Fine della giornata (1440 minuti)
                                    if ($offset_top_minutes + $height > $end_of_day) {
                                        $height = $end_of_day - $offset_top_minutes;  // Limita l'altezza alla fine della giornata
                                    }
                                ?>
                                <div class="appointment"
                                    data-minutes-from-start="<?= $offset_top_minutes ?>"
                                    data-duration="<?= $duration_min ?>">
                                    <?= htmlspecialchars($app['nome_servizio']) ?>
                                </div>


                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

<script>
    window.addEventListener('load', resizeSlots);
    window.addEventListener('resize', resizeSlots);

    function resizeSlots() {
        const container = document.getElementById('calendarContainer');
        const totalHeight = window.innerHeight - container.offsetTop - 40; // 40px di margine/padding/altro
        const slotCount = <?= $slot_count ?>;
        const slotHeight = totalHeight / slotCount;

        document.querySelectorAll('.slot').forEach(slot => {
            slot.style.height = `${slotHeight}px`;
        });

        document.querySelectorAll('.day-content').forEach(day => {
            day.style.height = `${slotHeight * slotCount}px`;
        });

        // Ridimensiona anche gli appuntamenti (assumendo 1 minuto = slotHeight / 60)
        const minuteToPixel = slotHeight / 60;
        document.querySelectorAll('.appointment').forEach(app => {
            const top = parseFloat(app.dataset.minutesFromStart); // lo passi da PHP
            const duration = parseFloat(app.dataset.duration);     // lo passi da PHP
            app.style.top = `${top * minuteToPixel}px`;
            app.style.height = `${duration * minuteToPixel}px`;
        });
    }
</script>