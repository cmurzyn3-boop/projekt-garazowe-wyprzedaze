<!-- header.php -->
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Garażowe Wyprzedaże</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div>
            <h2><a href="index.php" style="color: white; font-size: 1.2em;">📦 Garażowe Wyprzedaże</a></h2>
        </div>
        <nav>
            <a href="index.php">Strona główna</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="add_item.php">➕ Dodaj ogłoszenie</a>
                <a href="logout.php">Wyloguj (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
            <?php else: ?>
                <a href="login.php">Logowanie</a>
                <a href="register.php">Rejestracja</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">