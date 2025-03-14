<?php

    function filtro_testo($text){
        return addslashes(filter_var(trim($text), FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    }        

    function checkPassword($result, $password, $percorso){
        $user = mysqli_fetch_assoc($result);
        $hashedPassword = $user['password'];
    
        if(password_verify($password, $hashedPassword)){
            $_SESSION['user']['nome']       = $user['nome'];
            $_SESSION['user']['cognome']    = $user['cognome'];
            $_SESSION['user']['mail']       = $user['mail'];
    
            header("Location: $percorso");
            return "";
        }else{
            return "Email o password non valide.";
        }
    }
?>