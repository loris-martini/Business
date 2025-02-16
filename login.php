<?php

include 'connessione.php';
include 'funzioni.php';

session_start();
$_SESSION['logged'] = false;
$mail = $password = "";
$mailErr = $passwordErr = $message = "";
$dangerMail = $dangerPassword = "";
$isFormValid = true;

if (isset($_POST['submit']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = filtro_testo($_POST['mail']);
    $password = filtro_testo($_POST['password']);

    if(empty($mail)){
        $mailErr = "Mail obbligatoria";
        $dangerMail = 'error-box';
        $isFormValid = false;
    }
    if(empty($password)){
        $passwordErr = "Password obbligatoria";
        $dangerPassword = 'error-box';
        $isFormValid = false;
    }

    if ($isFormValid) {
    $mail =         @mysqli_real_escape_string($db_conn, strtolower(filtro_testo($_POST['mail'])));
    $password =     @mysqli_real_escape_string($db_conn, filtro_testo($_POST['password']));

    $query = "SELECT pass FROM taccount WHERE mail = ?";

    try{
        $stmt = mysqli_prepare($db_conn, $query);

        mysqli_stmt_bind_param($stmt, "s", $mail);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            $user = mysqli_fetch_assoc($result);
            $hashedPassword = $user['pass'];

            if(password_verify($password, $hashedPassword)){
                $_SESSION['logged'] = true;
                header("Location: index.php");
                exit();
            }else{
                $message = "Email o password non valide.";
            }
        }
    }catch(Exception $ex){
        $message = mysqli_error($db_conn);
    }
    }
}

if (isset($_POST['reset']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $password = "";
    $mailErr = $passwordErr = $message = "";
    $dangerMail = $dangerPassword = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style-reg.css">
    <link rel="stylesheet" href="./css/style.css">
    <title>Registrati</title>
</head>
<style>
    .error {
        color: #FF0000;
    }

    .error-box {
        border: 2px solid red;
        background-color: #ffe5e5;
        border-radius: 5px;
        }

</style>
<body>
    <header>
        <div class="logo">
            <h1>Login</h1>
        </div>
        <div id="menu"></div>
    </header>
    <center>
    <div id="form-registration">
    <form class="row g-3" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
        <table>
            <tr>
                <td><label class="form-label title">E-mail</label></td>
                <td>
                    <span class="error">* <?= $mailErr;?></span>
                    <input type="email" class="form-control <?= $dangerMail;?>" name="mail" value="<?=isset($_SESSION['mail']) ? $_SESSION['mail'] : ''?>" placeholder="Mail">
                </td>
            </tr>
            <tr>
                <td><label class="form-label title">Password</label></td>
                <td>
                    <span class="error">* <?= $passwordErr;?></span>
                    <input type="password" class="form-control <?= $dangerPassword;?>" name="password" placeholder="Password">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="text-danger"><?= $message; ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input class="btn" type="submit" name="reset" value="Cancella">
                    <input class="btn" type="submit" name="submit" value="Registrati">
                </td>
            </tr>
        </table>
    </form>
    </div>
    </center>
    <script src="script.js"></script>
</body>
</html>