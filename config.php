<?php
// config.php
session_start(); // Uruchamiamy sesje na starcie

$host = 'localhost';
$dbname = 'garazowe_wyprzedaze';
$user = 'root'; // domyślny użytkownik w XAMPP
$pass = '';     // domyślne hasło w XAMPP jest puste

try {
    // Nawiązujemy połączenie
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Ustawiamy tryb zgłaszania błędów (ułatwi nam szukanie literówek)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą: " . $e->getMessage());
}
?>