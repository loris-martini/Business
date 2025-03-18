document.getElementById("menu").innerHTML = `
<nav>
    <ul>
        <li><a href="/business/index.php">Home</a></li>
        <li><a href="/business/registrazione.php">Registrati</a></li>
        <li><a href="/business/login.php">Login</a></li>
        <li><a href="/business/user.php">Area Utente</a></li>
    </ul>
</nav>
`;

function confirmLogout() {
    return confirm("Sei sicuro di volerti sloggare?");
}

function confirmDelete() {
    return confirm("Sei sicuro di voler cancellare i tuoi dati? Questa azione è irreversibile.");
}

function successAlert() {
    return alert("Appuntamento prenotato con successo!");
}

// Inizialmente nascondiamo gli elementi
document.getElementById('service-container').style.display = 'none';
document.getElementById('barbiere-container').style.display = 'none';
document.getElementById('date-time').style.display = 'none';
document.getElementById('slots-container').style.display = 'none';

// Mostra il servizio quando il salone viene selezionato
document.getElementById('salone').addEventListener('change', function () {
    const saloneId = this.value;
    
    if (saloneId) {
        document.getElementById('service-container').style.display = 'table-row';
        // Carica i servizi per il salone selezionato
        fetch(`get_servizi.php?salone=${saloneId}`)
            .then(response => response.json())
            .then(data => {
                const servizioSelect = document.getElementById('service');
                servizioSelect.innerHTML = '<option value="">Seleziona un servizio</option>';
                data.forEach(servizio => {
                    const option = document.createElement('option');
                    option.value = servizio.id_servizio;
                    option.textContent = servizio.nome;
                    servizioSelect.appendChild(option);
                });
            });
    } else {
        document.getElementById('service-container').style.display = 'none';
        document.getElementById('barbiere-container').style.display = 'none';
        document.getElementById('date-time').style.display = 'none';
        document.getElementById('slots-container').style.display = 'none';
    }
});

// Mostra il barbiere quando il servizio viene selezionato
document.getElementById('service').addEventListener('change', function () {
    const servizioId = this.value;
    
    if (servizioId) {
        document.getElementById('barbiere-container').style.display = 'table-row';
        // Carica i barbieri per il servizio selezionato
        fetch(`get_barbieri.php?servizio=${servizioId}`)
            .then(response => response.json())
            .then(data => {
                const barbiereSelect = document.getElementById('barbiere');
                barbiereSelect.innerHTML = '<option value="">Seleziona un barbiere</option>';
                data.forEach(barbiere => {
                    const option = document.createElement('option');
                    option.value = barbiere.mail;
                    option.textContent = `${barbiere.nome} ${barbiere.cognome}`;
                    barbiereSelect.appendChild(option);
                });
            });
    } else {
        document.getElementById('barbiere-container').style.display = 'none';
        document.getElementById('date-time').style.display = 'none';
        document.getElementById('slots-container').style.display = 'none';
    }
});

// Mostra la sezione della data quando il barbiere viene selezionato
document.getElementById('barbiere').addEventListener('change', function () {
    const barbiere = this.value;

    if (barbiere) {
        document.getElementById('date-time').style.display = 'table-row';
    } else {
        document.getElementById('date-time').style.display = 'none';
        document.getElementById('slots-container').style.display = 'none';
    }
});

// Carica gli orari disponibili per la data e il barbiere
document.getElementById('date').addEventListener('change', function () {
    const date = this.value;
    const barbiere = document.getElementById('barbiere').value;

    if (date && barbiere) {
        fetch(`get_orari_disponibili.php?barbiere=${barbiere}&data=${date}`)
            .then(response => response.json())
            .then(data => {
                const slotsContainer = document.getElementById('slots');
                slotsContainer.innerHTML = ''; // Pulisce gli slot

                data.forEach(slot => {
                    const button = document.createElement('button');
                    button.classList.add(slot.available ? 'available' : 'unavailable');
                    button.textContent = slot.time;
                    button.disabled = !slot.available;
                    button.onclick = function () {
                        document.getElementById('selected-time').value = slot.time;
                    };

                    slotsContainer.appendChild(button);
                });

                document.getElementById('slots-container').style.display = 'block';
            });
    } else {
        document.getElementById('slots-container').style.display = 'none';
    }
});