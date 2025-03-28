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
    
            header("Location: $percorso");
            return "";
        }else{
            return "Email o password non valide.";
        }
    }
?>