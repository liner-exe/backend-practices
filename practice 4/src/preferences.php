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
            'docs_title'     => 'Хранилище PDF-документов',
            'upload_label'   => 'Выберите PDF-файл для загрузки на сервер:',
            'upload_btn'     => 'Загрузить PDF',
            'table_id'       => 'ID',
            'table_name'     => 'Имя файла',
            'table_author'   => 'Загрузил',
            'table_date'     => 'Дата загрузки',
            'table_action'   => 'Действие',
            'download_btn'   => 'Открыть / Скачать PDF',
            'no_files'       => 'Загруженных PDF-документов пока нет.',
            'users_management' => 'Управление пользователями',
            'users_table_name' => 'Имя',
            'users_table_management' => 'Управление',
            'users_create' => 'Создать пользователя',
            'users_update' => 'Обновить пользователя',
            'users_create_btn' => 'Создать',
            'users_save_btn' => 'Сохранить',
            'users_cancel_btn' => 'Отмена',
            'reviews_management' => 'Управление отзывами',
            'reviews_table_user_id' => 'ID пользователя',
            'reviews_table_name' => 'Имя',
            'reviews_table_rating' => 'Оценка',
            'reviews_table_review' => 'Отзыв',
            'reviews_table_management' => 'Управление',
            'reviews_create' => 'Создать отзыв',
            'reviews_update' => 'Обновить отзыв',
            'reviews_form_user_id' => 'ID пользователя',
            'reviews_form_review_id' => 'ID отзыва',
            'reviews_form_rating' => 'Оценка',
            'reviews_form_text' => 'Текст отзыва',
            'reviews_create_btn' => 'Создать',
            'reviews_save_btn' => 'Сохранить',
            'reviews_cancel_btn' => 'Отмена',
            'delivery' => [
                'title' => 'Доставка — Маркетплейс',
                'heading' => 'Условия доставки маркетплейса',
                'intro' => 'Мы доставляем заказы по всей территории страны с помощью собственной курьерской службы и партнёрских логистических сервисов.',
                'methods_heading' => 'Способы получения заказа',
                'methods' => [
                    ['Курьерская доставка до двери', 'ежедневно с 09:00 до 21:00. Доступна примерка перед оплатой.'],
                    ['Пункты выдачи заказов (ПВЗ)', 'более 500 пунктов выдачи с бесплатным хранением заказа до 7 календарных дней.'],
                    ['Постаматы', 'автоматизированная выдача 24/7 по коду из SMS.'],
                ],
                'rates_heading' => 'Сроки и стоимость',
                'rates' => [
                    'По городу — от 1 до 2 рабочих дней. Бесплатно при заказе от 2 000 ₽.',
                    'В регионы — от 3 до 7 рабочих дней в зависимости от удалённости населённого пункта.',
                ],
                'returns_heading' => 'Правила возврата',
                'returns' => 'Товар надлежащего качества можно вернуть в течение 14 дней с момента получения через любой удобный пункт выдачи.',
            ],
            'contacts' => [
                'title' => 'Контакты и реквизиты — Маркетплейс',
                'heading' => 'Контакты службы поддержки',
                'phone' => 'Горячая линия (бесплатно по РФ)',
                'email' => 'Электронная почта',
                'hours' => 'Режим работы поддержки',
                'hours_value' => 'Круглосуточно, 24/7',
                'office' => 'Центральный офис',
                'address' => 'г. Москва, ул. Примерная, д. 10',
                'details_heading' => 'Юридические реквизиты',
                'company' => 'Полное наименование',
                'company_name' => 'ООО «Маркетплейс Онлайн»',
                'registration' => 'ОГРН',
                'tax' => 'ИНН / КПП',
                'bank' => 'Банк',
                'bank_name' => 'ПАО «Сбербанк»',
                'account' => 'Расчётный счёт',
                'correspondent' => 'Корреспондентский счёт',
                'bank_code' => 'БИК',
            ],
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
            'docs_title'     => 'PDF Document Storage',
            'upload_label'   => 'Select a PDF file to upload to the server:',
            'upload_btn'     => 'Upload PDF',
            'table_id'       => 'ID',
            'table_name'     => 'File Name',
            'table_author'   => 'Uploaded By',
            'table_date'     => 'Upload Date',
            'table_action'   => 'Action',
            'download_btn'   => 'Open / Download PDF',
            'no_files'       => 'No PDF documents uploaded yet.',
            'users_management' => 'Users management',
            'users_table_name' => 'Name',
            'users_table_management' => 'Management',
            'users_create' => 'Create user',
            'users_update' => 'Update user',
            'users_create_btn' => 'Create',
            'users_save_btn' => 'Save',
            'users_cancel_btn' => 'Cancel',
            'reviews_management' => 'Reviews management',
            'reviews_table_user_id' => 'User ID',
            'reviews_table_name' => 'Name',
            'reviews_table_rating' => 'Rating',
            'reviews_table_review' => 'Review',
            'reviews_table_management' => 'Management',
            'reviews_create' => 'Create review',
            'reviews_update' => 'Update review',
            'reviews_form_user_id' => 'User ID',
            'reviews_form_review_id' => 'Review ID',
            'reviews_form_rating' => 'Rating',
            'reviews_form_text' => 'Review text',
            'reviews_create_btn' => 'Create',
            'reviews_save_btn' => 'Save',
            'reviews_cancel_btn' => 'Cancel',
            'delivery' => [
                'title' => 'Delivery — Marketplace',
                'heading' => 'Delivery terms',
                'intro' => 'We deliver orders nationwide through our own courier service and logistics partners.',
                'methods_heading' => 'Delivery options',
                'methods' => [
                    ['Door-to-door delivery', 'daily from 09:00 to 21:00. You can try items on before paying.'],
                    ['Pickup points', 'more than 500 locations that hold orders for up to 7 calendar days free of charge.'],
                    ['Parcel lockers', 'self-service pickup 24/7 using a code sent by SMS.'],
                ],
                'rates_heading' => 'Delivery times and fees',
                'rates' => [
                    'Within the city — 1 to 2 business days. Free for orders of ₽2,000 or more.',
                    'To other regions — 3 to 7 business days, depending on the destination.',
                ],
                'returns_heading' => 'Returns',
                'returns' => 'Items in good condition can be returned within 14 days of receipt at any pickup point.',
            ],
            'contacts' => [
                'title' => 'Contacts and company details — Marketplace',
                'heading' => 'Customer support contacts',
                'phone' => 'Hotline (toll-free within Russia)',
                'email' => 'Email',
                'hours' => 'Support hours',
                'hours_value' => '24/7',
                'office' => 'Head office',
                'address' => '10 Primernaya St., Moscow',
                'details_heading' => 'Company details',
                'company' => 'Registered name',
                'company_name' => 'Marketplace Online LLC',
                'registration' => 'Registration number (OGRN)',
                'tax' => 'Tax identification and registration codes (INN / KPP)',
                'bank' => 'Bank',
                'bank_name' => 'Sberbank PJSC',
                'account' => 'Current account',
                'correspondent' => 'Correspondent account',
                'bank_code' => 'Bank identification code (BIK)',
            ],
        ],
    ];

    return [
        'login' => $login,
        'theme' => $theme,
        'lang'  => $lang,
        't'     => $i18n[$lang] ?? $i18n['ru'],
    ];
}

