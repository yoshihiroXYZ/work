<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/csrf.php';
session_start();

verify_csrf();

$pdo = pdo();
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : ($_SESSION['quiz']['category_id'] ?? 0);
if ($category_id <= 0) {
    header('Location: ' . base_path('index.php'));
    exit;
}

// 初回アクセス時にセッション初期化
if (empty($_SESSION['quiz']) || ($_SESSION['quiz']['category_id'] ?? null) !== $category_id) {
    // 存在チェック
    $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = ?');
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    if (!$category) {
        exit('カテゴリが見つかりません');
    }

    // 10問までランダム
    $q = $pdo->prepare('SELECT id FROM questions WHERE category_id = ? ORDER BY RAND() LIMIT 10');
    $q->execute([$category_id]);
    $question_ids = array_column($q->fetchAll(), 'id');

    if (empty($question_ids)) {
        exit('このカテゴリには問題がありません。');
    }

    $_SESSION['quiz'] = [
        'category_id' => $category_id,
        'category_name' => $category['name'],
        'questions' => $question_ids,
        'index' => 0,
        'score' => 0,
        'last_feedback' => null,
    ];
}

$state = &$_SESSION['quiz'];

// 回答処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['choice_id'])) {
    $choice_id = (int)$_POST['choice_id'];

    // 現在の問題 ID
    $current_qid = $state['questions'][$state['index']] ?? null;
    if (!$current_qid) {
        header('Location: ' . base_path('index.php'));
        exit;
    }

    // 回答の正誤判定
    $stmt = $pdo->prepare('SELECT c.id, c.text, c.is_correct, q.text AS qtext, q.explanation FROM choices c JOIN questions q ON q.id=c.question_id WHERE c.id = ? AND c.question_id = ?');
    $stmt->execute([$choice_id, $current_qid]);
    $row = $stmt->fetch();

    if ($row) {
        // 正解選択肢のテキスト
        $stmt2 = $pdo->prepare('SELECT text FROM choices WHERE question_id = ? AND is_correct = 1 LIMIT 1');
        $stmt2->execute([$current_qid]);
        $correct_text = $stmt2->fetchColumn();

        $correct = (int)$row['is_correct'] === 1;
        if ($correct) {
            $state['score']++;
        }
        $state['last_feedback'] = [
            'question' => $row['qtext'],
            'is_correct' => $correct,
            'your' => $row['text'],
            'correct' => $correct_text ?: '',
            'explanation' => $row['explanation'] ?? ''
        ];

        // 次の問題へインデックスを進める
        $state['index']++;

        // フィードバック表示へ
        header('Location: ' . base_path('quiz.php'));
        exit;
    }
}

// 問題取得（まだ残っていれば）
$current_qid = $state['questions'][$state['index']] ?? null;
$finished = $current_qid === null;

// 現在問題のデータ
$question = null;
$choices = [];
if (!$finished) {
    $stmt = $pdo->prepare('SELECT id, text FROM questions WHERE id = ?');
    $stmt->execute([$current_qid]);
    $question = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT id, text FROM choices WHERE question_id = ? ORDER BY RAND()');
    $stmt->execute([$current_qid]);
    $choices = $stmt->fetchAll();
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>クイズ — <?php echo e($state['category_name']); ?></title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="brand">📘 クイズ — <?php echo e($state['category_name']); ?></div>
      <nav>
        <a class="button secondary" href="<?php echo e(base_path('index.php')); ?>">カテゴリへ戻る</a>
      </nav>
    </header>

    <?php if ($state['last_feedback']): $fb = $state['last_feedback']; ?>
      <div class="notice">
        <strong><?php echo $fb['is_correct'] ? '正解！' : '不正解'; ?></strong><br>
        <div style="margin-top:6px">Q: <?php echo e($fb['question']); ?></div>
        <?php if (!$fb['is_correct']): ?>
          <div>あなたの回答：<?php echo e($fb['your']); ?></div>
          <div>正しい答え：<strong><?php echo e($fb['correct']); ?></strong></div>
        <?php endif; ?>
        <?php if ($fb['explanation']): ?>
          <div style="margin-top:6px">解説：<?php echo e($fb['explanation']); ?></div>
        <?php endif; ?>
      </div>
      <?php $state['last_feedback'] = null; endif; ?>

    <?php if ($finished): ?>
      <div class="card">
        <h2>結果</h2>
        <p>スコア：<strong><?php echo (int)$state['score']; ?></strong> / <?php echo count($state['questions']); ?></p>
        <a class="button" href="<?php echo e(base_path('index.php')); ?>">トップへ</a>
      </div>
      <?php $_SESSION['quiz'] = null; unset($_SESSION['quiz']); ?>
    <?php else: ?>
      <div class="card">
        <div class="badge">問題 <?php echo (int)($state['index'] + 1); ?> / <?php echo count($state['questions']); ?></div>
        <h2><?php echo e($question['text']); ?></h2>
        <form method="post">
          <?php echo csrf_field(); ?>
          <?php foreach ($choices as $ch): ?>
            <div style="margin:8px 0">
              <label>
                <input type="radio" name="choice_id" value="<?php echo (int)$ch['id']; ?>" required>
                <?php echo e($ch['text']); ?>
              </label>
            </div>
          <?php endforeach; ?>
          <button class="button" type="submit">回答する</button>
        </form>
      </div>
    <?php endif; ?>

    <footer class="footer">© Quiz App</footer>
  </div>
</body>
</html>