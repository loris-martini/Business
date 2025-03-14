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

//APPUNTAMENTO  
document.getElementById("date").addEventListener("input", updateSlots);
document.getElementById("barbiere").addEventListener("change", updateSlots);

function updateSlots() {
    let date = document.getElementById("date").value;
    let barbiere = document.getElementById("barbiere").value;
    let slotsContainer = document.getElementById("slots");

    // Se la data è vuota, svuota il contenitore degli slot e interrompi la funzione
    if (!date) {
        slotsContainer.innerHTML = "";
        return;
    }

    if (!barbiere) return;

    fetch(`get_orari_disponibili.php?barbiere=${barbiere}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            slotsContainer.innerHTML = "";

            let startTime = 9 * 60; // 9:00 AM in minuti
            let endTime = 18 * 60; // 18:00 PM in minuti

            while (startTime < endTime) {
                let hour = Math.floor(startTime / 60);
                let minutes = startTime % 60;
                let time = `${hour.toString().padStart(2, "0")}:${minutes.toString().padStart(2, "0")}`;

                startTime += 30;
                let nextHour = Math.floor(startTime / 60);
                let nextMinutes = startTime % 60;
                let nextTime = `${nextHour.toString().padStart(2, "0")}:${nextMinutes.toString().padStart(2, "0")}`;

                let isAvailable = !data.includes(time);

                let button = document.createElement("button");
                button.type = "button";
                button.textContent = `${time} - ${nextTime}`;
                button.value = time;
                button.classList.add("slot");
                if (!isAvailable) {
                    button.disabled = true;
                    button.classList.add("unavailable");
                }

                button.addEventListener("click", function () {
                    document.querySelectorAll(".slot").forEach(b => b.classList.remove("selected"));
                    button.classList.add("selected");
                    document.getElementById("selected-time").value = time;
                });

                slotsContainer.appendChild(button);
            }
        })
        .catch(error => console.error("Errore nel recupero degli orari:", error));
}