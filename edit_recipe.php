<?php 
require __DIR__ . '/auth.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recipe = ['title' => '', 'description' => '', 'category' => 'Обеды', 'time_min' => 30, 'difficulty' => 'easy', 'servings' => 2, 'steps' => '', 'image_url' => '', 'calories' => 0, 'protein' => 0, 'fat' => 0, 'carbs' => 0];
$currentIngredients = [];

// Получаем все продукты для выпадающего списка
$allProducts = db()->query('SELECT * FROM products ORDER BY name ASC')->fetchAll();

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM recipes WHERE id = ?');
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    if (!$recipe) die('Рецепт не найден');

    $stmt = db()->prepare('SELECT rp.*, p.name FROM recipe_products rp JOIN products p ON rp.product_id = p.id WHERE rp.recipe_id = ?');
    $stmt->execute([$id]);
    $currentIngredients = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'Обеды');
    $time_min = (int)($_POST['time_min'] ?? 30);
    $difficulty = $_POST['difficulty'] ?? 'easy';
    $servings = (int)($_POST['servings'] ?? 2);
    $steps = trim($_POST['steps'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    // Автоматический расчет КБЖУ
    $total_calories = 0;
    $total_protein = 0;
    $total_fat = 0;
    $total_carbs = 0;

    if (isset($_POST['ing_product']) && is_array($_POST['ing_product'])) {
        for ($i = 0; $i < count($_POST['ing_product']); $i++) {
            $prodId = (int)$_POST['ing_product'][$i];
            $amount = (float)($_POST['ing_amount'][$i] ?? 0);
            $unit = trim($_POST['ing_unit'][$i]);

            if ($prodId > 0 && $amount > 0) {
                $stmtP = db()->prepare('SELECT calories_per_100g, protein_per_100g, fat_per_100g, carbs_per_100g FROM products WHERE id = ?');
                $stmtP->execute([$prodId]);
                $pData = $stmtP->fetch();

                // Считаем только если указаны граммы!
                if ($unit === 'г' || $unit === 'гр' || $unit === 'g') {
                    $total_calories += ($pData['calories_per_100g'] * $amount) / 100;
                    $total_protein += ($pData['protein_per_100g'] * $amount) / 100;
                    $total_fat += ($pData['fat_per_100g'] * $amount) / 100;
                    $total_carbs += ($pData['carbs_per_100g'] * $amount) / 100;
                }
            }
        }
    }

    // Делим на порции (если нужно КБЖУ на 1 порцию)
    // $total_calories = round($total_calories / $servings); ...

    // Округляем итоги
    $calories = round($total_calories);
    $protein = round($total_protein);
    $fat = round($total_fat);
    $carbs = round($total_carbs);

    if ($title && $steps) {
        if ($id > 0) {
            $stmt = db()->prepare('UPDATE recipes SET title=?, description=?, category=?, time_min=?, difficulty=?, servings=?, steps=?, image_url=?, calories=?, protein=?, fat=?, carbs=? WHERE id=?');
            $stmt->execute([$title, $description, $category, $time_min, $difficulty, $servings, $steps, $image_url, $calories, $protein, $fat, $carbs, $id]);
        } else {
            $stmt = db()->prepare('INSERT INTO recipes (title, description, category, time_min, difficulty, servings, steps, image_url, calories, protein, fat, carbs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $category, $time_min, $difficulty, $servings, $steps, $image_url, $calories, $protein, $fat, $carbs]);
            $id = db()->lastInsertId();
        }

        // Сохранение ингредиентов
        $stmt = db()->prepare('DELETE FROM recipe_products WHERE recipe_id = ?');
        $stmt->execute([$id]);

        if (isset($_POST['ing_product']) && is_array($_POST['ing_product'])) {
            $stmt = db()->prepare('INSERT INTO recipe_products (recipe_id, product_id, amount, unit) VALUES (?, ?, ?, ?)');
            for ($i = 0; $i < count($_POST['ing_product']); $i++) {
                $prodId = (int)$_POST['ing_product'][$i];
                $amount = $_POST['ing_amount'][$i] !== '' ? (float)$_POST['ing_amount'][$i] : null;
                $unit = trim($_POST['ing_unit'][$i]);
                
                if ($prodId > 0) {
                    $stmt->execute([$id, $prodId, $amount, $unit]);
                }
            }
        }

        header('Location: /admin/recipes.php?msg=Рецепт сохранен, КБЖУ пересчитано');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Редактировать' : 'Добавить' ?> рецепт | Админка</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ing-row { display: grid; grid-template-columns: 2fr 1fr 1fr 40px; gap: 10px; margin-bottom: 10px; align-items: center; }
        .ing-row input, .ing-row select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .btn-remove-ing { background: #ffebee; color: #e63946; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .btn-remove-ing:hover { background: #e63946; color: #fff; }
        .btn-add-ing { background: #e3f2fd; color: #1976d2; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; transition: 0.2s; }
        .btn-add-ing:hover { background: #1976d2; color: #fff; }
    </style>
</head>
<body style="background: #f4f4f4;">
    <header class="site-header" style="background: #111;"><div class="header-container"><a href="/admin/recipes.php" class="logo" style="color:#fff;">← Назад к рецептам</a></div></header>
    
    <div class="container" style="margin-top: 40px; max-width: 900px;">
        <div style="background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h1 class="page-title" style="margin-bottom: 24px;"><?= $id ? 'Редактировать' : 'Новый' ?> рецепт</h1>
            
            <div style="background: #fff0e6; color: #d62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                <i class="fa-solid fa-info-circle"></i> КБЖУ рецепта рассчитывается автоматически, <strong>только если ингредиенты указаны в граммах (г)</strong>. Для штук и ложек расчет не производится.
            </div>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Название рецепта</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Краткое описание</label>
                    <input type="text" name="description" value="<?= htmlspecialchars($recipe['description']) ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:500;">Категория</label>
                        <select name="category" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
                            <option value="Завтраки" <?= $recipe['category'] === 'Завтраки' ? 'selected' : '' ?>>Завтраки</option>
                            <option value="Обеды" <?= $recipe['category'] === 'Обеды' ? 'selected' : '' ?>>Обеды</option>
                            <option value="Салаты" <?= $recipe['category'] === 'Салаты' ? 'selected' : '' ?>>Салаты</option>
                            <option value="Выпечка" <?= $recipe['category'] === 'Выпечка' ? 'selected' : '' ?>>Выпечка</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:500;">Сложность</label>
                        <select name="difficulty" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
                            <option value="easy" <?= $recipe['difficulty'] === 'easy' ? 'selected' : '' ?>>Легко</option>
                            <option value="medium" <?= $recipe['difficulty'] === 'medium' ? 'selected' : '' ?>>Средне</option>
                            <option value="hard" <?= $recipe['difficulty'] === 'hard' ? 'selected' : '' ?>>Сложно</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:500;">Время (мин)</label>
                        <input type="number" name="time_min" value="<?= $recipe['time_min'] ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:8px; font-weight:500;">Порции</label>
                        <input type="number" name="servings" value="<?= $recipe['servings'] ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                     <div>
                        <label style="display:block; margin-bottom:8px; font-weight:500;">Ссылка на фото</label>
                        <input type="text" name="image_url" value="<?= htmlspecialchars($recipe['image_url']) ?>" placeholder="/assets/img/photo.jpg" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                </div>

                <!-- ИНФОРМАЦИЯ О КБЖУ (только для чтения) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 12px; border: 1px solid #eee;">
                    <div><label style="display:block; margin-bottom:8px; font-weight:500;">Калории</label><div style="font-size: 18px; font-weight: bold; color: #333;"><?= $recipe['calories'] ?> ккал</div></div>
                    <div><label style="display:block; margin-bottom:8px; font-weight:500;">Белки</label><div style="font-size: 18px; font-weight: bold; color: #333;"><?= $recipe['protein'] ?> г</div></div>
                    <div><label style="display:block; margin-bottom:8px; font-weight:500;">Жиры</label><div style="font-size: 18px; font-weight: bold; color: #333;"><?= $recipe['fat'] ?> г</div></div>
                    <div><label style="display:block; margin-bottom:8px; font-weight:500;">Углеводы</label><div style="font-size: 18px; font-weight: bold; color: #333;"><?= $recipe['carbs'] ?> г</div></div>
                </div>

                <!-- БЛОК ИНГРЕДИЕНТОВ -->
                <div style="background: #f9f9f9; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #eee;">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #333;">Ингредиенты</h3>
                    
                    <div id="ingredients-container">
                        <?php if (count($currentIngredients) > 0): ?>
                            <?php foreach ($currentIngredients as $ing): ?>
                                <div class="ing-row">
                                    <select name="ing_product[]">
                                        <option value="">-- Выберите продукт --</option>
                                        <?php foreach ($allProducts as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $ing['product_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" step="0.1" name="ing_amount[]" value="<?= $ing['amount'] ?>" placeholder="Кол-во">
                                    <input type="text" name="ing_unit[]" value="<?= htmlspecialchars($ing['unit']) ?>" placeholder="Ед. изм (г)">
                                    <button type="button" class="btn-remove-ing" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="btn-add-ing" onclick="addIngredient()">
                        <i class="fa-solid fa-plus"></i> Добавить ингредиент
                    </button>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Шаги приготовления (каждый шаг с новой строки)</label>
                    <textarea name="steps" rows="8" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;"><?= htmlspecialchars($recipe['steps']) ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer; padding: 15px;">Сохранить рецепт</button>
            </form>
        </div>
    </div>

    <template id="ing-template">
        <div class="ing-row">
            <select name="ing_product[]">
                <option value="">-- Выберите продукт --</option>
                <?php foreach ($allProducts as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" step="0.1" name="ing_amount[]" placeholder="Кол-во">
            <input type="text" name="ing_unit[]" placeholder="Ед. изм (г)">
            <button type="button" class="btn-remove-ing" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </template>

    <script>
        function addIngredient() {
            const container = document.getElementById('ingredients-container');
            const template = document.getElementById('ing-template');
            const clone = template.content.cloneNode(true);
            container.appendChild(clone);
        }
    </script>
</body>
</html>