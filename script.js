document.addEventListener("DOMContentLoaded", function () {
    // Menu di navigazione dinamico
    const menu = document.getElementById("menu");
    if (menu) {
        menu.innerHTML = `
            <nav>
                <ul>
                    <li><a href="/business/index.php">Home</a></li>
                    <li><a href="/business/registrazione.php">Registrati</a></li>
                    <li><a href="/business/login.php">Login</a></li>
                    <li><a href="/business/user.php">Area Utente</a></li>
                </ul>
            </nav>
        `;
    }

    // Funzioni di conferma
    function confirmLogout() {
        return confirm("Sei sicuro di volerti sloggare?");
    }

    function confirmDelete() {
        return confirm("Sei sicuro di voler cancellare i tuoi dati? Questa azione è irreversibile.");
    }

    function successAlert() {
        alert("Appuntamento prenotato con successo!");
    }

    // Elementi del form
    const serviceContainer = document.getElementById("service-container");
    const barbiereContainer = document.getElementById("barbiere-container");
    const dateTimeContainer = document.getElementById("date-time");
    const slotsContainer = document.getElementById("slots-container");

    // Nascondi inizialmente gli elementi
    if (serviceContainer) serviceContainer.style.display = "none";
    if (barbiereContainer) barbiereContainer.style.display = "none";
    if (dateTimeContainer) dateTimeContainer.style.display = "none";
    if (slotsContainer) slotsContainer.style.display = "none";

    // Elementi selettori
    const saloneSelect = document.getElementById("salone");
    const serviceSelect = document.getElementById("service");
    const barbiereSelect = document.getElementById("barbiere");
    const dateInput = document.getElementById("date");
    const slotsSelect = document.getElementById("slots");
    const selectedTimeInput = document.getElementById("selected-time");

    // Event listener per il cambio di salone
    if (saloneSelect) {
        saloneSelect.addEventListener("change", function () {
            const saloneId = this.value;

            if (saloneId) {
                serviceContainer.style.display = "table-row";

                fetch(`get_servizi.php?salone=${saloneId}`)
                    .then(response => response.json())
                    .then(data => {
                        serviceSelect.innerHTML = '<option value="">Seleziona un servizio</option>';
                        data.forEach(servizio => {
                            const option = document.createElement("option");
                            option.value = servizio.id_servizio;
                            option.textContent = servizio.nome;
                            serviceSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error("Errore nel caricamento dei servizi:", error));
            } else {
                serviceContainer.style.display = "none";
                barbiereContainer.style.display = "none";
                dateTimeContainer.style.display = "none";
                slotsContainer.style.display = "none";
            }
        });
    }

    // Event listener per il cambio di servizio
    if (serviceSelect) {
        serviceSelect.addEventListener("change", function () {
            const servizioId = this.value;

            if (servizioId) {
                barbiereContainer.style.display = "table-row";

                fetch(`get_barbieri.php?servizio=${servizioId}`)
                    .then(response => response.json())
                    .then(data => {
                        barbiereSelect.innerHTML = '<option value="">Seleziona un barbiere</option>';
                        data.forEach(barbiere => {
                            const option = document.createElement("option");
                            option.value = barbiere.mail;
                            option.textContent = `${barbiere.nome} ${barbiere.cognome}`;
                            barbiereSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        barbiereSelect.innerHTML = `<option value="">Errore nel caricamento dei barbieri ${error}</option>`;
                        console.log("Errore nel caricamento dei barbieri:", error)

                    });
            } else {
                barbiereContainer.style.display = "none";
                dateTimeContainer.style.display = "none";
                slotsContainer.style.display = "none";
            }
        });
    }

    // Event listener per il cambio di barbiere
    if (barbiereSelect) {
        barbiereSelect.addEventListener("change", function () {
            if (this.value) {
                dateTimeContainer.style.display = "table-row";
            } else {
                dateTimeContainer.style.display = "none";
                slotsContainer.style.display = "none";
            }
        });
    }

    // Event listener per il cambio di data
    if (dateInput) {
        dateInput.addEventListener("change", function () {
            const date = this.value;
            const barbiere = barbiereSelect.value;
            const service = serviceSelect.value;

            if (date && barbiere && service) {
                fetch(`get_orari_disponibili.php?barbiere=${barbiere}&data=${date}&service=${service}`)
                    .then(response => response.json())
                    .then(data => {
                        slotsSelect.innerHTML = '<option value="">Seleziona un orario</option>';

                        if (data.length === 0) {
                            slotsSelect.innerHTML = '<option value="">Nessun orario disponibile</option>';
                        } else {
                            data.forEach(slot => {
                                if (slot.available) {
                                    const option = document.createElement("option");
                                    option.value = `${slot.start}`;
                                    option.textContent = `${slot.start} - ${slot.end}`;
                                    slotsSelect.appendChild(option);
                                }
                            });
                        }

                        slotsContainer.style.display = "block";
                    })
                    .catch(error => {
                        console.error("Errore nel recupero degli orari:", error);
                        alert("Errore nel recupero degli orari. Riprova più tardi.");
                    });
            } else {
                slotsContainer.style.display = "none";
            }
        });
    }

    // Event listener per la selezione dello slot
    if (slotsSelect) {
        slotsSelect.addEventListener("change", function () {
            selectedTimeInput.value = this.value;
        });
    }
});
