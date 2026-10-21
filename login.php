<?php
require __DIR__ . '/config/db.php';
session_start();
$pageTitle = 'Вход';

if (isset($_SESSION['user_id'])) {
    header('Location: /'); 
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
             $_SESSION['is_admin'] = $user['is_admin']; 
            header('Location: /');
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    } else {
        $error = 'Заполните все поля';
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="auth-box">
    <h1>Вход</h1>
    <?php if ($error): ?><p style="color: red; margin-bottom: 15px;"><?= $error ?></p><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="ivan@mail.ru">
        </div>
        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" required placeholder="123456">
        </div>
        <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer;">Войти</button>
    </form>
    <p style="text-align: center; margin-top: 20px; font-size: 14px;">
        Нет аккаунта? <a href="/register.php" style="color: #e63946;">Зарегистрироваться</a>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>