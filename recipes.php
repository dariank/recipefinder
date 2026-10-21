<?php
require __DIR__ . '/config/db.php';
session_start(); // Важно для работы сердечек!

// Получаем все параметры из URL
$searchQuery = trim($_GET['search'] ?? '');
$categoryQuery = trim($_GET['category'] ?? '');
$timeQuery = trim($_GET['time'] ?? '');
$difficultyQuery = trim($_GET['difficulty'] ?? '');
$caloriesQuery = trim($_GET['calories'] ?? '');

// Формируем заголовок
if ($searchQuery !== '') {
    $pageTitle = 'Поиск: ' . htmlspecialchars($searchQuery);
} elseif ($categoryQuery !== '') {
    $pageTitle = 'Категория: ' . htmlspecialchars($categoryQuery);
} else {
    $pageTitle = 'Все рецепты';
}

// Строим SQL
$sql = 'SELECT * FROM recipes';
$params = [];
$conditions = [];

if ($searchQuery !== '') {
    $conditions[] = '(title LIKE ? OR description LIKE ?)';
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if ($categoryQuery !== '') {
    $conditions[] = 'category = ?';
    $params[] = $categoryQuery;
}

if ($difficultyQuery !== '') {
    $conditions[] = 'difficulty = ?';
    $params[] = $difficultyQuery;
}

// Фильтр по времени
if ($timeQuery === 'fast') {
    $conditions[] = 'time_min <= 15';
} elseif ($timeQuery === 'medium') {
    $conditions[] = 'time_min > 15 AND time_min <= 45';
} elseif ($timeQuery === 'long') {
    $conditions[] = 'time_min > 45';
}

// Фильтр по калориям
if ($caloriesQuery === 'low') {
    $conditions[] = 'calories > 0 AND calories <= 200';
} elseif ($caloriesQuery === 'medium') {
    $conditions[] = 'calories > 200 AND calories <= 400';
} elseif ($caloriesQuery === 'high') {
    $conditions[] = 'calories > 400';
}

if (count($conditions) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY views DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$recipes = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title"><?= $pageTitle ?></h1>

<!-- Рабочие фильтры -->
<div class="filters-row">
    <form action="/recipes.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; width: 100%; align-items: center;">
        
        <?php if ($searchQuery !== ''): ?><input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>"><?php endif; ?>
        <?php if ($categoryQuery !== ''): ?><input type="hidden" name="category" value="<?= htmlspecialchars($categoryQuery) ?>"><?php endif; ?>

        <select name="time" class="filter-select" onchange="this.form.submit()">
            <option value="">Время готовки</option>
            <option value="fast" <?= $timeQuery === 'fast' ? 'selected' : '' ?>>До 15 минут</option>
            <option value="medium" <?= $timeQuery === 'medium' ? 'selected' : '' ?>>15–45 минут</option>
            <option value="long" <?= $timeQuery === 'long' ? 'selected' : '' ?>>Больше 45 минут</option>
        </select>

        <select name="difficulty" class="filter-select" onchange="this.form.submit()">
            <option value="">Сложность</option>
            <option value="easy" <?= $difficultyQuery === 'easy' ? 'selected' : '' ?>>Легко</option>
            <option value="medium" <?= $difficultyQuery === 'medium' ? 'selected' : '' ?>>Средне</option>
            <option value="hard" <?= $difficultyQuery === 'hard' ? 'selected' : '' ?>>Сложно</option>
        </select>

        <select name="calories" class="filter-select" onchange="this.form.submit()">
            <option value="">Калорийность</option>
            <option value="low" <?= $caloriesQuery === 'low' ? 'selected' : '' ?>>До 200 ккал</option>
            <option value="medium" <?= $caloriesQuery === 'medium' ? 'selected' : '' ?>>200–400 ккал</option>
            <option value="high" <?= $caloriesQuery === 'high' ? 'selected' : '' ?>>Более 400 ккал</option>
        </select>

        <?php if ($timeQuery || $difficultyQuery || $caloriesQuery || $categoryQuery || $searchQuery): ?>
            <a href="/recipes.php" class="filter-reset">✕ Сбросить</a>
        <?php endif; ?>
    </form>
</div>

<!-- Сетка рецептов -->
<section class="recipes-section">
    <?php if (count($recipes) > 0): ?>
        <div class="recipe-grid grid-4">
            <?php foreach ($recipes as $r): ?>
                <a href="/recipe.php?id=<?= $r['id'] ?>" class="recipe-card" style="text-decoration: none; color: inherit; display: flex;">
                    
                    <!-- ВЫВОД КАРТИНКИ -->
                    <div class="recipe-image-placeholder" style="overflow: hidden; padding: 0;">
                        <?php if (!empty($r['image_url'])): ?>
                            <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                        <?php else: ?>
                            <i class="fa-solid fa-utensils"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Кнопка лайка (ИСПРАВЛЕНО НА ПРОВЕРКУ ПОЛЬЗОВАТЕЛЯ) -->
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
    <?php else: ?>
        <p style="color: #8c7b75; font-size: 18px;">По вашему запросу ничего не найдено.</p>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.add-to-cart-btn');

    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); 

            const btn = this;
            const recipeId = btn.getAttribute('data-id');

            btn.style.color = '#e63946';
            btn.innerHTML = '<i class="fa-solid fa-heart"></i>';
            btn.disabled = true;

            fetch('/api/cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'recipe_id=' + recipeId + '&action=add'
            })
            .then(response => response.json())
            .catch(error => console.error('Ошибка:', error));
        });
    });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>