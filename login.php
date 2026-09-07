<?php
// login.php
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Wypełnij wszystkie pola!";
    } else {
        // Pobieranie użytkownika z bazy po emailu
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Weryfikacja hasła
        if ($user && password_verify($password, $user['password'])) {
            // Logowanie udane - ustawiamy zmienne sesyjne oraz rolę
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Przekierowanie na stronę główną
            header("Location: index.php");
            exit;
        } else {
            $error = "Nieprawidłowy e-mail lub hasło!";
        }
    }
}

require_once 'header.php';
?>

<h2>Logowanie</h2>

<?php if($error): ?>
    <p style="color: red;"><?= $error ?></p>
<?php endif; ?>

<form method="POST" action="login.php" style="max-width: 400px;">
    <label>Adres e-mail:</label>
    <input type="email" name="email" required>
    
    <label>Hasło:</label>
    <input type="password" name="password" required>
    
    <button type="submit" class="btn">Zaloguj się</button>
</form>

<?php require_once 'footer.php'; ?>