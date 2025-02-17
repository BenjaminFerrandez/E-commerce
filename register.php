<?php
require_once 'config/database.php';
require_once 'functions.php';

$error = "test";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Register'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = htmlspecialchars($_POST['Username']);
    $password = password_hash($_POST['Password'], PASSWORD_BCRYPT);
    
    // Vérifier si l'utilisateur existe déjà
    $stmt = $db->prepare("SELECT Id FROM user WHERE Username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        $error = "Username already exists";
    } else {
        $stmt = $db->prepare("INSERT INTO user (Username, Password, Role, Solde) VALUES (?, ?, 'user', 0)");
        if ($stmt->execute([$username, $password])) {
            $_SESSION['Id'] = $db->lastInsertId();
            $_SESSION['Username'] = $username;
            $_SESSION['Role'] = 'user';
            header('Location: /index.php');
            exit();
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Login'])) {
    $error = "b";
    $database = new Database();
    $db = $database->getConnection();
    
    $username = htmlspecialchars($_POST['Username']);
    $password = password_hash($_POST['Password'], PASSWORD_BCRYPT);
    
    // Vérifier les identifiants
    $stmt = $db->prepare("SELECT Id, Username, Password, Role FROM user WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($user);
    echo $password;
    
    if ($user && password_verify($password, $user['Password'])) {
        // Connexion réussie, créer la session
        echo "aaaaaaaa";
        $_SESSION['Id'] = $user['Id'];
        $_SESSION['Username'] = $user['Username'];
        header('Location: index.php');
        exit();
    } else {
        echo "errroor";
        $error = "Invalid username";
    }
}
?>


<h2>Register</h2>

<form method="POST">
    <input type="text" name="Username" required placeholder="Username">
    <input type="password" name="Password" required placeholder="Password">
    <button type="submit" name="Register">Register</button>
</form>


<h2>Login</h2>

<p><?php echo $error ?></p>

    <form method="POST">
        <div>
            <input type="text" name="Username" required placeholder="Username">
        </div>
        <div>
            <input type="password" name="Password" required placeholder="Password">
        </div>
        <button type="submit" name="Login">Login</button>
    </form>