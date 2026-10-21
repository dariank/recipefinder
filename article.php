<?php
require __DIR__ . '/config/db.php';
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare('SELECT * FROM articles WHERE id = ?');
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    die('Статья не найдена. <a href="/">На главную</a>');
}

$pageTitle = $article['title'];
require __DIR__ . '/includes/header.php';
?>

<article class="single-article">
    <div class="article-header-text">
        <h1 class="page-title"><?= htmlspecialchars($article['title']) ?></h1>
        <p class="article-desc"><?= htmlspecialchars($article['description']) ?></p>
        <div class="article-meta">
            <span><i class="fa-regular fa-calendar"></i> <?= date('d.m.Y', strtotime($article['created_at'])) ?></span>
        </div>
    </div>

    <?php if (!empty($article['image_url'])): ?>
        <div class="single-article-image">
            <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
        </div>
    <?php endif; ?>

    <div class="article-content">
       <?= $article['content'] ?>
    </div>
    
    <div style="margin-top: 40px;">
        <a href="/" class="btn-primary" style="text-decoration: none;">← Вернуться на главную</a>
    </div>
</article>

<style>
.single-article {
    max-width: 800px;
    margin: 0 auto;
    padding-top: 40px;
}
.article-header-text {
    text-align: center;
    margin-bottom: 32px;
}
.article-desc {
    font-size: 20px;
    color: #666;
    margin-bottom: 16px;
}
.article-meta {
    font-size: 14px;
    color: #999;
}
.single-article-image {
    width: 100%;
    height: 450px;
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.single-article-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.article-content {
    font-size: 18px;
    line-height: 1.8;
    color: #333;
}
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>