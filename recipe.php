<?php
require __DIR__ . '/config/db.php';
session_start(); // ОЧЕНЬ ВАЖНО для работы избранного!

// Получаем ID рецепта из URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Достаем сам рецепт
$stmt = db()->prepare('SELECT * FROM recipes WHERE id = ?');
$stmt->execute([$id]);
$recipe = $stmt->fetch();

if (!$recipe) {
    die('Рецепт не найден. <a href="/">Вернуться на главную</a>');
}

$pageTitle = $recipe['title'];

// Достаем ингредиенты для этого рецепта
$stmt = db()->prepare('
    SELECT rp.amount, rp.unit, p.name 
    FROM recipe_products rp
    JOIN products p ON rp.product_id = p.id
    WHERE rp.recipe_id = ?
');
$stmt->execute([$id]);
$ingredients = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<!-- Шапка рецепта -->
<div class="recipe-header">
    <h1 class="page-title"><?= htmlspecialchars($recipe['title']) ?></h1>
    <p class="recipe-description"><?= htmlspecialchars($recipe['description']) ?></p>
    
    <div class="recipe-header-meta" style="margin-bottom: 24px;">
        <span><i class="fa-regular fa-clock"></i> <?= $recipe['time_min'] ?> мин</span>
        <span><i class="fa-solid fa-signal"></i> 
            <?php 
                $diff = $recipe['difficulty'];
                if ($diff === 'easy') echo 'Легко';
                elseif ($diff === 'medium') echo 'Средне';
                elseif ($diff === 'hard') echo 'Сложно';
                else echo $diff;
            ?>
        </span>
    </div>

      <!-- Большая картинка рецепта -->
    <?php if (!empty($recipe['image_url'])): ?>
        <div class="recipe-main-image-wrapper">
            <img src="<?= htmlspecialchars($recipe['image_url']) ?>" alt="<?= htmlspecialchars($recipe['title']) ?>" class="recipe-main-image">
        </div>
    <?php endif; ?>
</div>

<div class="recipe-layout">
    <!-- ЛЕВАЯ КОЛОНКА (Ингредиенты) -->
    <aside class="recipe-sidebar">
        <div class="ingredients-box">
            <div class="ingredients-header">
                <h2>Ингредиенты на</h2>
                <div class="portion-control">
                    <button type="button" onclick="changePortion(-1)">−</button>
                    <input type="number" id="portion-input" value="<?= $recipe['servings'] ?>" min="1" max="10" readonly>
                    <button type="button" onclick="changePortion(1)">+</button>
                </div>
                <h2>порции</h2>
            </div>
            
            <ul class="ingredients-list">
                <?php foreach ($ingredients as $ing): ?>
                    <li>
                        <span class="ing-name"><?= htmlspecialchars($ing['name']) ?></span>
                        <span class="ing-amount" 
                              data-amount="<?= $ing['amount'] ?>" 
                              data-unit="<?= htmlspecialchars($ing['unit']) ?>" 
                              data-base-portions="<?= $recipe['servings'] ?>">
                            <?= $ing['amount'] ? $ing['amount'] . ' ' . $ing['unit'] : 'по вкусу' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <?php 
            // Проверяем, есть ли рецепт уже в избранном
            $isInFav = isset($_SESSION['cart']) && in_array($recipe['id'], $_SESSION['cart']); 
            ?>

            <?php if ($isInFav): ?>
                <!-- Если уже добавлен, показываем красное сердечко -->
                <button class="btn-save-dark" style="background: #fff0e6; color: #e63946; border: 1px solid #e63946; cursor: default;">
                    <i class="fa-solid fa-heart"></i> В избранном
                </button>
            <?php else: ?>
                <!-- Если нет, показываем кнопку для добавления -->
                <button class="btn-save-dark like-btn" data-id="<?= $recipe['id'] ?>">
                    <i class="fa-regular fa-heart"></i> Добавить в избранное
                </button>
            <?php endif; ?>
        </div>
    </aside>

   
    <main class="recipe-steps">
        <h2>Как готовить</h2>
        <div class="steps-list">
            <?php 
            $steps = explode("\n", $recipe['steps']);
            $stepNum = 1;
            foreach ($steps as $step): 
                if (trim($step) === '') continue;
                $cleanStep = preg_replace('/^\d+\.\s*/', '', trim($step));
            ?>
                <div class="step-item">
                    <div class="step-number"><?= $stepNum ?></div>
                    <div class="step-text"><?= htmlspecialchars($cleanStep) ?></div>
                </div>
            <?php 
                $stepNum++;
            endforeach; 
            ?>
        </div>
    </main>
</div>

<!-- НИЖНЯЯ ПЛАШКА БЖУ -->
<?php if ($recipe['calories'] > 0): ?>
<section class="nutrition-section-bottom">
    <h2>Пищевая ценность</h2>
    <div class="nutrition-grid-bottom">
        <div class="nutr-card">
            <div class="nutr-title"><span>Белки</span> <span><?= $recipe['protein'] ?> г</span></div>
            <div class="nutr-bar"><div class="nutr-fill" style="width: <?= min(100, $recipe['protein'] * 2) ?>%"></div></div>
        </div>
        <div class="nutr-card">
            <div class="nutr-title"><span>Жиры</span> <span><?= $recipe['fat'] ?> г</span></div>
            <div class="nutr-bar"><div class="nutr-fill" style="width: <?= min(100, $recipe['fat'] * 2) ?>%"></div></div>
        </div>
        <div class="nutr-card">
            <div class="nutr-title"><span>Углеводы</span> <span><?= $recipe['carbs'] ?> г</span></div>
            <div class="nutr-bar"><div class="nutr-fill" style="width: <?= min(100, $recipe['carbs'] * 2) ?>%"></div></div>
        </div>
        <div class="nutr-card">
            <div class="nutr-title"><span>Калории</span> <span><?= $recipe['calories'] ?> кКал</span></div>
            <div class="nutr-bar"><div class="nutr-fill" style="width: <?= min(100, $recipe['calories'] / 5) ?>%"></div></div>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
    // Скрипт для пересчета порций
    function changePortion(delta) {
        const input = document.getElementById('portion-input');
        let current = parseInt(input.value);
        let newValue = current + delta;
        
        if (newValue >= 1 && newValue <= 10) {
            input.value = newValue;
            updateIngredients(newValue);
        }
    }

    function updateIngredients(portions) {
        document.querySelectorAll('.ing-amount').forEach(el => {
            const baseAmount = parseFloat(el.getAttribute('data-amount'));
            const basePortions = parseFloat(el.getAttribute('data-base-portions'));
            const unit = el.getAttribute('data-unit') || ''; 
            
            if (!isNaN(baseAmount) && baseAmount > 0) {
                let newAmount = (baseAmount / basePortions) * portions;
                let formattedAmount = Number.isInteger(newAmount) ? newAmount : newAmount.toFixed(1);
                el.textContent = formattedAmount + ' ' + unit;
            } else {
                el.textContent = 'по вкусу';
            }
        });
    }
</script>

<script>
    // Скрипт для добавления в избранное с самой страницы рецепта
    document.addEventListener('DOMContentLoaded', function() {
        const likeBtn = document.querySelector('.like-btn');
        
        if (likeBtn) {
            likeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const btn = this;
                const recipeId = btn.getAttribute('data-id');

                // 1. Мгновенно меняем вид кнопки
                btn.style.background = '#fff0e6';
                btn.style.color = '#e63946';
                btn.style.border = '1px solid #e63946';
                btn.style.cursor = 'default';
                btn.innerHTML = '<i class="fa-solid fa-heart"></i> В избранном';
                
                // 2. Отправляем запрос в фон
                fetch('/api/cart_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'recipe_id=' + recipeId + '&action=add'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Добавлено в избранное!');
                    }
                })
                .catch(error => console.error('Ошибка:', error));
            });
        }
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>