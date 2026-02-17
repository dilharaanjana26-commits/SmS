<?php
require_once __DIR__ . '/../../helpers/i18n.php';
require_once __DIR__ . '/../../middleware/auth.php';
$user = auth_user();
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(t('app_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="d-flex" id="wrapper">
