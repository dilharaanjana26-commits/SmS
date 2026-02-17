<div id="page-content-wrapper" class="w-100">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container-fluid">
    <span class="fw-semibold"><?= e(t('welcome')) ?>, <?= e($user['username'] ?? 'Guest') ?></span>
    <div class="d-flex gap-2">
      <a class="btn btn-sm btn-outline-secondary" href="?lang=en">EN</a>
      <a class="btn btn-sm btn-outline-secondary" href="?lang=si">සි</a>
      <a class="btn btn-sm btn-outline-secondary" href="?lang=ta">த</a>
      <a class="btn btn-sm btn-danger" href="/index.php?route=auth/logout"><?= e(t('logout')) ?></a>
    </div>
  </div>
</nav>
<div class="container-fluid p-4">
<?php foreach (pull_flashes() as $f): ?>
<div class="alert alert-<?= e($f['type']) ?> shadow-sm"><?= e($f['message']) ?></div>
<?php endforeach; ?>