function themeClass(string $theme): string
{
    return match ($theme) {
        'dark' => 'theme-dark',
        'colorblind' => 'theme-colorblind',
        default => '',
    };
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

    $page = $_SERVER['SCRIPT_NAME'] ?? '';
    $activeUsers = $page === '/users.php' ? ' aria-current="page"' : '';
    $activeReviews = $page === '/reviews.php' ? ' aria-current="page"' : '';
    $activeDocuments = $page === '/documents.php' ? ' aria-current="page"' : '';
    $activeDelivery = $page === '/delivery.php' ? ' aria-current="page"' : '';
    $activeContacts = $page === '/contacts.php' ? ' aria-current="page"' : '';

    return <<<HTML
    <div class="pref-bar">
        <nav class="pref-links">
            <a href="/users.php"{$activeUsers}>{$t['nav_users']}</a>
            <a href="/reviews.php"{$activeReviews}>{$t['nav_reviews']}</a>
            <a href="/documents.php"{$activeDocuments}>{$t['nav_docs']}</a>
            <a href="/delivery.php"{$activeDelivery}>{$t['nav_delivery']}</a>
            <a href="/contacts.php"{$activeContacts}>{$t['nav_contacts']}</a>
        </nav>

        <form method="POST" class="pref-form">
            <input type="hidden" name="action" value="save_prefs">
            <span>{$t['greeting']}</span>
            <input type="text" name="user_login" value="{$loginEsc}" placeholder="{$t['login_ph']}">
            <select name="theme">
                <option value="light" {$selLight}>{$t['theme_light']}</option>
                <option value="dark" {$selDark}>{$t['theme_dark']}</option>
                <option value="colorblind" {$selCb}>{$t['theme_cb']}</option>
            </select>
            <select name="lang">
                <option value="ru" {$selRu}>RU</option>
                <option value="en" {$selEn}>EN</option>
            </select>
            <button type="submit">{$t['save_btn']}</button>
        </form>
    </div>
HTML;
}