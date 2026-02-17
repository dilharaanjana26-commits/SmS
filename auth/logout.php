<?php
require_once __DIR__ . '/../helpers/security.php';
ensure_session_started();
session_destroy();
header('Location: /index.php');
exit;
