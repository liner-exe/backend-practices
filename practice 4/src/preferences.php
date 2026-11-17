<?php

require_once __DIR__ . '/session_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_prefs') {
    $login = trim($_POST['user_login'] ?? '');
    $theme = $_POST['theme'] ?? 'light';
    $lang  = $_POST['lang'] ?? 'ru';

    if (!in_array($theme, ['light', 'dark', 'colorblind'], true)) {
        $theme = 'light';
    }
    if (!in_array($lang, ['ru', 'en'], true)) {
        $lang = 'ru';
    }

    $cookieExpire = time() + 60 * 60 * 24 * 30;
    setcookie('pref_login', $login, ['expires' => $cookieExpire, 'path' => '/', 'samesite' => 'Lax']);
    setcookie('pref_theme', $theme, ['expires' => $cookieExpire, 'path' => '/', 'samesite' => 'Lax']);
    setcookie('pref_lang',  $lang,  ['expires' => $cookieExpire, 'path' => '/', 'samesite' => 'Lax']);

    $_SESSION['user_login'] = $login;

    session_write_close();

    $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?') ?: '/users.php';
    header('Location: ' . $redirectUrl);
    exit;
}

function getUserPreferences(): array
{
    $login = $_COOKIE['pref_login'] ?? ($_SESSION['user_login'] ?? '');
    $theme = $_COOKIE['pref_theme'] ?? 'light';
    $lang  = $_COOKIE['pref_lang']  ?? 'ru';

    $i18n = [
        'ru' => [
            'nav_users'      => 'Пользователи',
            'nav_reviews'    => 'Отзывы',
            'nav_docs'       => 'PDF-Документы',
            'nav_delivery'   => 'Доставка',
            'nav_contacts'   => 'Контакты',
            'greeting'       => $login !== '' ? "Привет, {$login}!" : 'Вы вошли как Гость',
            'login_ph'       => 'Ваш логин',
            'theme_light'    => 'Светлая тема',
            'theme_dark'     => 'Тёмная тема',
            'theme_cb'       => 'Для цветовой слепоты',
            'save_btn'       => 'Применить',
            'docs_title'     => 'Хранилище PDF-документов (Файловая система)',
            'upload_label'   => 'Выберите PDF-файл для загрузки на сервер:',
            'upload_btn'     => 'Загрузить PDF',
            'table_id'       => 'ID',
            'table_name'     => 'Имя файла',
            'table_author'   => 'Загрузил',
            'table_date'     => 'Дата загрузки',
            'table_action'   => 'Действие',
            'download_btn'   => 'Открыть / Скачать PDF',
            'no_files'       => 'Загруженных PDF-документов пока нет.',
            'users_management' => 'Управление пользователями'
        ],
        'en' => [
            'nav_users'      => 'Users',
            'nav_reviews'    => 'Reviews',
            'nav_docs'       => 'PDF Documents',
            'nav_delivery'   => 'Delivery',
            'nav_contacts'   => 'Contacts',
            'greeting'       => $login !== '' ? "Hello, {$login}!" : 'Logged in as Guest',
            'login_ph'       => 'Your login',
            'theme_light'    => 'Light theme',
            'theme_dark'     => 'Dark theme',
            'theme_cb'       => 'Colorblind mode',
            'save_btn'       => 'Apply',
            'docs_title'     => 'PDF Document Storage (Server Filesystem)',
            'upload_label'   => 'Select a PDF file to upload to the server:',
            'upload_btn'     => 'Upload PDF',
            'table_id'       => 'ID',
            'table_name'     => 'File Name',
            'table_author'   => 'Uploaded By',
            'table_date'     => 'Upload Date',
            'table_action'   => 'Action',
            'download_btn'   => 'Open / Download PDF',
            'no_files'       => 'No PDF documents uploaded yet.',
            'users_management' => 'Users management'
        ],
    ];

    return [
        'login' => $login,
        'theme' => $theme,
        'lang'  => $lang,
        't'     => $i18n[$lang] ?? $i18n['ru'],
    ];
}

