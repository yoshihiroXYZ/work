<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_login();
verify_csrf();

$pdo = pdo();

$action = $_GET['action'] ?? 'list';

// 共通：カテゴリ一覧
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

function findQuestion(PDO $pdo, int $id): array|null {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = ?');
    $stmt->execute([$id]);
    $q = $stmt->fetch();
    if (!$q) return null;
    $stmt = $pdo->prepare('SELECT id, text, is_correct FROM choices WHERE question_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $q['choices'] = $stmt->fetchAll();
    return $q;
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $text = trim($_POST['text'] ?? '');
    $explanation = trim($_POST['explanation'] ?? '');

    $choices = $_POST['choice'] ?? [];
    $correct = (int)($_POST['correct'] ?? -1);

    if ($category_id && $text && count($choices) >= 2 && isset($choices[$correct])) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO questions(category_id, text, explanation) VALUES (?,?,?)');
            $stmt->execute([$category_id, $text, $explanation]);
            $qid = (int)$pdo->lastInsertId();
            foreach ($choices as $i => $ctext) {
                $stmt = $pdo->prepare('INSERT INTO choices(question_id, text, is_correct) VALUES (?,?,?)');
                $stmt->execute([$qid, trim($ctext), ($i == $correct) ? 1 : 0]);
            }
            $pdo->commit();
            header('Location: ' . base_path('admin/questions.php'));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            exit('保存に失敗しました');
        }
    } else {
        exit('入力が不正です');
    }
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $text = trim($_POST['text'] ?? '');
    $explanation = trim($_POST['explanation'] ?? '');
    $choices = $_POST['choice'] ?? [];
    $choice_ids = $_POST['choice_id'] ?? [];
    $correct = (int)($_POST['correct'] ?? -1);

    if ($id && $category_id && $text && count($choices) === count($choice_ids) && isset($choices[$correct])) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE questions SET category_id=?, text=?, explanation=? WHERE id=?');
            $stmt->execute([$category_id, $text, $explanation, $id]);

            // 既存選択肢を更新（数は固定とする）
            foreach ($choice_ids as $i => $cid) {
                $stmt = $pdo->prepare('UPDATE choices SET text=?, is_correct=? WHERE id=? AND question_id=?');
                $stmt->execute([trim($choices[$i]), ($i == $correct) ? 1 : 0, (int)$cid, $id]);
            }
            $pdo->commit();
            header('Location: ' . base_path('admin/questions.php'));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            exit('更新に失敗しました');
        }
    } else {
        exit('入力が不正です');
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM questions WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: ' . base_path('admin/questions.php'));
    exit;
}

// 表示用データ
$filter_category = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
if ($action === 'list') {
    if ($filter_category) {
        $stmt = $pdo->prepare('SELECT q.id, q.text, c.name AS cname FROM questions q JOIN categories c ON c.id=q.category_id WHERE q.category_id = ? ORDER BY q.id DESC');
        $stmt->execute([$filter_category]);
    } else {
        $stmt = $pdo->query('SELECT q.id, q.text, c.name AS cname FROM questions q JOIN categories c ON c.id=q.category_id ORDER BY q.id DESC');
    }
    $rows = $stmt->fetchAll();
}

$edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit = findQuestion($pdo, (int)$_GET['id']);
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>問題管理</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<div class="container">
  <header class="header">
    <div class="brand">📝 問題管理</div>
    <nav>

      <a class="button secondary" href="/">問題ページへ</a>
      <a class="button secondary" href="categories.php">カテゴリ管理</a>
      <a class="button secondary" href="logout.php">ログアウト</a>
    </nav>
  </header>

  <div class="card">
    <h2>フィルタ</h2>
    <form method="get">
      <label class="label">カテゴリ</label>
      <select name="category_id" class="input" onchange="this.form.submit()">
        <option value="0">すべて</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo $filter_category===$c['id']?'selected':'';?>><?php echo e($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="action" value="list">
    </form>
  </div>

  <div class="card">
    <h2>新規作成</h2>
    <form method="post" action="?action=create">
      <?php echo csrf_field(); ?>
      <label class="label">カテゴリ</label>
      <select class="input" name="category_id" required>
        <?php foreach ($categories as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <label class="label" style="margin-top:10px">問題文</label>
      <textarea class="input" name="text" rows="3" required></textarea>
      <label class="label" style="margin-top:10px">解説（任意）</label>
      <textarea class="input" name="explanation" rows="2"></textarea>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px">
        <?php for($i=0;$i<4;$i++): ?>
          <div>
            <label class="label">選択肢 <?php echo $i+1; ?></label>
            <input class="input" type="text" name="choice[]" required>
            <label><input type="radio" name="correct" value="<?php echo $i; ?>" <?php echo $i===0?'checked':''; ?>> 正解にする</label>
          </div>
        <?php endfor; ?>
      </div>
      <button class="button" style="margin-top:10px" type="submit">保存</button>
    </form>
  </div>

  <div class="card">
    <h2>問題一覧</h2>
    <table class="table">
      <thead><tr><th>ID</th><th>カテゴリ</th><th>問題文</th><th>操作</th></tr></thead>
      <tbody>
      <?php foreach (($rows ?? []) as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><span class="badge"><?php echo e($r['cname']); ?></span></td>
          <td><?php echo e(mb_strimwidth($r['text'], 0, 60, '…', 'UTF-8')); ?></td>
          <td>
            <a class="button secondary" href="?action=edit&id=<?php echo (int)$r['id']; ?>">編集</a>
            <a class="button secondary" href="?action=delete&id=<?php echo (int)$r['id']; ?>" onclick="return confirm('削除しますか？');">削除</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($edit): ?>
  <div class="card">
    <h2>編集 #<?php echo (int)$edit['id']; ?></h2>
    <form method="post" action="?action=update">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
      <label class="label">カテゴリ</label>
      <select class="input" name="category_id" required>
        <?php foreach ($categories as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>" <?php echo ($edit['category_id']==$c['id'])?'selected':''; ?>><?php echo e($c['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <label class="label" style="margin-top:10px">問題文</label>
      <textarea class="input" name="text" rows="3" required><?php echo e($edit['text']); ?></textarea>
      <label class="label" style="margin-top:10px">解説（任意）</label>
      <textarea class="input" name="explanation" rows="2"><?php echo e($edit['explanation']); ?></textarea>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px">
        <?php foreach ($edit['choices'] as $i => $ch): ?>
          <div>
            <label class="label">選択肢 <?php echo $i+1; ?></label>
            <input class="input" type="text" name="choice[]" value="<?php echo e($ch['text']); ?>" required>
            <input type="hidden" name="choice_id[]" value="<?php echo (int)$ch['id']; ?>">
            <label><input type="radio" name="correct" value="<?php echo $i; ?>" <?php echo ((int)$ch['is_correct']===1)?'checked':''; ?>> 正解にする</label>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="button" style="margin-top:10px" type="submit">更新</button>
    </form>
  </div>
  <?php endif; ?>

</div>
</body>
</html>