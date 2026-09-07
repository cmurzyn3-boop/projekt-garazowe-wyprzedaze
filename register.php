<?php
// register.php
require_once 'config.php'; // Dołączenie połączenia z bazą

$error = '';
$success = '';

// Sprawdzamy czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Wypełnij wszystkie pola!";
    } else {
        // Szyfrowanie hasła
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Wstawianie do bazy
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            $success = "Rejestracja zakończona sukcesem! Możesz się <a href='login.php'>zalogować</a>.";
        } catch (PDOException $e) {
            $error = "Użytkownik o takim loginie lub emailu już istnieje!";
        }
    }
}

require_once 'header.php'; // Dołączenie nagłówka strony
?>

<h2>Rejestracja</h2>

<?php if($error): ?>
    <p style="color: red;"><?= $error ?></p>
<?php endif; ?>
<?php if($success): ?>
    <p style="color: green;"><?= $success ?></p>
<?php endif; ?>

<form method="POST" action="register.php" style="max-width: 400px;">
    <label>Nazwa użytkownika:</label>
    <input type="text" name="username" required>
    
    <label>Adres e-mail:</label>
    <input type="email" name="email" required>
    
    <label>Hasło:</label>
    <input type="password" name="password" required>
    
    <button type="submit" class="btn">Zarejestruj się</button>
</form>

<?php require_once 'footer.php'; ?>