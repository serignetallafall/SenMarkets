<?php
// ============================================================
// cart.php — SenMarket  (version autonome, zéro dépendances)
// ============================================================

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// ── CONFIG ───────────────────────────────────────────────────
define('SM_DB_HOST', '127.0.0.1');
define('SM_DB_PORT', '3306');
define('SM_DB_NAME', 'senmarket');
define('SM_DB_USER', 'root');
define('SM_DB_PASS', '');   // <- votre mot de passe MySQL

// ── CONNEXION PDO ─────────────────────────────────────────────
function smConnect() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = 'mysql:host='.SM_DB_HOST.';port='.SM_DB_PORT.';dbname='.SM_DB_NAME.';charset=utf8mb4';
    $pdo = new PDO($dsn, SM_DB_USER, SM_DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

// ── HELPERS ───────────────────────────────────────────────────
function smCartTotal() {
    $total = 0;
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += intval($item['price']) * intval($item['quantity']);
        }
    }
    return $total;
}

function smCartCount() {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) $count += intval($item['quantity']);
    return $count;
}

function smOk($extra = array()) {
    $base = array(
        'success' => true,
        'cart'    => !empty($_SESSION['cart']) ? $_SESSION['cart'] : array(),
        'count'   => smCartCount(),
        'total'   => smCartTotal(),
    );
    echo json_encode(array_merge($base, $extra));
    exit;
}

function smErr($message, $code = 400) {
    http_response_code($code);
    echo json_encode(array('success' => false, 'message' => $message));
    exit;
}

// ── ACTION ────────────────────────────────────────────────────
$action = '';
if (!empty($_GET['action']))  $action = trim($_GET['action']);
elseif (!empty($_POST['action'])) $action = trim($_POST['action']);

// ── TEST ──────────────────────────────────────────────────────
if ($action === 'test') {
    $dbStatus = 'Non testee';
    try {
        smConnect();
        $stmt = smConnect()->query("SHOW TABLES LIKE 'products'");
        $tableExists = $stmt->fetchColumn();
        if ($tableExists) {
            $count = smConnect()->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $dbStatus = 'OK - ' . $count . ' produit(s) en base';
        } else {
            $dbStatus = 'ATTENTION : table products introuvable - importez install.sql';
        }
    } catch (PDOException $e) {
        $dbStatus = 'ERREUR : ' . $e->getMessage();
    }
    echo json_encode(array(
        'success'    => true,
        'message'    => 'cart.php fonctionne !',
        'php'        => PHP_VERSION,
        'session_id' => session_id(),
        'db'         => $dbStatus,
    ));
    exit;
}

// ── GET ───────────────────────────────────────────────────────
if ($action === 'get') {
    smOk();
}

// ── ADD ───────────────────────────────────────────────────────
if ($action === 'add') {
    $product_id = intval(!empty($_POST['product_id']) ? $_POST['product_id'] : 0);
    $quantity   = intval(!empty($_POST['quantity'])   ? $_POST['quantity'] : 1);
    if ($quantity < 1) $quantity = 1;
    if ($product_id <= 0) smErr('ID de produit invalide.');

    try {
        $stmt = smConnect()->prepare(
            'SELECT id,name,price,original_price,emoji,artisan,stock FROM products WHERE id=:id AND is_active=1 LIMIT 1'
        );
        $stmt->execute(array(':id' => $product_id));
        $product = $stmt->fetch();

        if (empty($product)) smErr('Produit #'.$product_id.' introuvable.', 404);
        if (intval($product['stock']) <= 0) smErr('Produit epuise.', 409);

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = array();

        $current_qty = isset($_SESSION['cart'][$product_id]['quantity']) ? intval($_SESSION['cart'][$product_id]['quantity']) : 0;
        $new_qty = min($current_qty + $quantity, intval($product['stock']));

        $_SESSION['cart'][$product_id] = array(
            'id'             => intval($product['id']),
            'name'           => $product['name'],
            'price'          => intval($product['price']),
            'original_price' => !empty($product['original_price']) ? intval($product['original_price']) : null,
            'emoji'          => $product['emoji'],
            'artisan'        => $product['artisan'],
            'quantity'       => $new_qty,
            'stock'          => intval($product['stock']),
        );

        smOk(array('message' => $product['name'].' ajoute au panier !'));

    } catch (PDOException $e) {
        smErr('Erreur base de donnees : '.$e->getMessage(), 503);
    }
}

// ── UPDATE ────────────────────────────────────────────────────
if ($action === 'update') {
    $product_id = intval(!empty($_POST['product_id']) ? $_POST['product_id'] : 0);
    $quantity   = intval(!empty($_POST['quantity'])   ? $_POST['quantity'] : 0);
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $max = isset($_SESSION['cart'][$product_id]['stock']) ? intval($_SESSION['cart'][$product_id]['stock']) : 999;
            $_SESSION['cart'][$product_id]['quantity'] = min($quantity, $max);
        }
    }
    smOk();
}

// ── REMOVE ────────────────────────────────────────────────────
if ($action === 'remove') {
    $product_id = intval(!empty($_POST['product_id']) ? $_POST['product_id'] : 0);
    if (isset($_SESSION['cart'][$product_id])) unset($_SESSION['cart'][$product_id]);
    smOk();
}

// ── CLEAR ─────────────────────────────────────────────────────
if ($action === 'clear') {
    $_SESSION['cart'] = array();
    smOk(array('message' => 'Panier vide.'));
}

// ── INCONNUE ──────────────────────────────────────────────────
smErr('Action "' . $action . '" inconnue. Valides : test, get, add, update, remove, clear');