<?php
include 'connessione.php';
include 'funzioni.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['ruolo'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$message = '';

// Qui si gestiscono tutte le operazioni POST (creazione, modifica, assegnazione)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Esempio: Creazione salone
    if (isset($_POST['crea_salone'])) {
        $nome = filtro_testo($_POST['nome_salone']);
        $indirizzo = filtro_testo($_POST['indirizzo']);
        $sql = "INSERT INTO saloni (nome, indirizzo) VALUES (?, ?)";
        $stmt = mysqli_prepare($db_conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $nome, $indirizzo);
        mysqli_stmt_execute($stmt);
        $message = "Salone creato con successo.";
    }
    // Aggiungi altre operazioni qui (servizi, turni, assegnazioni...)
}

if (isset($_POST['modifica_salone'])) {
    $id = $_POST['id_salone_modifica'];
    $nome = filtro_testo($_POST['nome_salone_mod']);
    $indirizzo = filtro_testo($_POST['indirizzo_mod']);
    $sql = "UPDATE saloni SET nome = ?, indirizzo = ? WHERE id_salone = ?";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $nome, $indirizzo, $id);
    mysqli_stmt_execute($stmt);
    $message = "Salone modificato con successo.";
}

// Crea nuovo servizio
if (isset($_POST['crea_servizio'])) {
    $nome_servizio = filtro_testo($_POST['nome_servizio']);
    $durata = intval($_POST['durata']);
    $sql = "INSERT INTO servizi (nome, durata) VALUES (?, ?)";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $nome_servizio, $durata);
    mysqli_stmt_execute($stmt);
    $message = "Servizio creato con successo.";
}

if (isset($_POST['modifica_servizio'])) {
    $id = $_POST['id_servizio_modifica'];
    $nome = filtro_testo($_POST['nome_servizio_mod']);
    $durata = intval($_POST['durata_mod']);
    $sql = "UPDATE servizi SET nome = ?, durata = ? WHERE id_servizio = ?";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $nome, $durata, $id);
    mysqli_stmt_execute($stmt);
    $message = "Servizio modificato con successo.";
}

// Assegna barbiere a salone
if (isset($_POST['assegna_barbiere'])) {
    $barbiere = $_POST['barbiere'];
    $salone = intval($_POST['salone']);
    $sql = "INSERT INTO salone_barbiere (mail_barbiere, id_salone) VALUES (?, ?)";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $barbiere, $salone);
    mysqli_stmt_execute($stmt);
    $message = "Barbiere assegnato al salone con successo.";
}

// Assegna servizio a salone
if (isset($_POST['assegna_servizio'])) {
    $servizio = intval($_POST['servizio']);
    $salone = intval($_POST['salone']);
    $sql = "INSERT INTO salone_servizio (id_servizio, id_salone) VALUES (?, ?)";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $servizio, $salone);
    mysqli_stmt_execute($stmt);
    $message = "Servizio assegnato al salone con successo.";
}

// Associa servizio a barbiere
if (isset($_POST['associa_servizio_barbiere'])) {
    $servizio = intval($_POST['servizio']);
    $barbiere = $_POST['barbiere'];
    $sql = "INSERT INTO barbiere_servizio (id_servizio, mail_barbiere) VALUES (?, ?)";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $servizio, $barbiere);
    mysqli_stmt_execute($stmt);
    $message = "Servizio associato al barbiere con successo.";
}

// Gestione turni
if (isset($_POST['crea_turno'])) {
    $barbiere = $_POST['barbiere'];
    $giorno = $_POST['giorno'];
    $ora_inizio = $_POST['ora_inizio'];
    $ora_fine = $_POST['ora_fine'];
    $sql = "INSERT INTO turni_barbiere (mail_barbiere, giorno, ora_inizio, ora_fine) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $barbiere, $giorno, $ora_inizio, $ora_fine);
    mysqli_stmt_execute($stmt);
    $message = "Turno creato con successo.";
}

if (isset($_POST['modifica_turno'])) {
    $barbiere = $_POST['barbiere_turno'];
    $giorno = $_POST['giorno_turno'];
    $ora_inizio = $_POST['ora_inizio_mod'];
    $ora_fine = $_POST['ora_fine_mod'];
    $sql = "UPDATE turni SET ora_inizio = ?, ora_fine = ? WHERE barbiere = ? AND giorno = ?";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $ora_inizio, $ora_fine, $barbiere, $giorno);
    mysqli_stmt_execute($stmt);
    $message = "Turno modificato con successo.";
}


