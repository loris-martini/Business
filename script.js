document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('booking-form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        alert("Il tuo appuntamento è stato prenotato con successo!");
        form.reset();
    });
});
