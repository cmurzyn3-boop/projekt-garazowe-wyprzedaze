<?php
// search_ajax.php
require_once 'config.php';

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT items.*, categories.name AS category_name, users.username 
        FROM items 
        JOIN categories ON items.category_id = categories.id
        JOIN users ON items.user_id = users.id
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (items.title LIKE ? OR items.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $sql .= " AND items.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY items.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

if(count($items) > 0) {
    foreach($items as $item) {
        $imgHtml = ($item['image_path'] && file_exists($item['image_path'])) 
            ? '<img src="' . htmlspecialchars($item['image_path']) . '" class="item-image">' 
            : '<div class="item-placeholder">Brak zdjęcia</div>';
            
        $shortDesc = htmlspecialchars(mb_strimwidth($item['description'], 0, 100, '...'));
        $priceFormatted = number_format($item['price'], 2, ',', ' ') . ' zł';
        
        echo '
        <div class="item-card">
            <div>' . $imgHtml . '</div>
            <div class="item-info">
                <h3 class="item-title">
                    <a href="item_details.php?id=' . $item['id'] . '">' . htmlspecialchars($item['title']) . '</a>
                </h3>
                <p class="item-desc">' . $shortDesc . '</p>
                <p class="item-price">' . $priceFormatted . '</p>
                <p class="item-meta">
                    Kategoria: <a href="index.php?category=' . $item['category_id'] . '">' . htmlspecialchars($item['category_name']) . '</a> 
                    | Wystawił: <strong>' . htmlspecialchars($item['username']) . '</strong>
                </p>
            </div>
        </div>';
    }
} else {
    echo '<p>Brak ogłoszeń spełniających podane kryteria.</p>';
}
?>