function renderThemeStyles(?string $theme = null): string
{
    $theme = $theme ?? getUserPreferences()['theme'];

    if ($theme === 'dark') {
        return '
        <style>
            body { background-color: #0f172a !important; color: #f8fafc !important; }
            .card, .pref-bar { background: #1e293b !important; color: #f8fafc !important; box-shadow: 0 4px 6px rgba(0,0,0,0.4) !important; }
            input, select { background: #334155 !important; color: #f8fafc !important; border-color: #475569 !important; }
            th { background-color: #334155 !important; color: #e2e8f0 !important; }
            td, th { border-color: #334155 !important; }
            a { color: #38bdf8 !important; }
        </style>';
    }

    if ($theme === 'colorblind') {
        return '
        <style>
            body { background-color: #ffffff !important; color: #000000 !important; }
            .card, .pref-bar { background: #fffbeb !important; border: 2px solid #000000 !important; color: #000000 !important; }
            button, .btn-primary { background-color: #004488 !important; color: #ffffff !important; border: 2px solid #000000 !important; font-weight: bold !important; }
            input, select { border: 2px solid #000000 !important; background: #ffffff !important; color: #000000 !important; }
            th { background-color: #ddaa33 !important; color: #000000 !important; border: 2px solid #000000 !important; }
            td { border: 1px solid #000000 !important; }
            a { color: #004488 !important; text-decoration: underline !important; font-weight: bold !important; }
        </style>';
    }

    return '';
}

function renderHeaderBar(?array $t = null, ?string $currentLogin = null, ?string $currentTheme = null, ?string $currentLang = null): string
{
    $prefs = getUserPreferences();
    $t            = $t ?? $prefs['t'];
    $currentLogin = $currentLogin ?? $prefs['login'];
    $currentTheme = $currentTheme ?? $prefs['theme'];
    $currentLang  = $currentLang ?? $prefs['lang'];

    $loginEsc = htmlspecialchars($currentLogin, ENT_QUOTES, 'UTF-8');
    $selLight = $currentTheme === 'light' ? 'selected' : '';
    $selDark  = $currentTheme === 'dark' ? 'selected' : '';
    $selCb    = $currentTheme === 'colorblind' ? 'selected' : '';
    $selRu    = $currentLang === 'ru' ? 'selected' : '';
    $selEn    = $currentLang === 'en' ? 'selected' : '';

    return <<<HTML
    <div class="pref-bar" style="background:#fff; padding:14px 20px; border-radius:10px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom:20px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;">
        <div style="display:flex; gap:14px; flex-wrap:wrap; font-weight:600; font-size:14px;">
            <a href="/users.php">{$t['nav_users']}</a>
            <a href="/reviews.php">{$t['nav_reviews']}</a>
            <a href="/documents.php">{$t['nav_docs']}</a>
            <a href="/delivery.html" target="_blank">{$t['nav_delivery']}</a>
            <a href="/contacts.html" target="_blank">{$t['nav_contacts']}</a>
        </div>

        <form method="POST" style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin:0; font-size:13px;">
            <input type="hidden" name="action" value="save_prefs">
            <span style="font-weight:600; margin-right:6px;">{$t['greeting']}</span>
            <input type="text" name="user_login" value="{$loginEsc}" placeholder="{$t['login_ph']}" style="padding:5px 8px; border:1px solid #cbd5e1; border-radius:4px; width:120px;">
            <select name="theme" style="padding:5px 8px; border:1px solid #cbd5e1; border-radius:4px;">
                <option value="light" {$selLight}>{$t['theme_light']}</option>
                <option value="dark" {$selDark}>{$t['theme_dark']}</option>
                <option value="colorblind" {$selCb}>{$t['theme_cb']}</option>
            </select>
            <select name="lang" style="padding:5px 8px; border:1px solid #cbd5e1; border-radius:4px;">
                <option value="ru" {$selRu}>RU</option>
                <option value="en" {$selEn}>EN</option>
            </select>
            <button type="submit" style="padding:6px 12px; background:#0284c7; color:#fff; border:none; border-radius:4px; cursor:pointer; font-weight:600;">{$t['save_btn']}</button>
        </form>
    </div>
HTML;
}