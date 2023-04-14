<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $config_path = 'config.php';
    require_once($config_path);

    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "SELECT p.name, p.surname, p.birth_day, p.birth_place, p.birth_country, p.death_day, p.death_place, p.death_country
                FROM person p
                WHERE p.id = ?";
        $stmt_athlete = $db_connection->prepare($query);
        $stmt_athlete->execute([$_GET['id']]);
        $athlete =$stmt_athlete->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT g.type, g.year, g.city, g.country, s.placing, s.discipline
                FROM person p, game g, standing s 
                WHERE p.id = ? 
                AND s.person_id = p.id
                AND s.game_id = g.id
                ORDER BY year, s.placing;";
        $stmt_results = $db_connection->prepare($query);
        $stmt_results->execute([$_GET['id']]);
        $results =$stmt_results->fetchAll(PDO::FETCH_ASSOC);

    }catch (PDOException $e){
        echo "<script>console.log('Error connecting to database');</script>";
    }



?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php
    echo "<title>" . $athlete[0]['name'] . " " . $athlete[0]['surname'] . "</title>";
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="vendor/css/styleAthlete.css">
</head>
<body>
<button type="button" id="backButton" class="btn btn-outline-dark"><i id="backIcon" class="bi bi-arrow-left-circle"></i>Návrat na zoznam športovcov</button>
<div class="container ml-4 mp-4 mt-4">

    <?php
    $birth_date = new DateTimeImmutable($athlete[0]["birth_day"]);
    echo "<h1>" . $athlete[0]['name'] . " " . $athlete[0]['surname'] . "</h1>";
    if (is_null($athlete[0]['death_day'])){
        echo "<ul>
                <li>Datum narodenia: " .  $birth_date->format("d.m.Y") . "</li>
                <li>Miesto narodenia: " . $athlete[0]['birth_place'] . ", " . $athlete[0]['birth_country'] . "</li>
            </ul>";
    }else{
        $death_day = new DateTimeImmutable($athlete[0]["death_day"]);
        echo "<ul>
                <li>Datum narodenia: " .  $birth_date->format("d.m.Y") . "</li>
                <li>Miesto narodenia: " . $athlete[0]['birth_place'] . ", " . $athlete[0]['birth_country'] . "</li>
                <li>Dátum úmrtia: " . $death_day->format("d.m.Y") . "</li>
                <li>Miesto úmrtia: " . $athlete[0]['death_place'] . ", " . $athlete[0]['death_country'] . "</li>
            </ul>";
    }
    ?>

    <div class="container mp-4 mt-4">
        <h3>Umiestnenia na olympiádach</h3>
        <?php
        $year_prev = $results[0]['year'];
        echo "<h5>" . $year_prev . ":" . "</h5>";
        foreach ($results as $result){
            if ($result['year'] != $year_prev){
                $year_prev = $result['year'];
                echo "<h5>" . $year_prev . ":" . "</h5>";
            }
            echo "<p>" . $result['placing'] . ". " . "miesto " . $result['type'] . " ". $result['city'] . ", ". $result['country'] . " v disciplíne " . $result['discipline'] . "</p>";
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="vendor/js/scriptAthlete.js"></script>;
</body>
</html>
