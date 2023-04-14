<?php
session_start();

if ((!array_key_exists('logged_in',$_SESSION)) || !$_SESSION['logged_in']){
    header('Location: ../index.php');
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privátna zóna</title>
</head>
<body>
<div>
    <a href="../index.php">Návrat na hlavnú stránku</a>
    <br>
    <?php
        echo '<h3>Užívateľ ' . $_SESSION['name'] . " " .$_SESSION['surname'] .'</h3>';
    ?>
    <a href="addAthlete.php">Pridať nového športovca</a>
    <br>
    <a href="addStanding.php">Pridať nové umiestnenie športovca na OH</a>
    <br>
    <a href="updateAthlete.php">Zmeniť údaje športovca</a>
    <br>
    <a href="deleteAthlete.php">Vymazať športovca</a>
    <br>
    <a href="deleteStanding.php">Vymazať umiestnenie športovca</a>
    <br>
    <a href="history.php">História prihlásení</a>
</div>
</body>
</html>

