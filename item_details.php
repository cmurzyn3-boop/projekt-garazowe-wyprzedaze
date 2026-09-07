<?php
// item_details.php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$item_id = (int)$_GET['id'];
$message_status = '';

$stmt = $pdo->prepare("SELECT items.*, categories.name AS category_name, users.username 
                       FROM items 
                       JOIN categories ON items.category_id = categories.id
                       JOIN users ON items.user_id = users.id
                       WHERE items.id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("<h2>Nie znaleziono ogłoszenia.</h2>");
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id'];
$can_manage_item = $is_owner || $is_admin;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_text']) && isset($_SESSION['user_id'])) {
    $msg_text = trim($_POST['message_text']);
    if (!empty($msg_text)) {
        $stmt_msg = $pdo->prepare("INSERT INTO messages (item_id, sender_id, message) VALUES (?, ?, ?)");
        $stmt_msg->execute([$item_id, $_SESSION['user_id'], $msg_text]);
        $message_status = "Wiadomość została wysłana do sprzedającego!";
    }
}

$messages = [];
if (isset($_SESSION['user_id'])) {
    $msg_query = $pdo->prepare("SELECT messages.*, users.username 
                                FROM messages 
                                JOIN users ON messages.sender_id = users.id 
                                WHERE messages.item_id = ? 
                                ORDER BY messages.created_at DESC");
    $msg_query->execute([$item_id]);
    $all_messages = $msg_query->fetchAll();
    
    foreach ($all_messages as $msg) {
        if ($is_admin || $is_owner || $_SESSION['user_id'] == $msg['sender_id']) {
            $messages[] = $msg;
        }
    }
}

require_once 'header.php';
?>

<div class="details-container">
    <div class="details-header">
        <h2><?= htmlspecialchars($item['title']) ?></h2>
        
        <?php if($can_manage_item): ?>
            <div>
                <?php if($is_owner): ?>
                    <a href="edit_item.php?id=<?= $item['id'] ?>" class="btn btn-edit">Edytuj</a>
                <?php endif; ?>
                <a href="delete_item.php?id=<?= $item['id'] ?>" class="btn btn-delete" onclick="return confirm('Na pewno usunąć?');">Usuń</a>
            </div>
        <?php endif; ?>
    </div>
    
    <p class="item-meta">
        Kategoria: <a href="index.php?category=<?= $item['category_id'] ?>"><?= htmlspecialchars($item['category_name']) ?></a> | 
        Wystawił: <strong><?= htmlspecialchars($item['username']) ?></strong> | 
        Data: <?= $item['created_at'] ?>
    </p>
    
    <h3 class="item-price" style="font-size: 1.5em; margin-top: 15px;"><?= number_format($item['price'], 2, ',', ' ') ?> zł</h3>

    <?php if($item['image_path'] && file_exists($item['image_path'])): ?>
        <img src="<?= htmlspecialchars($item['image_path']) ?>" class="details-main-image">
    <?php endif; ?>

    <p class="details-text"><?= htmlspecialchars($item['description']) ?></p>
</div>

<div class="messages-section">
    <h3>Wiadomości w tej sprawie</h3>
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        <p>Musisz być <a href="login.php">zalogowany</a>, aby wysłać wiadomość.</p>
    <?php else: ?>
        
        <?php if($message_status): ?>
            <p class="alert-success"><?= $message_status ?></p>
        <?php endif; ?>

        <?php if(!$is_owner): ?>
            <form method="POST" action="item_details.php?id=<?= $item_id ?>" style="margin-bottom: 20px;">
                <textarea name="message_text" rows="3" placeholder="Napisz wiadomość..." required></textarea>
                <button type="submit" class="btn" style="margin-top: 10px;">Wyślij wiadomość</button>
            </form>
        <?php endif; ?>

        <?php if(count($messages) > 0): ?>
            <?php foreach($messages as $msg): ?>
                <div class="message-card" style="position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <p class="message-meta">
                            <strong><?= htmlspecialchars($msg['username']) ?></strong> napisał(a) (<?= $msg['created_at'] ?>):
                        </p>
                        <?php if($is_admin || $_SESSION['user_id'] == $msg['sender_id'] || $_SESSION['user_id'] == $item['user_id']): ?>
                            <a href="delete_message.php?id=<?= $msg['id'] ?>" onclick="return confirm('Usunąć tę wiadomość?');" style="color: #e74c3c; font-size: 0.85em; text-decoration: none; font-weight: bold;">[Usuń]</a>
                        <?php endif; ?>
                    </div>
                    <p class="message-content"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #777;">Brak wiadomości. Bądź pierwszy!</p>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>