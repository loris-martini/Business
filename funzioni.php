<?php

    function filtro_testo($text){
        return addslashes(filter_var(trim($text), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }        

    function checkPassword($result, $password){
        $user = mysqli_fetch_assoc($result);
        if (!$user) return false;
    
        $hashedPassword = $user['password'];
        if (!in_array($user['ruolo'], ['CLIENTE', 'BARBIERE', 'ADMIN'])) {
            return false;
        }
    
        if (password_verify($password, $hashedPassword)) {
            return $user;
        } else {
            return false;
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

    function getColorByState($state) {
        switch($state) {
            case 'IN_ATTESA': return 'red';
            case 'CONFERMATO': return 'yellow';
            case 'COMPLETATO': return 'green';
            case 'CANCELLATO': return '#70707086';
            default: return 'gray'; // Colore di default se lo stato non è riconosciuto
        }
    }

    function generaCodice($length) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $codice = '';
        for ($i = 0; $i < $length; $i++) {
            $codice .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $codice;
    }
?>