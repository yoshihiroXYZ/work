<?php
// 一時的に使って、管理者パスワードのハッシュを生成するだけのページ
// 使い終わったら必ず削除してください。
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>パスワードハッシュ生成</title>
  <link rel="stylesheet" href="../public/assets/styles.css">
</head>
<body>
<div class="container">
  <div class="card">
    <h2>パスワードハッシュ生成</h2>
    <form method="post">
      <label class="label">プレーンパスワード</label>
      <input class="input" type="text" name="pw" required>
      <button class="button" style="margin-top:8px">生成</button>
    </form>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'):
      $pw = (string)($_POST['pw'] ?? '');
      $hash = password_hash($pw, PASSWORD_DEFAULT);
    ?>
      <div class="notice" style="word-break:break-all">ハッシュ：<code><?php echo e($hash); ?></code></div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>