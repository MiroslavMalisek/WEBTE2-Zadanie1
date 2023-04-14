<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authenticator QR kód</title>
    <link rel="stylesheet" href="vendor/css/styleRegistrationQr.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

</head>
<body>
<?php
if (array_key_exists('qr_code',$_SESSION)){
    echo '<div class="formDiv shadow p-2 mb-2 rounded">
    <div class="title">Naskenujte QR kód do aplikácie Authenticator:</div>
    <form class="form" action="login/authenticator_code.php">
        <div class="code">
            <div class="code_box">
                <img src="'.$_SESSION['qr_code'].'" alt="QR kód">    
            </div>
        </div>
        <div class="button">
            <input type="submit" value="Pokračovať na zadanie kódu a prihlásenie">
        </div>
    </form>
</div>';
}else{
    echo '<p>Access denied</p>';
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

</body>
</html>
