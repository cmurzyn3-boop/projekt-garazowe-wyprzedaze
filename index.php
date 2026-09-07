<?php
// index.php
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

$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();

require_once 'header.php';
?>

<h2>Aktualne ogłoszenia</h2>

<form class="filters-form" onsubmit="return false;">
    <input type="text" id="search-input" name="search" placeholder="Szukaj na żywo (tytuł lub opis)..." value="<?= htmlspecialchars($search) ?>">
    <select id="category-select" name="category">
        <option value="">-- Wszystkie kategorie --</option>
        <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($category_filter == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<div id="results-container">
    <?php include 'search_ajax.php'; ?>
</div>

<?php require_once 'footer.php'; ?>