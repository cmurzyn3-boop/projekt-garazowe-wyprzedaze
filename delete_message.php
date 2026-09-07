<?php
// delete_message.php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$msg_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

// Pobieramy wiadomość i sprawdzamy powiązane ogłoszenie
$stmt = $pdo->prepare("SELECT messages.*, items.user_id AS item_owner_id FROM messages JOIN items ON messages.item_id = items.id WHERE messages.id = ?");
$stmt->execute([$msg_id]);
$msg = $stmt->fetch();

if ($msg) {
    // Admin, autor wiadomości lub właściciel ogłoszenia może usunąć komentarz/wiadomość
    if ($user_role === 'admin' || $user_id == $msg['sender_id'] || $user_id == $msg['item_owner_id']) {
        $del = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $del->execute([$msg_id]);
    }
}

$item_id = $msg['item_id'] ?? '';
header("Location: item_details.php?id=" . $item_id);
exit;
?>