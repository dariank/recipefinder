<?php
require __DIR__ . '/config/db.php';

$pageTitle = 'Подбор по ингредиентам';

// Получаем все продукты из базы (кроме базовых)
$sql = "SELECT * FROM products WHERE is_basic = 0 
        ORDER BY FIELD(category, 'Овощи', 'Фрукты', 'Мясо', 'Рыба', 'Молочные продукты', 'Крупы', 'Консервы', 'Соусы', 'Специи и приправы', 'Выпечка', 'Прочее'), name ASC";

$products = db()->query($sql)->fetchAll();


$selectedIds = $_GET['ingredients'] ?? [];

$recipes = [];
if (count($selectedIds) > 0) {
    // Формируем плейсхолдеры для безопасного запроса (?,?,?)
    $inQuery = implode(',', array_fill(0, count($selectedIds), '?'));
    
    // Ищем рецепты, у которых есть выбранные продукты.
    $sql = "
        SELECT r.*, COUNT(rp.product_id) as matched_ingredients
        FROM recipes r
        JOIN recipe_products rp ON r.id = rp.recipe_id
        WHERE rp.product_id IN ($inQuery)
        GROUP BY r.id
        ORDER BY matched_ingredients DESC, r.views DESC
    ";
    
    $stmt = db()->prepare($sql);
    $stmt->execute($selectedIds);
    $recipes = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Что у вас в холодильнике?</h1>
<p class="recipe-description">Отметьте продукты, которые у вас есть, и мы покажем, что из них можно приготовить.</p>

<form action="/ingredients.php" method="GET" class="ingredients-form">
    <div class="accordion-grid">
    <?php 
    // 1. Группируем продукты по категориям
    $groupedProducts = [];
    foreach ($products as $p) {
        $groupedProducts[$p['category']][] = $p;
    }

    // 2. Выводим карточки
    foreach ($groupedProducts as $categoryName => $items): 
        $catTitle = mb_convert_case(mb_strtolower($categoryName, 'UTF-8'), MB_CASE_TITLE, "UTF-8");

        $catTitle = str_replace(' И ', ' и ', $catTitle);
    ?>
        <div class="accordion-wrapper">
            <details class="accordion-item">
                <summary class="accordion-header">
                    <span><?= htmlspecialchars($catTitle) ?></span>
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>
                <div class="accordion-content">
                    <div class="ing-grid">
                        <?php foreach ($items as $p): ?>
                            <label class="ing-checkbox">
                                <input type="checkbox" name="ingredients[]" value="<?= $p['id'] ?>" 
                                    <?= in_array($p['id'], $selectedIds) ? 'checked' : '' ?>>
                                <span class="checkmark"></span>
                                <?= htmlspecialchars($p['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>
        </div>
    <?php endforeach; ?>
    </div>
    
    <div class="form-actions" style="margin-top: 30px;">
       <button type="submit" class="btn-primary" style="border: none; cursor: pointer; width: 100%; display: flex; justify-content: center; align-items: center; padding: 18px; font-size: 18px; font-weight: 700; border-radius: 12px;">
    Показать рецепты
</button>
        <?php if (count($selectedIds) > 0): ?>
            <a href="/ingredients.php" style="margin-left: 16px; color: #e63946; text-decoration: none; font-weight: 500;">Сбросить выбор</a>
        <?php endif; ?>
    </div>
</form>

<!-- Результаты поиска -->
<?php if (count($selectedIds) > 0): ?>
    <section class="recipes-section" style="margin-top: 48px;">
        <h2>Найдено рецептов: <?= count($recipes) ?></h2>
        
        <?php if (count($recipes) > 0): ?>
            <div class="recipe-grid grid-4">
                <?php foreach ($recipes as $r): ?>
                    <a href="/recipe.php?id=<?= $r['id'] ?>" class="recipe-card" style="text-decoration: none; color: inherit; display: flex;">
                        
                
                        <div class="recipe-image-placeholder" style="overflow: hidden; padding: 0;">
                            <?php if (!empty($r['image_url'])): ?>
                                <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                            <?php else: ?>
                                <i class="fa-solid fa-utensils"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Кнопка лайка (с проверкой пользователя) -->
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
                                <span class="meta-rating" style="color: #e63946; font-weight: 600;">
                                    <i class="fa-solid fa-check"></i> Совпало: <?= $r['matched_ingredients'] ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #8c7b75; font-size: 18px;">Из этих продуктов не нашлось ни одного рецепта. Попробуйте выбрать другие.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<script>
    // Этот скрипт делает так, чтобы одновременно была открыта только одна категория
    document.querySelectorAll('.accordion-item').forEach((targetDetail) => {
        targetDetail.addEventListener('click', () => {
            // Перебираем все остальные открытые карточки и закрываем их
            document.querySelectorAll('.accordion-item[open]').forEach((detail) => {
                if (detail !== targetDetail) {
                    detail.removeAttribute('open');
                }
            });
        });
    });
</script>
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
            }).catch(error => console.error('Ошибка:', error));
        });
    });
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>