// Recupera dati utili per moduli
$barbieri = mysqli_query($db_conn, "SELECT mail, nome FROM utenti WHERE ruolo = 'BARBIERE'");
$saloni = mysqli_query($db_conn, "SELECT id_salone, nome FROM saloni");
$servizi = mysqli_query($db_conn, "SELECT id_servizio, nome FROM servizi");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pannello Admin</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <header><h1>Area Admin</h1></header>
    <p><?= $message ?></p>

    <!-- SALONE -->
    <section class="user-info">
        <h2>Crea nuovo salone</h2>
        <form method="POST">
            <input type="text" name="nome_salone" placeholder="Nome Salone" required>
            <input type="text" name="indirizzo" placeholder="Indirizzo" required>
            <button type="submit" name="crea_salone">Crea Salone</button>
        </form>
    </section>

    <section>
        <h2>Modifica Salone</h2>
        <form method="POST">
            <select name="id_salone_modifica">
                <?php mysqli_data_seek($saloni, 0); while ($s = mysqli_fetch_assoc($saloni)) echo "<option value='{$s['id_salone']}'>{$s['nome']}</option>"; ?>
            </select>
            <input type="text" name="nome_salone_mod" placeholder="Nuovo nome">
            <input type="text" name="indirizzo_mod" placeholder="Nuovo indirizzo">
            <button type="submit" name="modifica_salone">Modifica</button>
        </form>
    </section>


    <section>
        <h2>Crea nuovo servizio</h2>
        <form method="POST">
            <input type="text" name="nome_servizio" placeholder="Nome Servizio" required>
            <input type="number" name="durata" placeholder="Durata (minuti)" required>
            <button type="submit" name="crea_servizio">Crea Servizio</button>
        </form>
    </section>

    <section>
        <h2>Modifica Servizio</h2>
        <form method="POST">
            <select name="id_servizio_modifica">
                <?php mysqli_data_seek($servizi, 0); while ($srv = mysqli_fetch_assoc($servizi)) echo "<option value='{$srv['id_servizio']}'>{$srv['nome']}</option>"; ?>
            </select>
            <input type="text" name="nome_servizio_mod" placeholder="Nuovo nome">
            <input type="number" name="durata_mod" placeholder="Nuova durata (min)">
            <button type="submit" name="modifica_servizio">Modifica</button>
        </form>
    </section>


    <section>
        <h2>Assegna barbiere a salone</h2>
        <form method="POST">
            <select name="barbiere">
                <?php while ($b = mysqli_fetch_assoc($barbieri)) echo "<option value='{$b['mail']}'>{$b['nome']}</option>"; ?>
            </select>
            <select name="salone">
                <?php mysqli_data_seek($saloni, 0); while ($s = mysqli_fetch_assoc($saloni)) echo "<option value='{$s['id_salone']}'>{$s['nome']}</option>"; ?>
            </select>
            <button type="submit" name="assegna_barbiere">Assegna</button>
        </form>
    </section>

    <section>
        <h2>Assegna servizio a salone</h2>
        <form method="POST">
            <select name="servizio">
                <?php while ($srv = mysqli_fetch_assoc($servizi)) echo "<option value='{$srv['id_servizio']}'>{$srv['nome']}</option>"; ?>
            </select>
            <select name="salone">
                <?php mysqli_data_seek($saloni, 0); while ($s = mysqli_fetch_assoc($saloni)) echo "<option value='{$s['id_salone']}'>{$s['nome']}</option>"; ?>
            </select>
            <button type="submit" name="assegna_servizio">Assegna</button>
        </form>
    </section>

    <section>
        <h2>Associa servizi a barbiere</h2>
        <form method="POST">
            <select name="barbiere">
                <?php mysqli_data_seek($barbieri, 0); while ($b = mysqli_fetch_assoc($barbieri)) echo "<option value='{$b['mail']}'>{$b['nome']}</option>"; ?>
            </select>
            <select name="servizio">
                <?php mysqli_data_seek($servizi, 0); while ($srv = mysqli_fetch_assoc($servizi)) echo "<option value='{$srv['id_servizio']}'>{$srv['nome']}</option>"; ?>
            </select>
            <button type="submit" name="associa_servizio_barbiere">Associa</button>
        </form>
    </section>

    <section>
        <h2>Gestione Turni Barbieri</h2>
        <form method="POST">
            <select name="barbiere">
                <?php mysqli_data_seek($barbieri, 0); while ($b = mysqli_fetch_assoc($barbieri)) echo "<option value='{$b['mail']}'>{$b['nome']}</option>"; ?>
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
            <button type="submit" name="crea_turno">Crea Turno</button>
        </form>
    </section>

    <section>
        <h2>Modifica Turno Barbiere</h2>
        <form method="POST">
            <select name="barbiere_turno">
                <?php mysqli_data_seek($barbieri, 0); while ($b = mysqli_fetch_assoc($barbieri)) echo "<option value='{$b['mail']}'>{$b['nome']}</option>"; ?>
            </select>
            <select name="giorno_turno">
                <option value="Monday">Lunedì</option>
                <option value="Tuesday">Martedì</option>
                <option value="Wednesday">Mercoledì</option>
                <option value="Thursday">Giovedì</option>
                <option value="Friday">Venerdì</option>
                <option value="Saturday">Sabato</option>
                <option value="Sunday">Domenica</option>
            </select>
            <input type="time" name="ora_inizio_mod" required>
            <input type="time" name="ora_fine_mod" required>
            <button type="submit" name="modifica_turno">Modifica Turno</button>
        </form>
    </section>


    <!-- Altre sezioni di ricerca/modifica possono essere aggiunte dinamicamente tramite JS/AJAX -->
</body>
</html>
