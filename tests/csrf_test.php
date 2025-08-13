<?php
require_once __DIR__ . '/../quiz-app/includes/csrf.php';

// Token generation should be consistent within a session
$token1 = csrf_token();
assert(strlen($token1) === 64);
$token2 = csrf_token();
assert($token1 === $token2);

// verify_csrf should accept a correct token
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['_token'] = $token1;
verify_csrf();

echo "CSRF tests passed\n";
