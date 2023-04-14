<?php
session_start();


?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div>
    <?php
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    // Vypis relevantne info a uvitaciu spravu.
    echo '<h3>Vitaj ' . $_SESSION['name'] . '</h3>';
    echo '<p>Si prihlaseny ako: ' . $_SESSION['email'] . '</p>';

    }elseif (isset($_SESSION['qr_code']) && $_SESSION['qr_code']){
        echo '<h3>Vitaj ' . $_SESSION['name'] . '</h3>';
        echo '<p>Si prihlaseny ako: ' . $_SESSION['email'] . '</p>';
    }
    ?>
</div>
</body>
</html>
