<?php require __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель | RecipeFinder</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #f4f4f4;">
    <header class="site-header" style="background: #111; color: #fff; border: none;">
        <div class="header-container">
            <a href="/" class="logo" style="color: #fff;"><i class="fa-solid fa-utensils" style="color: #e63946;"></i> Админ-панель</a>
            <a href="/" class="btn-outline" style="color: #fff; border-color: #444; background: transparent;">На сайт</a>
        </div>
    </header>
    
    <div class="container" style="margin-top: 40px;">
        <h1 class="page-title">Управление сайтом</h1>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 30px;">
            <!-- Управление рецептами -->
            <a href="/admin/recipes.php" style="background: #fff; padding: 30px; border-radius: 16px; text-decoration: none; color: #333; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <i class="fa-solid fa-book-open" style="font-size: 40px; color: #e63946; margin-bottom: 16px;"></i>
                <h2 style="font-size: 20px; margin: 0;">Рецепты</h2>
                <p style="color: #888; margin-top: 8px; font-size: 14px;">Добавить, изменить или удалить рецепт</p>
            </a>
            
            <!-- Управление статьями -->
            <a href="/admin/articles.php" style="background: #fff; padding: 30px; border-radius: 16px; text-decoration: none; color: #333; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <i class="fa-regular fa-newspaper" style="font-size: 40px; color: #f48c06; margin-bottom: 16px;"></i>
                <h2 style="font-size: 20px; margin: 0;">Статьи</h2>
                <p style="color: #888; margin-top: 8px; font-size: 14px;">Управление блогом и материалами</p>
            </a>

            <!-- Управление пользователями -->
            <a href="/admin/users.php" style="background: #fff; padding: 30px; border-radius: 16px; text-decoration: none; color: #333; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <i class="fa-solid fa-users" style="font-size: 40px; color: #2a9d8f; margin-bottom: 16px;"></i>
                <h2 style="font-size: 20px; margin: 0;">Пользователи</h2>
                <p style="color: #888; margin-top: 8px; font-size: 14px;">Список профилей и права доступа</p>
            </a>
        </div>
    </div>
</body>
</html>