<?php
require __DIR__ . '/config/db.php';
session_start();
$pageTitle = 'Регистрация';

if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        // Проверяем, есть ли уже такой email
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже существует';
        } else {
            // Хэшируем пароль и создаем пользователя
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            
            $_SESSION['user_id'] = db()->lastInsertId();
            $_SESSION['user_name'] = $name;
            header('Location: /');
            exit;
        }
    } else {
        $error = 'Заполните все поля';
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="auth-box">
    <h1>Регистрация</h1>
    <?php if ($error): ?><p style="color: red; margin-bottom: 15px;"><?= $error ?></p><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Ваше имя</label>
            <input type="text" name="name" required placeholder="Иван">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="ivan@mail.ru">
        </div>
        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" required placeholder="Придумайте пароль">
        </div>
        <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer;">Зарегистрироваться</button>
    </form>
    <p style="text-align: center; margin-top: 20px; font-size: 14px;">
        Уже есть аккаунт? <a href="/login.php" style="color: #e63946;">Войти</a>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>