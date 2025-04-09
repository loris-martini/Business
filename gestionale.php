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
    <link rel="stylesheet" href="./css/style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Gestionale</h1>
        </div>
        <div id="menu"></div>
    </header>
    

    <h2>Agenda Settimanale</h2>

    <div class="calendar-container" id="calendarContainer">
        <!-- Colonne giorni -->
        <div class="calendar" style="grid-template-columns: 80px repeat(5, 1fr);">
           <!-- Colonna degli orari -->
            <div class="day-column">
                <div class="day-header">Orari</div>
                <div class="day-content">
                    <?php for ($i = 0; $i < $slot_count; $i++): ?>
                        <div class="slot"><?= date("H:i", $global_ora_inizio + $i * 3600) ?></div>
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
                                    $slot_height = 60;

                                    // Cambiato: uso l'inizio del turno del giorno, non globale
                                    $giorno_start = strtotime($giorno['turni'][0]['ora_inizio']);
                                    $startApp = strtotime($app['ora_inizio']);
                                    $offset_top_minutes = ($startApp - $giorno_start) / 60;
                                    $duration_min = $app['durata'];
                                ?>
                                <div class="slot-container">
                                    <div class="appointment"
                                        data-minutes-from-start="<?= $offset_top_minutes ?>"
                                        data-duration="<?= $duration_min ?>">
                                        <?= htmlspecialchars($app['nome_servizio']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <section id="contact">
        <h2>Contattaci</h2>
        <p>Email: info@tuosalone.com</p>
        <p>Telefono: +39 012 3456789</p>
    </section>
    
    <footer>
        <p>&copy; 2025 Il Tuo Salone di Parrucchiere</p>
    </footer>
</body>
</html>

<script>
    window.addEventListener('load', resizeSlots);
    window.addEventListener('resize', resizeSlots);

    function resizeSlots() {
        const container = document.getElementById('calendarContainer');
        const totalHeight = window.innerHeight - container.offsetTop - 40;
        const slotCount = <?= $slot_count ?>;
        const slotHeight = totalHeight / slotCount;

        document.querySelectorAll('.slot').forEach(slot => {
            slot.style.height = `${slotHeight}px`;
        });

        document.querySelectorAll('.day-content').forEach(day => {
            day.style.height = `${slotHeight * slotCount}px`;
        });

        const minuteToPixel = slotHeight / 60;
        document.querySelectorAll('.appointment').forEach(app => {
            const offset = parseFloat(app.dataset.minutesFromStart);
            const duration = parseFloat(app.dataset.duration);
            app.style.top = `${offset * minuteToPixel}px`;
            app.style.height = `${duration * minuteToPixel}px`;
        });
    }
</script>