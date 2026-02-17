<?php require_once __DIR__ . '/../../middleware/auth.php'; require_once __DIR__ . '/../../helpers/security.php'; ?>
<!doctype html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<title>Login</title></head><body class="bg-light">
<div class="container py-5"><div class="row justify-content-center"><div class="col-md-4">
<div class="card shadow"><div class="card-body">
<h4 class="mb-3 text-center">Login</h4>
<?php foreach (pull_flashes() as $f): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
<form method="post">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label>Username</label><input class="form-control" name="username" required></div>
<div class="mb-3"><label>Password</label><input type="password" class="form-control" name="password" required></div>
<button class="btn btn-primary w-100">Sign in</button>
</form></div></div>
<div class="mt-3 text-center">
<a href="/index.php?route=auth/admin_login">Admin</a> | <a href="/index.php?route=auth/teacher_login">Teacher</a> | <a href="/index.php?route=auth/student_login">Student</a>
</div></div></div></div>
</body></html>
