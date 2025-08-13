<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

verify_csrf();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    $stmt = pdo()->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$u]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($p, $admin['password_hash'])) {
        login_admin((int)$admin['id']);
        header('Location: ' . base_path('admin/questions.php'));
        exit;
    } else {
        $error = 'ユーザー名またはパスワードが違います。';
    }
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>管理者ログイン</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container">
  <header class="header">
    <div class="brand">🔐 管理者ログイン</div>
  </header>
  <div class="card">
    <?php if ($error): ?><div class="notice"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post">
      <?php echo csrf_field(); ?>
      <label class="label">ユーザー名</label>
      <input class="input" type="text" name="username" required>
      <label class="label" style="margin-top:10px">パスワード</label>
      <input class="input" type="password" name="password" required>
      <div style="margin-top:12px">
        <button class="button" type="submit">ログイン</button>
        <a class="button secondary" href="<?php echo e(base_path('index.php')); ?>">トップへ</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
```php
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

verify_csrf();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    $stmt = pdo()->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$u]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($p, $admin['password_hash'])) {
        login_admin((int)$admin['id']);
        header('Location: ' . base_path('admin/questions.php'));
        exit;
    } else {
        $error = 'ユーザー名またはパスワードが違います。';
    }
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>管理者ログイン</title>
  <link rel="stylesheet" href="../public/assets/styles.css">
</head>
<body>
<div class="container">
  <header class="header">
    <div class="brand">🔐 管理者ログイン</div>
  </header>
  <div class="card">
    <?php if ($error): ?><div class="notice"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post">
      <?php echo csrf_field(); ?>
      <label class="label">ユーザー名</label>
      <input class="input" type="text" name="username" required>
      <label class="label" style="margin-top:10px">パスワード</label>
      <input class="input" type="password" name="password" required>
      <div style="margin-top:12px">
        <button class="button" type="submit">ログイン</button>
        <a class="button secondary" href="<?php echo e(base_path('index.php')); ?>">トップへ</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>