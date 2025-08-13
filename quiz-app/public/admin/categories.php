<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_login();
verify_csrf();

$pdo = pdo();

// 追加
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO categories(name) VALUES (?)');
        $stmt->execute([$name]);
        header('Location: ' . base_path('admin/categories.php'));
        exit;
    }
}

// 削除
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: ' . base_path('admin/categories.php'));
    exit;
}

$cats = $pdo->query('SELECT id, name FROM categories ORDER BY id DESC')->fetchAll();
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>カテゴリ管理</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container">
  <header class="header">
    <div class="brand">📁 カテゴリ管理</div>
    <nav>
      <a class="button secondary" href="/">問題ページへ</a>
      <a class="button secondary" href="questions.php">問題管理</a>
      <a class="button secondary" href="logout.php">ログアウト</a>
    </nav>
  </header>

  <div class="card">
    <h2>カテゴリを追加</h2>
    <form method="post">
      <?php echo csrf_field(); ?>
      <input class="input" type="text" name="name" placeholder="例：HTML" required>
      <button class="button" style="margin-top:8px" type="submit">追加</button>
    </form>
  </div>

  <div class="card">
    <h2>カテゴリ一覧</h2>
    <table class="table">
      <thead><tr><th>ID</th><th>名前</th><th>操作</th></tr></thead>
      <tbody>
      <?php foreach ($cats as $c): ?>
        <tr>
          <td><?php echo (int)$c['id']; ?></td>
          <td><?php echo e($c['name']); ?></td>
          <td>
            <a class="button secondary" href="?delete=<?php echo (int)$c['id']; ?>" onclick="return confirm('削除しますか？このカテゴリの問題も削除されます');">削除</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
