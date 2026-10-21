<?php
require __DIR__ . '/config/db.php';
session_start();

$pageTitle = 'Статьи о кулинарии';

// Получаем все статьи из базы
$articles = db()->query('SELECT * FROM articles ORDER BY id DESC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Статьи <i class="fa-solid fa-newspaper" style="color: #e63946; font-size: 28px; margin-left: 8px;"></i></h1>

<div class="articles-grid" style="margin-top: 40px; margin-bottom: 60px;">
    <?php foreach ($articles as $a): ?>
        <a href="/article.php?id=<?= $a['id'] ?>" class="article-card">
            <div class="article-image">
                <?php if (!empty($a['image_url'])): ?>
                    <img src="<?= htmlspecialchars($a['image_url']) ?>" alt="<?= htmlspecialchars($a['title']) ?>">
                <?php else: ?>
                    <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; color:#ccc; font-size:40px;">
                        <i class="fa-regular fa-newspaper"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="article-info">
                <h3><?= htmlspecialchars($a['title']) ?></h3>
                <p><?= htmlspecialchars($a['description']) ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>