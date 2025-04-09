<?php

    function filtro_testo($text){
        return addslashes(filter_var(trim($text), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }        

    function checkPassword($result, $password){
        $user = mysqli_fetch_assoc($result);
        $hashedPassword = $user['password'];
        if($user['ruolo'] == 'CLIENTE'){
            $percorso = 'index.php';
        }elseif($user['ruolo'] == 'BARBIERE'){
            $percorso = 'gestionale.php';
        }elseif($user['ruolo'] == 'ADMIN'){
            $percorso = 'login.php';
        }else{
            $percorso = 'login.php';
        }
    
        if(password_verify($password, $hashedPassword)){
            $_SESSION['user']['nome']       = $user['nome'];
            $_SESSION['user']['cognome']    = $user['cognome'];
            $_SESSION['user']['mail']       = $user['mail'];
            $_SESSION['user']['genere']     = $user['genere'];
            $_SESSION['user']['ruolo']      = $user['ruolo'];
    
            header("Location: $percorso");
            return "";
        }else{
            return "Email o password non valide.";
        }
    }

    function sendMail($subject, $message, $from, $to) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $from . "\r\n";
        $headers .= "Reply-To: " . $from . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
    
        if (mail($to, $subject, $message, $headers)) {
            return "Email inviata con successo!";
        } else {
            return "Errore nell'invio dell'email.";
        }
    }
    
?>