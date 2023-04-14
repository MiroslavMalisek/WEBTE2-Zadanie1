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

    $sql = "SELECT * FROM game";
    $stmt = $db_connection->query($sql);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo $e->getMessage();
}

$error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $sql = "INSERT INTO standing(person_id, game_id, placing, discipline) VALUES (?, ?, ?, ?)";
    $stmt = $db_connection->prepare($sql);
    if ($stmt->execute([$_POST['person_id'], $_POST['game_id'], $_POST['standing'], $_POST['discipline']])){
        $error_msg.= "<p class='errMsg'>*Umiestnenie bolo pridané</p>";
    }else{
        $error_msg.= "<p class='errMsg'>*Umiestnenie sa nepodarilo pridať</p>";
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
    <title>Pridať umiestnenie</title>
</head>
<body>
<a href="user_zone.php">Návrat do privátnej zóny</a>
<br>
<h2>Pridať umiestnenie športovca</h2>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <select name="person_id">
        <?php
        foreach($persons as $person){
            echo '<option value="' . $person['id'] . '">' . $person['name'] . ' ' . $person['surname'] . '</option>';
        }
        ?>
    </select>
    <br>
    <select name="game_id">
        <?php
        foreach($games as $game){
            echo '<option value="' . $game['id'] . '">' . $game['type'] . ' ' . $game['year']  . ', ' . $game['city']  . ', ' . $game['country']. '</option>';
        }
        ?>
    </select>
    <br>
    <div class="mb-3">
        <label for="InputStanding" class="form-label">Umiestnenie:</label>
        <input type="number" name="standing" class="form-control" id="InputStanding" required>
    </div>
    <div class="mb-3">
        <label for="InputDiscipline" class="form-label">Disciplína:</label>
        <input type="text" name="discipline" class="form-control" id="InputDiscipline" required>
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
