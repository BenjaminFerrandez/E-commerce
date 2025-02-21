<?php
    require_once 'config/database.php';
    require_once 'functions.php';

    $error = "test";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Register'])) {
        $database = new Database();
        $db = $database->getConnection();
        
        $username = htmlspecialchars($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        // Vérifier si l'utilisateur existe déjà
        $stmt = $db->prepare("SELECT Id FROM user WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Username already exists";
        } else {
            $stmt = $db->prepare("INSERT INTO user (username, password, role, solde) VALUES (?, ?, 'user', 0)");
            if ($stmt->execute([$username, $password])) {
                $_SESSION['id'] = $db->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';
                header('Location: /index.php');
                exit();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Login'])) {
        $error = "b";
        $database = new Database();
        $db = $database->getConnection();
        
        $username = htmlspecialchars($_POST['username']);
        $password = $_POST['password'];
        
        // Vérifier les identifiants
        $stmt = $db->prepare("SELECT id, username, password, role FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        print_r($user);
        echo $password;
        echo $user['password'];

        var_dump(password_verify($password, $user['password']));
        
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie, créer la session
            echo "aaaaaaaa";
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
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
    <input type="text" name="username" required placeholder="Username">
    <input type="password" name="password" required placeholder="Password">
    <button type="submit" name="Register">Register</button>
</form>

<h2>Login</h2>

<p><?php echo $error ?></p>
    <form method="POST">
        <div>
            <input type="text" name="username" required placeholder="Username">
        </div>
        <div>
            <input type="password" name="password" required placeholder="Password">
        </div>
        <button type="submit" name="Login">Login</button>
    </form>