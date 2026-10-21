<?php 
require __DIR__ . '/auth.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = ['title' => '', 'description' => '', 'content' => '', 'image_url' => ''];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM articles WHERE id = ?');
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if (!$article) die('Статья не найдена');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    if ($title && $content) {
        if ($id > 0) {
            // Обновление
            $stmt = db()->prepare('UPDATE articles SET title=?, description=?, content=?, image_url=? WHERE id=?');
            $stmt->execute([$title, $description, $content, $image_url, $id]);
            header('Location: /admin/articles.php?msg=Статья обновлена');
        } else {
            // Создание
            $stmt = db()->prepare('INSERT INTO articles (title, description, content, image_url) VALUES (?, ?, ?, ?)');
            $stmt->execute([$title, $description, $content, $image_url]);
            header('Location: /admin/articles.php?msg=Статья добавлена');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Редактировать' : 'Добавить' ?> статью | Админка</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #f4f4f4;">
    <header class="site-header" style="background: #111;"><div class="header-container"><a href="/admin/articles.php" class="logo" style="color:#fff;">← Назад к статьям</a></div></header>
    
    <div class="container" style="margin-top: 40px; max-width: 800px;">
        <div style="background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h1 class="page-title" style="margin-bottom: 24px;"><?= $id ? 'Редактировать' : 'Новая' ?> статья</h1>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Заголовок</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($article['title']) ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Краткое описание (для карточки)</label>
                    <input type="text" name="description" value="<?= htmlspecialchars($article['description']) ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Ссылка на картинку (например: /assets/img/my-image.jpg)</label>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($article['image_url']) ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Текст статьи (можно использовать HTML-теги: &lt;p&gt;, &lt;h3&gt;, &lt;ul&gt;, &lt;blockquote&gt;)</label>
                    <textarea name="content" rows="15" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px; font-family:monospace; line-height:1.5;"><?= htmlspecialchars($article['content']) ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer; padding: 15px;">Сохранить статью</button>
            </form>
        </div>
    </div>
</body>
</html>