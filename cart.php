<?php

require_once 'config/database.php';
require_once 'functions.php';

$username = $_SESSION['Username'];



$database = new Database();
$db = $database->getConnection();
    
$username = $_SESSION['Username'];

$stmt = $db->prepare("SELECT Solde FROM user WHERE Username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo($username);
echo($user["Solde"]);

$stmt = $db->prepare("SELECT * FROM article");
$stmt->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>

    <h2>PANIER</h2>

    <div class="wrap">

        <?php
            var_dump($_SESSION["Cart"]);
            if (count($_SESSION["Cart"]) > 0) {
                foreach ($_SESSION["Cart"] as $article) {
                    echo $article["nom"];
                }
            }
        ?>

    </div>

    
    
</body>
</html>