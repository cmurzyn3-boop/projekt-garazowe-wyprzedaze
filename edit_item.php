<?php
// edit_item.php
require_once 'config.php';

// Zabezpieczenie - tylko dla zalogowanych
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$item_id = (int)$_GET['id'];
$error = '';
$success = '';

// Pobieranie ogłoszenia z bazy (sprawdzamy czy należy do użytkownika)
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->execute([$item_id, $_SESSION['user_id']]);
$item = $stmt->fetch();

if (!$item) {
    die("<h2>Brak dostępu lub ogłoszenie nie istnieje.</h2>");
}

// Pobranie kategorii
$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $image_path = $item['image_path']; // domyślnie zostaje stare zdjęcie

    // Obsługa nowo wgranego zdjęcia (opcjonalnie)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $destination = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Usuwamy stare zdjęcie, jeśli istniało
                if ($item['image_path'] && file_exists($item['image_path'])) {
                    unlink($item['image_path']);
                }
                $image_path = $destination;
            } else {
                $error = "Błąd podczas zapisywania nowego pliku.";
            }
        } else {
            $error = "Niedozwolony format pliku.";
        }
    }

    if (empty($title) || empty($description) || $price === '') {
        $error = "Wypełnij wymagane pola.";
    } elseif (empty($error)) {
        $update_stmt = $pdo->prepare("UPDATE items SET category_id = ?, title = ?, description = ?, price = ?, image_path = ? WHERE id = ?");
        $update_stmt->execute([$category_id, $title, $description, $price, $image_path, $item_id]);
        
        $success = "Ogłoszenie zostało zaktualizowane! <a href='item_details.php?id=$item_id'>Wróć do ogłoszenia</a>";
        
        // Odświeżenie danych w zmiennej $item
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
    }
}

require_once 'header.php';
?>

<h2>Edytuj ogłoszenie</h2>

<?php if($error): ?>
    <p class="alert-error"><?= $error ?></p>
<?php endif; ?>
<?php if($success): ?>
    <p class="alert-success"><?= $success ?></p>
<?php endif; ?>

<form method="POST" action="edit_item.php?id=<?= $item_id ?>" enctype="multipart/form-data" class="form-container">
    <label>Tytuł ogłoszenia:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>

    <label>Kategoria:</label>
    <select name="category_id" required>
        <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($item['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Opis przedmiotu:</label>
    <textarea name="description" rows="5" required><?= htmlspecialchars($item['description']) ?></textarea>

    <label>Cena (zł):</label>
    <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required>

    <label>Zdjęcie (zostaw puste, aby nie zmieniać):</label>
    <?php if($item['image_path']): ?>
        <p><img src="<?= htmlspecialchars($item['image_path']) ?>" style="width: 100px; border-radius: 4px;"></p>
    <?php endif; ?>
    <input type="file" name="image" accept="image/png, image/jpeg, image/webp">

    <button type="submit" class="btn" style="margin-top: 15px;">Zapisz zmiany</button>
</form>

<?php require_once 'footer.php'; ?>