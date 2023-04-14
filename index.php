<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$config_path = 'config.php';
require_once($config_path);

require_once ('oauth_api/vendor/autoload.php');

try {
    $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = "SELECT p.id, p.name, p.surname, g.year, g.city, g.country, g.type, s.discipline 
            FROM person p, game g, standing s 
            WHERE s.placing = 1 AND s.person_id = p.id AND g.id = s.game_id
            ORDER BY g.year";
    $stmt_all =$db_connection->prepare($query);
    $stmt_all->execute();
    $results_all =$stmt_all->fetchAll(PDO::FETCH_ASSOC);

    $query = 'select person.name, person.surname, COUNT(*) AS "pocet"
            FROM person, standing, game
            WHERE standing.person_id = person.id
                AND standing.game_id = game.id
                AND standing.placing = 1
            GROUP BY person.id
            ORDER BY pocet DESC, MAX(game.year) DESC
            LIMIT 10;';
    $stmt_best =$db_connection->prepare($query);
    $stmt_best->execute();
    $results_best =$stmt_best->fetchAll(PDO::FETCH_ASSOC);

}catch (PDOException $e){
    echo "<script>console.log('Error connecting to database');</script>";
}

//google auth
$client = new Google\Client();
$client->setAuthConfig('OAuth.json');
$redirect_uri = "https://site152.webte.fei.stuba.sk/zadanie1/oauth/redirect.php";
$client->setRedirectUri($redirect_uri);

// Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
$client->addScope("email");
$client->addScope("profile");

// Vytvorenie URL pre autentifikaciu na Google server - odkaz na Google prihlasenie.
$auth_url = $client->createAuthUrl();


function checkGmail($email) {
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function set_sessions($user_profile){
    $_SESSION['id_user']=$user_profile['id'];
    $_SESSION['name'] = $user_profile['name'];
    $_SESSION['surname'] = $user_profile['surname'];
    $_SESSION['email'] = $user_profile['email'];
    $_SESSION['login'] = $user_profile['login'];
    $_SESSION['type_login'] = 2;
    $_SESSION['fa_code'] = $user_profile['2fa_code'];
    header('Location: login/authenticator_code.php');
}

function checkEmailLogin($login, $password){
    $config_path = 'config.php';
    require($config_path);
    $error_msg = "";

    $param_login = trim($login);

    try {
        $db_connection = new PDO("mysql:host=$hostname;dbname=$dbname", $dbusername, $dbpassword);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM user_profile WHERE email = :email";
        $stmt = $db_connection->prepare($sql);
        $stmt->bindParam(":email", $param_login, PDO::PARAM_STR);
        $stmt->execute();
        $user_profile =$stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() == 1) {
            if (checkGmail($login)){
                if (!is_null($user_profile['2fa_code'])){
                    if (password_verify($password, $user_profile['password'])){
                        //je to gmail vytvoreny registraciou, prejdeme na 2FA
                        set_sessions($user_profile);
                    }else{
                        $error_msg.= "<p class='errMsg'>*Nesprávne heslo</p>";
                        return $error_msg;
                    }
                }else{
                    $error_msg.= "<p class='errMsg'>*Na daný účet sa treba prihlásiť cez OAuth2</p>";
                    return $error_msg;
                }
            }else{
                if (password_verify($password, $user_profile['password'])){
                    set_sessions($user_profile);
                }else{
                    $error_msg.= "<p class='errMsg'>*Nesprávne heslo</p>";
                    return $error_msg;
                }
            }
        }else{
            //moznost, ze bolo zadane prihlasenie cez login
            $sql = "SELECT * FROM user_profile WHERE login = :login";
            $stmt = $db_connection->prepare($sql);
            $stmt->bindParam(":login", $param_login, PDO::PARAM_STR);
            $stmt->execute();
            $user_profile =$stmt->fetch(PDO::FETCH_ASSOC);
            if ($stmt->rowCount() == 1) {
                if (checkGmail($user_profile['email'])){
                    if (!is_null($user_profile['2fa_code'])){
                        if (password_verify($password, $user_profile['password'])){
                            //je to gmail vytvoreny registraciou, prejdeme na 2FA
                            set_sessions($user_profile);
                        }else{
                            $error_msg.= "<p class='errMsg'>*Nesprávne heslo</p>";
                            return $error_msg;
                        }
                    }else{
                        $error_msg.= "<p class='errMsg'>*Na daný účet sa treba prihlásiť cez OAuth2</p>";
                        return $error_msg;
                    }
                }else{
                    if (password_verify($password, $user_profile['password'])){
                        set_sessions($user_profile);
                    }else{
                        $error_msg.= "<p class='errMsg'>*Nesprávne heslo</p>";
                        return $error_msg;
                    }
                }
            }else{
                $error_msg.= "<p class='errMsg'>*Zadaný login neexistuje</p>";
                return $error_msg;
            }
        }

    }catch (PDOException $e){
        echo "<script>console.log('Error connecting to database');</script>";
    }
    return $error_msg;
}

