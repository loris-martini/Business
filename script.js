document.addEventListener("DOMContentLoaded", function () {
    // Elementi DOM
    const elements = {
        menu: document.getElementById("menu"),
        serviceContainer: document.getElementById("service-container"),
        barbiereContainer: document.getElementById("barbiere-container"),
        dateTimeContainer: document.getElementById("date-time"),
        slotsContainer: document.getElementById("slots-container"),
        saloneSelect: document.getElementById("salone"),
        serviceSelect: document.getElementById("service"),
        barbiereSelect: document.getElementById("barbiere"),
        dateInput: document.getElementById("date"),
        slotsSelect: document.getElementById("slots"),
        selectedTimeInput: document.getElementById("selected-time"),
        prezzoContainer: document.getElementById("prezzo-container")
    };

    // Funzioni di utilità
    const setDisplay = (element, display) => {
        if (element) element.style.display = display;
    };

    const resetSelect = (select, defaultOption) => {
        if (select) select.innerHTML = `<option value="">${defaultOption}</option>`;
    };

    const fetchData = async (url) => {
        try {
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error(`Errore nel caricamento dei dati: ${error}`);
            throw error;
        }
    };

    const showError = (select, message) => {
        if (select) select.innerHTML = `<option value="">${message}</option>`;
    };

    const confirmAction = (message) => confirm(message);

    // Inizializzazione menu dinamico
    if (elements.menu) {
        elements.menu.innerHTML = `
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

    // Nascondi inizialmente i contenitori
    setDisplay(elements.serviceContainer, "none");
    setDisplay(elements.barbiereContainer, "none");
    setDisplay(elements.dateTimeContainer, "none");
    setDisplay(elements.slotsContainer, "none");

    // Funzione per caricare i servizi
    const loadServizi = async (saloneId) => {
        resetSelect(elements.serviceSelect, "Seleziona un servizio");
        const serviziMappa = {};
        try {
            const data = await fetchData(`get_servizi.php?salone=${saloneId}`);
            data.forEach(servizio => {
                const option = document.createElement("option");
                option.value = servizio.id_servizio;
                option.textContent = servizio.nome;
                elements.serviceSelect.appendChild(option);
                serviziMappa[servizio.id_servizio] = servizio.prezzo;
            });

            // Aggiorna il prezzo quando cambia il servizio
            elements.serviceSelect.addEventListener("change", () => {
                const selectedServiceId = elements.serviceSelect.value;
                elements.prezzoContainer.innerHTML = "";
                if (selectedServiceId && serviziMappa[selectedServiceId]) {
                    const prezzo = document.createElement("h3");
                    prezzo.innerText = `Prezzo: €${serviziMappa[selectedServiceId]}`;
                    elements.prezzoContainer.appendChild(prezzo);
                }
            });
        } catch {
            showError(elements.serviceSelect, "Errore nel caricamento dei servizi");
        }
    };

    // Funzione per caricare i barbieri
    const loadBarbieri = async (servizioId) => {
        resetSelect(elements.barbiereSelect, "Seleziona un barbiere");
        try {
            const data = await fetchData(`get_barbieri.php?servizio=${servizioId}`);
            data.forEach(barbiere => {
                const option = document.createElement("option");
                option.value = barbiere.mail;
                option.textContent = `${barbiere.nome} ${barbiere.cognome}`;
                elements.barbiereSelect.appendChild(option);
            });
        } catch (error) {
            showError(elements.barbiereSelect, `Errore nel caricamento dei barbieri: ${error}`);
        }
    };

    // Funzione per caricare gli slot disponibili
    const loadSlots = async (barbiere, date, service) => {
        resetSelect(elements.slotsSelect, "Seleziona un orario");
        try {
            const data = await fetchData(`get_orari_disponibili.php?barbiere=${barbiere}&data=${date}&service=${service}`);
            if (data.length === 0) {
                elements.slotsSelect.innerHTML = '<option value="">Nessun orario disponibile</option>';
            } else {
                data.forEach(slot => {
                    if (slot.available) {
                        const option = document.createElement("option");
                        option.value = slot.start;
                        option.textContent = `${slot.start} - ${slot.end}`;
                        elements.slotsSelect.appendChild(option);
                    }
                });
            }
            setDisplay(elements.slotsContainer, "block");
        } catch {
            alert("Errore nel recupero degli orari. Riprova più tardi.");
        }
    };

    // Event listener per il salone
    if (elements.saloneSelect) {
        elements.saloneSelect.addEventListener("change", async () => {
            const saloneId = elements.saloneSelect.value;
            if (saloneId) {
                setDisplay(elements.serviceContainer, "table-row");
                await loadServizi(saloneId);
            } else {
                setDisplay(elements.serviceContainer, "none");
                setDisplay(elements.barbiereContainer, "none");
                setDisplay(elements.dateTimeContainer, "none");
                setDisplay(elements.slotsContainer, "none");
            }
        });
    }

    // Event listener per il servizio
    if (elements.serviceSelect) {
        elements.serviceSelect.addEventListener("change", async () => {
            const servizioId = elements.serviceSelect.value;
            if (servizioId) {
                setDisplay(elements.barbiereContainer, "table-row");
                resetSelect(elements.slotsSelect, "Seleziona un orario");
                setDisplay(elements.slotsContainer, "none");
                await loadBarbieri(servizioId);

                // Ricalcola gli slot se barbiere e data sono già selezionati
                const barbiere = elements.barbiereSelect.value;
                const date = elements.dateInput.value;
                if (barbiere && date) {
                    await loadSlots(barbiere, date, servizioId);
                }
            } else {
                setDisplay(elements.barbiereContainer, "none");
                setDisplay(elements.dateTimeContainer, "none");
                setDisplay(elements.slotsContainer, "none");
            }
        });
    }

    // Event listener per il barbiere
    if (elements.barbiereSelect) {
        elements.barbiereSelect.addEventListener("change", () => {
            setDisplay(elements.dateTimeContainer, elements.barbiereSelect.value ? "table-row" : "none");
            setDisplay(elements.slotsContainer, "none");
        });
    }

    // Event listener per la data
    if (elements.dateInput) {
        elements.dateInput.addEventListener("change", async () => {
            const { dateInput, barbiereSelect, serviceSelect } = elements;
            const date = dateInput.value;
            const barbiere = barbiereSelect.value;
            const service = serviceSelect.value;

            if (date && barbiere && service) {
                await loadSlots(barbiere, date, service);
            } else {
                setDisplay(elements.slotsContainer, "none");
            }
        });
    }

    // Event listener per gli slot
    if (elements.slotsSelect) {
        elements.slotsSelect.addEventListener("change", () => {
            elements.selectedTimeInput.value = elements.slotsSelect.value;
        });
    }

    // Funzioni di conferma
    window.confirmLogout = () => confirmAction("Sei sicuro di volerti sloggare?");
    window.confirmDelete = () => confirmAction("Sei sicuro di voler cancellare i tuoi dati? Questa azione è irreversibile.");
    window.successAlert = () => alert("Appuntamento prenotato con successo!");
});