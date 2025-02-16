console.log("DOM completamente caricato!");
const menuDiv = document.getElementById("menu");


menuDiv.innerHTML = `
    <nav>
        <ul>
            <li><a href="/business/index.php">Home</a></li>
            <li><a href="/business/registrazione.php">Registrati</a></li>
            <li><a href="/business/login.php">Login</a></li>
            <li><a href="#gallery">Galleria</a></li>
            <li><a href="#booking">Prenota Appuntamento</a></li>
            <li><a href="#contact">Contatti</a></li>
        </ul>
    </nav>
`;

