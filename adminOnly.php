<?php
include 'connessione.php';
include 'funzioni.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['ruolo'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Esempio: Creazione salone
    if (isset($_POST['crea_salone'])) {
        $nome =             filtro_testo($_POST['nome_salone']);
        $indirizzo =        filtro_testo($_POST['indirizzo']);
        $posti =            filtro_testo($_POST['posti']);
        $orario_apertura =  filtro_testo($_POST['orario_apertura']);
        $orario_chiusura =  filtro_testo($_POST['orario_chiusura']);

        $sql = "INSERT INTO saloni (nome, indirizzo, posti, orario_apertura, orario_chiusura) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $nome, $indirizzo, $posti, $orario_apertura, $orario_chiusura);
        mysqli_stmt_execute($stmt);
        $message = "Salone creato con successo.";
    }

    if (isset($_POST['modifica_salone'])) {
        $id =               filtro_testo($_POST['id_salone_modifica']);
        $nome =             filtro_testo($_POST['nome_salone_mod']);
        $indirizzo =        filtro_testo($_POST['indirizzo_mod']);
        $posti =            filtro_testo($_POST['posti_mod']);
        $orario_apertura =  filtro_testo($_POST['orario_apertura_mod']);
        $orario_chiusura =  filtro_testo($_POST['orario_chiusura_mod']);

        $sql = "UPDATE saloni SET nome = ?, indirizzo = ?, posti = ?, orario_apertura = ?, orario_chiusura = ? WHERE id_salone = ?";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssiss", $nome, $indirizzo, $id, $orario_apertura, $orario_chiusura);
        mysqli_stmt_execute($stmt);
        $message = "Salone modificato con successo.";
    }

    // Crea nuovo servizio
    if (isset($_POST['crea_servizio'])) {
        $nome_servizio =    filtro_testo($_POST['nome_servizio']);
        $durata =           filtro_testo($_POST['durata']);
        $prezzo =           filtro_testo($_POST['prezzo']);

        $sql = "INSERT INTO servizi (nome, durata, prezzo) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "sid", $nome_servizio, $durata, $prezzo);
        mysqli_stmt_execute($stmt);
        $message = "Servizio creato con successo.";
    }

    if (isset($_POST['modifica_servizio'])) {
        $id = $_POST['id_servizio_modifica'];
        $nome_servizio =    filtro_testo($_POST['nome_servizio_mod']);
        $durata =           filtro_testo($_POST['durata_mod']);
        $prezzo =           filtro_testo($_POST['prezzo_mod']);

        $sql = "UPDATE servizi SET nome = ?, durata = ?, prezzo = ? WHERE id_servizio = ?";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "sidi", $nome, $durata, $prezzo, $id);
        mysqli_stmt_execute($stmt);
        $message = "Servizio modificato con successo.";
    }

    // Assegna barbiere a salone
    if (isset($_POST['assegna_barbiere'])) {
        $barbiere =     filtro_testo($_POST['barbiere']);
        $salone =       filtro_testo($_POST['salone']);

        $sql = "INSERT INTO salone_barbiere (fk_barbiere, id_salone) VALUES (?, ?)";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $barbiere, $salone);
        mysqli_stmt_execute($stmt);
        $message = "Barbiere assegnato al salone con successo.";
    }

    // Assegna servizio a salone
    if (isset($_POST['assegna_servizio'])) {
        $servizio =     filtro_testo($_POST['servizio']);
        $salone =       filtro_testo($_POST['salone']);

        $sql = "INSERT INTO salone_servizio (id_servizio, id_salone) VALUES (?, ?)";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $servizio, $salone);
        mysqli_stmt_execute($stmt);
        $message = "Servizio assegnato al salone con successo.";
    }

    // Associa servizio a barbiere
    if (isset($_POST['associa_servizio_barbiere'])) {
        $servizio =     filtro_testo($_POST['servizio']);
        $barbiere =     filtro_testo($_POST['barbiere']);

        $sql = "INSERT INTO barbiere_servizio (id_servizio, fk_barbiere) VALUES (?, ?)";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $servizio, $barbiere);
        mysqli_stmt_execute($stmt);
        $message = "Servizio associato al barbiere con successo.";
    }

    // Gestione turni
    if (isset($_POST['crea_turno'])) {
        $barbiere =     filtro_testo($_POST['barbiere']);
        $giorno =       filtro_testo($_POST['giorno']);
        $ora_inizio =   filtro_testo($_POST['ora_inizio']);
        $ora_fine =     filtro_testo($_POST['ora_fine']);
        $salone =       filtro_testo($_POST['salone']);

        $sql = "INSERT INTO turni_barbieri (fk_barbiere, fk_salone, giorno, ora_inizio, ora_fine) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "sisss", $barbiere, $salone, $giorno, $ora_inizio, $ora_fine);
        mysqli_stmt_execute($stmt);
        $message = "Turno creato con successo.";
    }

    if (isset($_POST['modifica_turno'])) {
        $id =           filtro_testo($_POST['id_turno']);
        $ora_inizio =   filtro_testo($_POST['ora_inizio_turno']);
        $ora_fine =     filtro_testo($_POST['ora_fine_turno']);

        $sql = "UPDATE turni_barbieri SET ora_inizio = ?, ora_fine = ? WHERE id_turno = ?";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $ora_inizio, $ora_fine, $id);
        mysqli_stmt_execute($stmt);
        $message = "Turno modificato con successo.";
    }
}

    $_SESSION['adminMessage'] = $message;


    // Recupera dati utili per moduli
    $barbieri =     mysqli_query($db_conn, "SELECT * FROM utenti WHERE ruolo = 'BARBIERE' ORDER BY nome");
    $saloni =       mysqli_query($db_conn, "SELECT * FROM saloni ORDER BY nome");
    $servizi =      mysqli_query($db_conn, "SELECT * FROM servizi ORDER BY nome");
    $turni =        mysqli_query($db_conn, "SELECT * FROM turni_barbieri t JOIN saloni s ON t.fk_salone = s.id_salone ORDER BY fk_barbiere, fk_salone, giorno");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pannello Admin</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style-index.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Area Admin</h1>
        </div>

        <div id="menu"></div>
    </header>
    <main>
        <center>
        <?php if(!empty($_SESSION['adminMessage'])){ ?>
            <section style="background-color: #f0f0f0; padding: 20px; border-radius: 10px; margin: 2%; width: 50%;">
                <h2 style="color: green;"><?=$_SESSION['adminMessage']?></h2>
            </section>
        <?php }; ?>

        <section class="user-info">
            <h2>Crea nuovo salone</h2>
            <form id="form-registration" method="POST">
                <input type="text" name="nome_salone" placeholder="Nome Salone" required>
                <input type="text" name="indirizzo" placeholder="Indirizzo" required>
                <input type="text" name="posti" placeholder="Posti" required>
                <input type="time" name="orario_apertura" placeholder="Apertura" required>
                <input type="time" name="orario_chiusura" placeholder="Chiusura" required>
                <input class=btn type="submit" name="crea_salone" value="Crea Salone">
            </form>
        </section>

        <section>
            <h2>Modifica Salone</h2>
            <form id="form-registration" method="POST">
                <select name="id_salone_modifica">
                    <?php mysqli_data_seek($saloni, 0); while ($s = mysqli_fetch_assoc($saloni)) echo "<option value='{$s['id_salone']}'>{$s['nome']}</option>"; ?>
                </select>
                <input type="text" name="nome_salone_mod" placeholder="Nome">
                <input type="text" name="indirizzo_mod" placeholder="Indirizzo">
                <input type="text" name="posti_mod" placeholder="Posti">
                <input type="time" name="orario_apertura_mod" placeholder="Apertura">
                <input type="time" name="orario_chiusura_mod" placeholder="Chiusura">
                <input class=btn type="submit" name="modifica_salone" value="Modifica">
            </form>
        </section>

        <section>
            <h2>Crea nuovo servizio</h2>
            <form id="form-registration" method="POST">
                <input type="text" name="nome_servizio" placeholder="Nome Servizio" required>
                <input type="number" name="durata" placeholder="Durata (minuti)" required>
                <input type="number" name="prezzo" step="0.01" min="0" max="1000" placeholder="Prezzo" required>
                <input class=btn type="submit" name="crea_servizio" value="Crea Servizio">
            </form>
        </section>

        <section>
            <h2>Modifica Servizio</h2>
            <form id="form-registration" method="POST">
                <select name="id_servizio_modifica">
                    <?php mysqli_data_seek($servizi, 0); while ($srv = mysqli_fetch_assoc($servizi)) echo "<option value='{$srv['id_servizio']}'>{$srv['nome']}</option>"; ?>
                </select>
                <input type="text" name="nome_servizio_mod" placeholder="Nome">
                <input type="number" name="durata_mod" placeholder="Durata (min)">
                <input type="number" name="prezzo_mod" step="0.01" min="0" max="1000" placeholder="Prezzo">
                <input class=btn type="submit" name="modifica_servizio" value="Modifica">
            </form>
        </section>

        <section>
            <h2>Assegna barbiere a salone</h2>
            <form id="form-registration" method="POST">
                <select name="barbiere">
                    <?php mysqli_data_seek($barbieri, 0);while ($b = mysqli_fetch_assoc($barbieri)) echo "<option value='{$b['mail']}'>{$b['nome']}</option>"; ?>
                </select>
                <select name="salone">
                    <?php mysqli_data_seek($saloni, 0); while ($s = mysqli_fetch_assoc($saloni)) echo "<option value='{$s['id_salone']}'>{$s['nome']}</option>"; ?>
                </select>
                <input class=btn type="submit" name="assegna_barbiere" value="Assegna barbiere-salone">
            </form>
        </section>

        <section>
            <h2>Assegna servizio a salone</h2>
            <form id="form-registration" method="POST">
                <select name="servizio">
                    <?php mysqli_data_seek($servizi, 0); while ($srv = mysqli_fetch_assoc($servizi)) echo "<option value='{$srv['id_servizio']}'>{$srv['nome']}</option>"; ?>
                </select>
                <select name="salone">
                    <?php mysqli_data_seek($saloni, 0); while ($s = mysqli_fetch_assoc($saloni)) echo "<option value='{$s['id_salone']}'>{$s['nome']}</option>"; ?>
                </select>
                <input class=btn type="submit" name="assegna_servizio" value="Assegna servizio-salone">
            </form>
        </section>

        <section>
            <h2>Associa servizi a barbiere</h2>
            <form id="form-registration" method="POST">
                <select name="barbiere">
                    <?php mysqli_data_seek($barbieri, 0); while ($b = mysqli_fetch_assoc($barbieri)) echo "<option value='{$b['mail']}'>{$b['nome']}</option>"; ?>
                </select>
                <select name="servizio">
                    <?php mysqli_data_seek($servizi, 0); while ($srv = mysqli_fetch_assoc($servizi)) echo "<option value='{$srv['id_servizio']}'>{$srv['nome']}</option>"; ?>
                </select>
                <input class=btn type="submit" name="associa_servizio_barbiere" value="Associa servizio-barbiere">
            </form>
        </section>

        <section>
            <h2>Gestione Turni Barbieri</h2>
            <form id="form-registration" method="POST">
                <select name="barbiere">
                    <?php mysqli_data_seek($barbieri, 0); while ($b = mysqli_fetch_assoc($barbieri)) echo "<option value='{$b['mail']}'>{$b['nome']}</option>"; ?>
                </select>
                <select name="salone">
                    <?php mysqli_data_seek($saloni, 0); while ($s = mysqli_fetch_assoc($saloni)) echo "<option value='{$s['id_salone']}'>{$s['nome']}</option>"; ?>
                </select>
                <select name="giorno">
                    <option value="Monday">Lunedì</option>
                    <option value="Tuesday">Martedì</option>
                    <option value="Wednesday">Mercoledì</option>
                    <option value="Thursday">Giovedì</option>
                    <option value="Friday">Venerdì</option>
                    <option value="Saturday">Sabato</option>
                    <option value="Sunday">Domenica</option>
                </select>
                <input type="time" name="ora_inizio" required>
                <input type="time" name="ora_fine" required>
                <input class=btn type="submit" name="crea_turno" value="Crea Turno">
            </form>
        </section>

        <section>
            <h2>Modifica Turno Barbiere</h2>
            <form id="form-registration" method="POST">
                <select name="id_turno">
                    <?php mysqli_data_seek($turni, 0); while ($t = mysqli_fetch_assoc($turni)) echo '<option value="' . $t['id_turno'] . '">' . $t['fk_barbiere'] . ' - ' . $t['nome'] . ' - ' . $t['giorno'] . '</option>'; ?>
                </select>
                <input type="time" name="ora_inizio_turno" required>
                <input type="time" name="ora_fine_turno" required>
                <input class=btn type="submit" name="modifica_turno" value="Modifica Turno">
            </form>
        </section>
        </center>
    </main>    
</body>
</html>
