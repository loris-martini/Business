console.log("DOM completamente caricato!");
const menuDiv = document.getElementById("menu");


menuDiv.innerHTML = `
    <nav>
        <ul>
            <li><a href="/business/index.php">Home</a></li>
            <li><a href="/business/registrazione.php">Registrati</a></li>
            <li><a href="/business/login.php">Login</a></li>
        </ul>
    </nav>
`;

