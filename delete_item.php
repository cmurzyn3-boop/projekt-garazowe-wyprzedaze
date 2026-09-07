<?php
// delete_item.php
require_once 'config.php';

// Sprawdzenie, czy użytkownik jest zalogowany i czy podano ID ogłoszenia
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$item_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

// Pobieramy ogłoszenie z bazy
if ($user_role === 'admin') {
    // Admin może pobrać każde ogłoszenie do usunięcia
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$item_id]);
} else {
    // Zwykły użytkownik tylko swoje
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$item_id, $user_id]);
}

$item = $stmt->fetch();

if ($item) {
    // Jeśli ogłoszenie posiada zdjęcie, usuwamy plik z folderu uploads
    if (!empty($item['image_path']) && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }

    // Najpierw usuwamy powiązane wiadomości, żeby nie zepsuć kluczy obcych (foreign key)
    $del_msgs = $pdo->prepare("DELETE FROM messages WHERE item_id = ?");
    $del_msgs->execute([$item_id]);

    // Następnie usuwamy samo ogłoszenie
    $delete_stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $delete_stmt->execute([$item_id]);
}

header("Location: index.php");
exit;
?>