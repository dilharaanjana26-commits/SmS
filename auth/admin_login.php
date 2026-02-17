<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../middleware/auth.php';
ensure_session_started();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' AND status = 'active' LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = $user;
        db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
        header('Location: /index.php?route=admin/dashboard');
        exit;
    }
    flash('danger', 'Invalid credentials');
}
include __DIR__ . '/../views/partials/login_form.php';
