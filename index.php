<?php
require __DIR__ . '/config/db.php';
session_start();

$pageTitle = 'Главная';

// Получаем Топ-8 популярных рецептов
$popularRecipes = db()->query('SELECT * FROM recipes ORDER BY views DESC LIMIT 8')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- Широкий баннер -->
<section class="hero-banner-wide">
    <!-- Плавающие иконки на фоне -->
    <i class="fa-solid fa-carrot floating-icon icon-1"></i>
    <i class="fa-solid fa-utensils floating-icon icon-2"></i>
    <i class="fa-solid fa-lemon floating-icon icon-3"></i>
    <i class="fa-solid fa-pizza-slice floating-icon icon-4"></i>

    <div class="hero-content">
        <h1>Вкусные идеи из того, <br>что под рукой</h1>
        <p>Превратите остатки продуктов в кулинарный шедевр. Просто укажите, что у вас есть, и найдите лучшие рецепты.</p>
        <a href="/ingredients.php" class="hero-btn-new">
            Подобрать по ингредиентам
        </a>
    </div>
</section>

<!-- Блок категорий -->
<section class="categories-section">
    <h2>Выбери категорию</h2>
    <div class="category-grid">
        <a href="/recipes.php?category=Завтраки" class="category-card" style="background: #fff0e6;">
            <i class="fa-solid fa-mug-hot" style="color: #f48c06;"></i>
            <span>Завтраки</span>
        </a>
        <a href="/recipes.php?category=Обеды" class="category-card" style="background: #ffe5d0;">
            <i class="fa-solid fa-bowl-food" style="color: #e63946;"></i>
            <span>Обеды</span>
        </a>
        <a href="/recipes.php?category=Салаты" class="category-card" style="background: #fce4d6;">
            <i class="fa-solid fa-leaf" style="color: #2a9d8f;"></i>
            <span>Салаты</span>
        </a>
        <a href="/recipes.php?category=Выпечка" class="category-card" style="background: #ffe5d0;">
            <i class="fa-solid fa-cake-candles" style="color: #e63946;"></i>
            <span>Выпечка</span>
        </a>
    </div>
</section>

<!-- Блок популярных рецептов -->
<section class="recipes-section">
    <h2><i class="fa-solid fa-fire" style="color: #e63946;"></i> Популярные рецепты</h2>
    <div class="recipe-grid grid-4">
        <?php foreach ($popularRecipes as $r): ?>
            <a href="/recipe.php?id=<?= $r['id'] ?>" class="recipe-card" style="text-decoration: none; color: inherit; display: flex;">
                
            
                <div class="recipe-image-placeholder" style="overflow: hidden; padding: 0;">
                    <?php if (!empty($r['image_url'])): ?>
                        <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                    <?php else: ?>
                        <i class="fa-solid fa-utensils"></i>
                    <?php endif; ?>
                </div>

                <!-- Кнопка лайка -->
                <div class="recipe-badges">
                    <?php 
                    
$isInCart = false;
if (isset($_SESSION['user_id'])) {
    $stmt = db()->prepare('SELECT 1 FROM user_favorites WHERE user_id = ? AND recipe_id = ?');
    $stmt->execute([$_SESSION['user_id'], $r['id']]);
    $isInCart = (bool)$stmt->fetch();
}
                    ?>
                    <?php if ($isInCart): ?>
                        <span class="badge-plus" style="color: #e63946; border: none; cursor: default;">
                            <i class="fa-solid fa-heart"></i>
                        </span>
                    <?php else: ?>
                        <button type="button" class="badge-plus add-to-cart-btn" 
                                data-id="<?= $r['id'] ?>" 
                                style="border: none; cursor: pointer; color: #ccc; background: #fff;">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="recipe-content">
                    <h3><?= htmlspecialchars($r['title']) ?></h3>
                    <div class="recipe-meta-bottom">
                        <span class="meta-time"><?= (int)$r['time_min'] ?> мин</span>
                        <span class="meta-rating">5.0 <i class="fa-solid fa-star"></i> <?= rand(1, 15) ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Стильная белая плашка -->
<section class="promo-white">
    <div class="promo-white-content">
        <h2>Все еще не знаете, что приготовить?</h2>
        <p>Добавьте продукты, которые есть у вас в холодильнике, и мы подберем идеальный рецепт! Превратите обычный ужин в кулинарный шедевр без лишних походов в магазин.</p>
        <a href="/ingredients.php" class="btn-promo-white">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Подобрать рецепт
        </a>
    </div>
    
    <div class="promo-white-image">
        <!-- Красивая иллюстрация из бесплатных иконок -->
        <div class="image-circle">
            <i class="fa-solid fa-bowl-food main-icon"></i>
            <i class="fa-solid fa-carrot float-icon-1"></i>
            <i class="fa-solid fa-pepper-hot float-icon-2"></i>
            <i class="fa-solid fa-lemon float-icon-3"></i>
            <i class="fa-solid fa-seedling float-icon-4"></i>
        </div>
    </div>
</section>
<!-- Блок статей -->
<section class="articles-section" style="margin-top: 60px;">
    <h2>Статьи <i class="fa-solid fa-newspaper" style="color: #e63946; font-size: 24px; margin-left: 8px;"></i></h2>
    <div class="articles-grid">
        <?php 
        // Получаем 4 последние статьи для главной
        $articles = db()->query('SELECT * FROM articles ORDER BY id DESC LIMIT 4')->fetchAll();
        foreach ($articles as $a): 
        ?>
            <a href="/article.php?id=<?= $a['id'] ?>" class="article-card">
                <div class="article-image">
                    <img src="<?= htmlspecialchars($a['image_url']) ?>" alt="<?= htmlspecialchars($a['title']) ?>">
                </div>
                <div class="article-info">
                    <h3><?= htmlspecialchars($a['title']) ?></h3>
                    <p><?= htmlspecialchars($a['description']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>