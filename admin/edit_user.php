<?php 
require __DIR__ . '/auth.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die('Пользователь не найден');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if ($name && $email) {
        // Проверка, не занят ли email другим пользователем
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $id]);
        
        if ($stmt->fetch()) {
            $error = 'Этот email уже используется другим пользователем.';
        } else {
            // Обновляем данные
            $sql = 'UPDATE users SET name = ?, email = ?, is_admin = ?';
            $params = [$name, $email, $is_admin];

            // Если введен новый пароль, обновляем и его
            if (!empty($password)) {
                $sql .= ', password = ?';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= ' WHERE id = ?';
            $params[] = $id;

            $stmt = db()->prepare($sql);
            $stmt->execute($params);

            // Если меняем самого себя — обновляем имя в сессии
            if ($id == $_SESSION['user_id']) {
                $_SESSION['user_name'] = $name;
                $_SESSION['is_admin'] = $is_admin;
            }

            $success = 'Данные успешно сохранены!';
            
            // Обновляем данные для отображения в форме
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch();
        }
    } else {
        $error = 'Заполните обязательные поля.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование пользователя | Админка</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #f4f4f4;">
    <header class="site-header" style="background: #111;"><div class="header-container"><a href="/admin/users.php" class="logo" style="color:#fff;">← Назад к пользователям</a></div></header>
    
    <div class="container" style="margin-top: 40px; max-width: 600px;">
        <div style="background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h1 class="page-title" style="margin-bottom: 24px;">Изменить данные: <?= htmlspecialchars($user['name']) ?></h1>

            <?php if ($error): ?>
                <div style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#666;">Имя</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#666;">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#666;">Новый пароль (оставьте пустым, чтобы не менять)</label>
                    <input type="password" name="password" placeholder="••••••••" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div style="margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_admin" id="is_admin" value="1" <?= $user['is_admin'] ? 'checked' : '' ?> style="width:18px; height:18px;">
                    <label for="is_admin" style="margin:0; font-size:15px; color:#333; font-weight:500; cursor:pointer;">Права администратора</label>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; border:none; cursor:pointer;">Сохранить изменения</button>
            </form>
        </div>
    </div>
</body>
</html>