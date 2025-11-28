<?php
define('DATA_DIR', __DIR__ . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('TENANTS_FILE', DATA_DIR . '/tenants.json');
define('PROPERTIES_FILE', DATA_DIR . '/properties.json');
define('PAYMENTS_FILE', DATA_DIR . '/payments.json');

if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

function initializeDataFiles() {
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([]));
    }
    if (!file_exists(TENANTS_FILE)) {
        file_put_contents(TENANTS_FILE, json_encode([]));
    }
    if (!file_exists(PROPERTIES_FILE)) {
        file_put_contents(PROPERTIES_FILE, json_encode([]));
    }
    if (!file_exists(PAYMENTS_FILE)) {
        file_put_contents(PAYMENTS_FILE, json_encode([]));
    }
}

initializeDataFiles();

function readJsonFile($filepath) {
    if (!file_exists($filepath)) {
        return [];
    }
    $content = file_get_contents($filepath);
    return json_decode($content, true) ?: [];
}

function writeJsonFile($filepath, $data) {
    return file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
}

function generateId() {
    return uniqid('', true);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateNumeric($value) {
    return is_numeric($value) && $value >= 0;
}

function validateMonth($month) {
    return preg_match('/^\d{4}-\d{2}$/', $month);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>
