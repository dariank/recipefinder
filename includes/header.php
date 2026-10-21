<?php
if (!defined('APP_NAME')) {
    define('APP_NAME', 'RecipeFinder');
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  
  <!-- Иконки -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Шрифты -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Jost:wght@500;600;700&display=swap" rel="stylesheet">
  
  <!-- Стили -->
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  
  <!-- Уникальная шапка -->
  <header class="site-header">
    <div class="header-container">
      
      <!-- Логотип -->
      <a href="/" class="logo">
        <i class="fa-solid fa-utensils"></i> <?= APP_NAME ?>
      </a>

      <!-- Навигация, поиск и профиль -->
      <div class="header-actions">
        
        <!-- Кнопки навигации -->
        <div class="nav-links">
            <a href="/recipes.php" class="nav-link">
              <i class="fa-solid fa-book-open"></i> Рецепты
            </a>
            <a href="/articles.php" class="nav-link">
              <i class="fa-regular fa-newspaper"></i> Статьи
            </a>
            <a href="/cart.php" class="nav-link">
              <i class="fa-solid fa-heart" style="color: #e63946;"></i> Избранное
            </a>
        </div>

        <!-- Поиск -->
        <form action="/recipes.php" method="GET" class="header-search">
          <input type="text" name="search" id="search-input" placeholder="Что приготовим сегодня?" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </form>

        <!-- Профиль / Войти -->
        <div class="auth-block" style="display: flex; align-items: center; gap: 12px;">
            
            <!-- Кнопка Админки (вынесена отдельно) -->
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="/admin/" class="nav-link" style="background: #111; color: #fff;">
                    <i class="fa-solid fa-gear"></i> Админка
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Блок авторизованного пользователя (пилюля) -->
                <div class="user-profile-badge">
                    <a href="/profile.php" class="user-name-link" title="Перейти в профиль">
                        <div class="user-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </a>
                    <div class="user-divider"></div>
                    <a href="/logout.php" class="logout-btn" title="Выйти из аккаунта">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            <?php else: ?>
                <!-- Кнопка Войти (красная) -->
                <a href="/login.php" class="login-btn">
                    <i class="fa-regular fa-user"></i> Войти
                </a>
            <?php endif; ?>
        </div>

      </div>
    </div>
  </header>

  <main class="container">