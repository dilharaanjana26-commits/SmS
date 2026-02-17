<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../middleware/auth.php';
ensure_session_started();
verify_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare("SELECT * FROM users WHERE username = ? AND role='student' AND status='active' LIMIT 1");
    $stmt->execute([clean_input($_POST['username'] ?? '')]);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        $_SESSION['user'] = $u;
        header('Location: /index.php?route=student/dashboard');
        exit;
    }
    flash('danger', 'Invalid credentials');
}
include __DIR__ . '/../views/partials/login_form.php';
