<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if ((!array_key_exists('logged_in',$_SESSION)) || !$_SESSION['logged_in']){
    header('Location: ../index.php');
}

require_once('../config.php');

try {
    $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM person";
    $stmt = $db_connection->query($sql);
    $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo $e->getMessage();
}
$id_athlete="";
if($_SERVER["REQUEST_METHOD"] == "GET"){
    $id_athlete = $_GET['person_id'];
    foreach ($persons as $person){
        if ($person['id'] == $id_athlete){
            $athlete_to_update = $person;
            break;
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $error_msg = "";
    $sql = "UPDATE person SET name = :name, surname = :surname, birth_day = :birth_day, birth_place = :birth_place, birth_country = :birth_country, death_day = :death_day, death_place = :death_place, death_country = :death_country WHERE id = :id";
    $stmt = $db_connection->prepare($sql);
    $stmt->bindParam(":name", $_POST['name'], PDO::PARAM_STR);
    $stmt->bindParam(":surname", $_POST['surname'], PDO::PARAM_STR);
    $stmt->bindParam(":birth_day", $_POST['birth_day'], PDO::PARAM_STR);
    $stmt->bindParam(":birth_place", $_POST['birth_place'], PDO::PARAM_STR);
    $stmt->bindParam(":birth_country", $_POST['birth_country'], PDO::PARAM_STR);
    $stmt->bindParam(":death_day", $_POST['death_day'], PDO::PARAM_STR);
    $stmt->bindParam(":death_place", $_POST['death_place'], PDO::PARAM_STR);
    $stmt->bindParam(":death_country", $_POST['death_country'], PDO::PARAM_STR);
    $stmt->bindParam(":id", $id_athlete, PDO::PARAM_STR);
    if ($stmt->execute()){
        $error_msg.= "<p class='errMsg'>*Športovec bol upravený</p>";
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
    <title>Zmeniť údaje</title>
</head>
<body>
<a href="user_zone.php">Návrat do privátnej zóny</a>
<br>
<h2>Zmeniť údaje športovca</h2>
<?php
if (isset($id_athlete)){
    $out = '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">
    <div class="mb-3">
        <label for="InputName" class="form-label">Name:</label>
        <input type="text" value="' . $athlete_to_update['name'] . '" name="name" class="form-control" id="InputName" required>
    </div>
    <div class="mb-3">
        <label for="InputSurname" class="form-label">Surname:</label>
        <input type="text" value="' . $athlete_to_update['surname'] . '" name="surname" class="form-control" id="InputSurname" required>
    </div>
    <div class="mb-3">
        <label for="InputDate" class="form-label">birth day:</label>
        <input type="date" value="' . $athlete_to_update['birth_day'] . '" name="birth_day" class="form-control" id="InputDate" required>
    </div>
    <div class="mb-3">
        <label for="InputbrPlace" class="form-label">birth place:</label>
        <input type="text" value="' . $athlete_to_update['birth_place'] . '" name="birth_place" class="form-control" id="InputBrPlace" required>
    </div>
    <div class="mb-3">
        <label for="InputBrCountry" class="form-label">birth country:</label>
        <input type="text" value="' . $athlete_to_update['birth_country'] . '" name="birth_country" class="form-control" id="InputBrCountry" required>
    </div>
    <div class="mb-3">
        <label for="InputDeathDate" class="form-label">death day:</label>
        <input type="date" value="' . $athlete_to_update['death_day'] . '" name="death_day" class="form-control" id="InputDeathDate">
    </div>
    <div class="mb-3">
        <label for="InputDtPlace" class="form-label">death place:</label>
        <input type="text" value="' . $athlete_to_update['death_place'] . '" name="death_place" class="form-control" id="InputDtPlace">
    </div>
    <div class="mb-3">
        <label for="InputDtCountry" class="form-label">death country:</label>
        <input type="text" value="' . $athlete_to_update['death_country'] . '" name="death_country" class="form-control" id="InputDtCountry">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>';
if (!empty($error_msg)) {
    $out.= $error_msg;
}
echo $out;
}else{
     $out = '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">
        <select name="person_id">';
            foreach($persons as $person){
                $out.='<option value="' . $person['id'] . '">' . $person['name'] . ' ' . $person['surname'] . '</option>';
            };
     $out.= '</select>
    <button type="submit" class="btn btn-primary">Submit</button>
    </form>';
     echo $out;
}
?>

</body>
</html>
