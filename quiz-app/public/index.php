<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

// カテゴリ一覧
$stmt = pdo()->query('SELECT id, name FROM categories ORDER BY name');
$categories = $stmt->fetchAll();
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>クイズアプリ</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="brand">📘 クイズアプリ</div>
      <nav>
        <a class="button secondary" href="<?php echo e(base_path('admin/login.php')); ?>">管理者ログイン</a>
      </nav>
    </header>

    <div class="card">
      <h2>カテゴリを選んで開始</h2>
      <div class="list">
        <?php foreach ($categories as $c): ?>
          <div class="card">
            <div class="badge">カテゴリー</div>
            <h3><?php echo e($c['name']); ?></h3>
            <a class="button" href="quiz.php?category_id=<?php echo (int)$c['id']; ?>">開始</a>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (empty($categories)): ?>
        <p>カテゴリがありません。管理画面から追加してください。</p>
      <?php endif; ?>
    </div>

    <footer class="footer">© Quiz App</footer>
  </div>
</body>
</html>