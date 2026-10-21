<?php 
require __DIR__ . '/auth.php'; 
$articles = db()->query('SELECT * FROM articles ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление статьями | Админка</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; font-size: 14px; }
        .btn-edit { background: #fff0e6; color: #f48c06; }
        .btn-edit:hover { background: #f48c06; color: #fff; }
        .btn-delete { background: #ffebee; color: #e63946; }
        .btn-delete:hover { background: #e63946; color: #fff; }
        .btn-add { display: inline-flex; align-items: center; gap: 8px; background: #e63946; color: #fff; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600; margin-bottom: 20px; }
    </style>
</head>
<body style="background: #f4f4f4;">
    <header class="site-header" style="background: #111;">
        <div class="header-container"><a href="/admin/" class="logo" style="color:#fff;">← Назад в админку</a></div>
    </header>
    
    <div class="container" style="margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="page-title" style="margin: 0;">Статьи</h1>
            <a href="/admin/edit_article.php" class="btn-add"><i class="fa-solid fa-plus"></i> Добавить статью</a>
        </div>
        
        <?php if (isset($_GET['msg'])): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <table style="width: 100%; background: #fff; border-radius: 12px; border-collapse: collapse; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <tr style="background: #ffe5d0; text-align: left;">
                <th style="padding: 16px;">ID</th>
                <th style="padding: 16px;">Заголовок</th>
                <th style="padding: 16px;">Дата</th>
                <th style="padding: 16px; text-align: center;">Действия</th>
            </tr>
            <?php foreach ($articles as $a): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 16px;"><?= $a['id'] ?></td>
                <td style="padding: 16px;"><strong><?= htmlspecialchars($a['title']) ?></strong></td>
                <td style="padding: 16px;"><?= date('d.m.Y', strtotime($a['created_at'])) ?></td>
                <td style="padding: 16px; text-align: center; display: flex; gap: 8px; justify-content: center;">
                    <a href="/admin/edit_article.php?id=<?= $a['id'] ?>" class="action-btn btn-edit" title="Редактировать">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <form action="/admin/delete_article.php" method="POST" style="margin: 0;" onsubmit="return confirm('Точно удалить статью?');">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <button type="submit" class="action-btn btn-delete" title="Удалить">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>