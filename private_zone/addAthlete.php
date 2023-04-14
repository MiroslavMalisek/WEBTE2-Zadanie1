<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if ((!array_key_exists('logged_in',$_SESSION)) || !$_SESSION['logged_in']){
    header('Location: ../index.php');
}

require_once('../config.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $error_msg = "";
    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM person WHERE name = :name AND surname = :surname";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":name", $_POST['name'], PDO::PARAM_STR);
        $stmt->bindParam(":surname", $_POST['surname'], PDO::PARAM_STR);
        $stmt->execute();
        $user_profile =$stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() == 1) {
            $error_msg.= "<p class='errMsg'>*Športovec s daným menom už existuje</p>";
        }else{
            $death_day = (empty($_POST['death_day'])) ? null : $_POST['death_day'];
            $death_place = (empty($_POST['death_place'])) ? null : $_POST['death_place'];
            $death_country = (empty($_POST['death_country'])) ? null : $_POST['death_country'];
            $sql = "INSERT INTO person(name, surname, birth_day, birth_place, birth_country, death_day, death_place, death_country) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db_connection->prepare($sql);
            if ($stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_day'], $_POST['birth_place'], $_POST['birth_country'], $death_day, $death_place, $death_country])){
                $error_msg.= "<p class='errMsg'>*Športovec bol vložený do databázy</p>";
            }
        }

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}



?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pridať športovca</title>
</head>
<body>
<a href="user_zone.php">Návrat do privátnej zóny</a>
<br>
<h2>Pridať nového športovca</h2>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <div class="mb-3">
        <label for="InputName" class="form-label">Name:</label>
        <input type="text" name="name" class="form-control" id="InputName" required>
    </div>
    <div class="mb-3">
        <label for="InputSurname" class="form-label">Surname:</label>
        <input type="text" name="surname" class="form-control" id="InputSurname" required>
    </div>
    <div class="mb-3">
        <label for="InputDate" class="form-label">birth day:</label>
        <input type="date" name="birth_day" class="form-control" id="InputDate" required>
    </div>
    <div class="mb-3">
        <label for="InputbrPlace" class="form-label">birth place:</label>
        <input type="text" name="birth_place" class="form-control" id="InputBrPlace" required>
    </div>
    <div class="mb-3">
        <label for="InputBrCountry" class="form-label">birth country:</label>
        <input type="text" name="birth_country" class="form-control" id="InputBrCountry" required>
    </div>
    <div class="mb-3">
        <label for="InputDeathDate" class="form-label">death day:</label>
        <input type="date" name="death_day" class="form-control" id="InputDeathDate">
    </div>
    <div class="mb-3">
        <label for="InputDtPlace" class="form-label">death place:</label>
        <input type="text" name="death_place" class="form-control" id="InputDtPlace">
    </div>
    <div class="mb-3">
        <label for="InputDtCountry" class="form-label">death country:</label>
        <input type="text" name="death_country" class="form-control" id="InputDtCountry">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
<?php
if (!empty($error_msg)) {
    echo $error_msg;
}
?>

</body>
</html>