//login
if($_SERVER["REQUEST_METHOD"] == "POST") {
    //backend validácia
    $error_msg = "";
    $error_msg = checkEmailLogin($_POST['loginLogin'], $_POST['passwordLogin']);
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 1</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="vendor/css/styleTable.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark id="neubar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <H1>Zadanie č.1</H1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <?php
        if ($_SESSION['logged_in']) {
            $loggedName = $_SESSION['name'];
            $loggedSurname = $_SESSION['surname'];
            echo '<div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto" id="login">
                        <li class="nav-item">
                            <a title="Moja zóna" class="nav-link" href="private_zone/user_zone.php"><i class="bi bi-person-fill"></i>' . $loggedName . " " . $loggedSurname .
                            '</a>
                        </li>
                        <li class="nav-item">
                            <a title="Odhlásiť" class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i></a>
                        </li>
                    </ul>
                </div>';
        }else{
            $out = '<div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto" id="login">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-fill"></i>
                        Login
                    </a>
                    <ul id="login-dp" class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="row">
                                <div class="col-md-12">
                                    <form class="loginForm" onsubmit="return checkLoginForm()" role="form" method="post" accept-charset="UTF-8" id="login-nav">
                                        <div class="form-group">
                                            <label for="loginLogin">Login</label>
                                            <input type="text" class="form-control" name="loginLogin" id="loginLogin" placeholder="login alebo email">
                                        </div>
                                        <div class="form-group">
                                            <label for="passwordLogin">Heslo</label>
                                            <input type="password" class="form-control" name="passwordLogin" id="passwordLogin" placeholder="heslo">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" id="submitButtonLogin" class="btn btn-submit btn-primary btn-block">Prihlásiť sa</button>
                                        </div>
                                        <div id="errorLogin">
                                            <p>*Login je v nesprávnom tvare</p>
                                        </div>
                                        <div id="errorPassword">
                                            <p>*Heslo je v nesprávnom tvare</p>
                                        </div>';
            if (!empty($error_msg)){
                $out.= $error_msg;
            }
            $out.=                            '<div class="lineDiv">
                                            <hr class="line">
                                            <p>alebo</p>
                                            <hr class="line">
                                        </div>

                                        <div class="social-buttons">
                                            <a href="' . filter_var($auth_url, FILTER_SANITIZE_URL) . '" class="btn btn-google shadow p-2 mb-2 rounded"><i class="bi bi-google"></i>Sign in with Google</a>
                                        </div>
                                    </form>
                                </div>
                                <div class="bottom text-center">
                                    <a href="register_form.php"><b>Registrovať sa</b></a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
            </div>';
            echo $out;
        }
        ?>
        <!--<div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto" id="login">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-fill"></i>
                        Login
                    </a>
                    <ul id="login-dp" class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="row">
                                <div class="col-md-12">
                                    <form class="loginForm" onsubmit="return checkLoginForm()" role="form" method="post" accept-charset="UTF-8" id="login-nav">
                                        <div class="form-group">
                                            <label for="loginLogin">Login</label>
                                            <input type="text" class="form-control" id="loginLogin" placeholder="Login" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="passwordLogin">Heslo</label>
                                            <input type="password" class="form-control" id="passwordLogin" placeholder="Heslo" required>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" id="submitButtonLogin" class="btn btn-submit btn-primary btn-block">Prihlásiť sa</button>
                                        </div>
                                        <div id="errorLogin">
                                            <p>Nesprávny login</p>
                                        </div>
                                        <div class="lineDiv">
                                            <hr class="line">
                                            <p>alebo</p>
                                            <hr class="line">
                                        </div>

                                        <div class="social-buttons">

                                            <?php
                                        //    echo '<a href="' . filter_var($auth_url, FILTER_SANITIZE_URL) . '" class="btn btn-google shadow p-2 mb-2 rounded"><i class="bi bi-google"></i>Sign in with Google</a>';
                                            ?>

                                        </div>
                                    </form>
                                </div>
                                <div class="bottom text-center">
                                    <a href="#"><b>Registrovať sa</b></a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>-->
    </div>
</nav>
<!--<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid d-flex">
        <div>
            <a class="navbar-brand" href="#">Zadanie 1</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Link
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
-->

<div class="container ml-4 mp-4 mt-4 mb-4">
    <h2>Slovenskí olympijskí víťazi</h2>
    <table id="table" class="hover row-border" style="width:100%">
        <thead>
        <tr>
            <th>Meno</th>
            <th>Priezvisko</th>
            <th>Rok</th>
            <th>Miesto</th>
            <th>Typ olympiády</th>
            <th>Disciplína</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($results_all as $result){
            echo "<tr>
                        <td><a href='athlete_detail.php?id=" .  $result['id'] . "'>" . $result['name'] . "</a></td>
                        <td><a href='athlete_detail.php?id=" .  $result['id'] . "'>" . $result['surname'] . "</a></td>
                        <td>".$result['year']."</td>
                        <td>".$result['city'].", ".$result['country']."</td>
                        <td>".$result['type']."</td>
                        <td>".$result['discipline']."</td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="container ml-4 mp-4" id="divBest">
    <h2>10 najúspešnejších olympionikov</h2>
    <table id="tableBest" class="table row-border hover" style="width:100%">
        <thead>
        <tr>
            <th>Meno</th>
            <th>Priezvisko</th>
            <th>Počet zlatých medailí</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($results_best as $result){
            echo "<tr>
                        <td>".$result['name']."</td>
                        <td>".$result['surname']."</td>
                        <td>".$result['pocet']."</td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</div>



<script
        src="https://code.jquery.com/jquery-3.6.4.js"
        integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
        crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
<script src="vendor/js/scriptTable.js"></script>


</body>
</html>
