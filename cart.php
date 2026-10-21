<?php
require __DIR__ . '/config/db.php';
session_start();

$pageTitle = 'Избранные рецепты';


$recipes = [];
if (isset($_SESSION['user_id'])) {
    $stmt = db()->prepare('
        SELECT r.* 
        FROM recipes r 
        JOIN user_favorites uf ON r.id = uf.recipe_id 
        WHERE uf.user_id = ?
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $recipes = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Избранные рецепты</h1>

<?php if (count($recipes) > 0): ?>
    <div class="recipe-grid grid-4">
        <?php foreach ($recipes as $r): ?>
            <div style="position: relative;">
                <a href="/recipe.php?id=<?= $r['id'] ?>" class="recipe-card" style="text-decoration: none; color: inherit; display: flex;">
                    
                    
                    <div class="recipe-image-placeholder" style="overflow: hidden; padding: 0;">
                        <?php if (!empty($r['image_url'])): ?>
                            <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                        <?php else: ?>
                            <i class="fa-solid fa-utensils"></i>
                        <?php endif; ?>
                    </div>

                    <div class="recipe-content">
                        <h3><?= htmlspecialchars($r['title']) ?></h3>
                        <div class="recipe-meta-bottom">
                            <span class="meta-time"><?= (int)$r['time_min'] ?> мин</span>
                        </div>
                    </div>
                </a>
                
                <!-- Кнопка удаления из избранного -->
                <button type="button" class="remove-btn" 
                        data-id="<?= $r['id'] ?>"
                        style="position: absolute; top: 12px; right: 12px; background: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; color: #e63946; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 10; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;"
                        onmouseover="this.style.transform='scale(1.1)'" 
                        onmouseout="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="font-size: 18px; color: #888;">Вы пока не добавили ни одного рецепта. Перейдите в <a href="/recipes.php" style="color: #e63946;">Рецепты</a>, чтобы найти что-то вкусное!</p>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-btn');

    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); 

            const btn = this;
            const recipeId = btn.getAttribute('data-id');
            
          
            const cardWrapper = btn.closest('div[style*="position: relative"]');
            
            if (confirm('Удалить этот рецепт из избранного?')) {
                // Отправляем запрос на удаление
                fetch('/api/cart_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'recipe_id=' + recipeId + '&action=remove'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Плавно скрываем карточку
                        cardWrapper.style.transition = 'opacity 0.3s';
                        cardWrapper.style.opacity = '0';
                        setTimeout(() => {
                            cardWrapper.remove();
                            
                            // Если карточек больше не осталось, показываем текст
                            if(document.querySelectorAll('.recipe-card').length === 0) {
                                location.reload(); 
                            }
                        }, 300);
                    }
                })
                .catch(error => console.error('Ошибка:', error));
            }
        });
    });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>