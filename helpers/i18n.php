<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/security.php';

function load_lang(string $lang): array
{
    $safeLang = in_array($lang, ['en', 'si', 'ta'], true) ? $lang : DEFAULT_LANG;
    $file = __DIR__ . '/../lang/' . $safeLang . '.php';
    return file_exists($file) ? require $file : [];
}

function current_lang(): string
{
    ensure_session_started();
    if (!empty($_GET['lang']) && in_array($_GET['lang'], ['en', 'si', 'ta'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

function t(string $key): string
{
    static $cache = [];
    $lang = current_lang();
    if (!isset($cache[$lang])) {
        $cache[$lang] = load_lang($lang);
    }
    return $cache[$lang][$key] ?? $key;
}
