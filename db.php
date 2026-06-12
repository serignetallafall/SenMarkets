<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erreur PHP [$errno]: $errstr (ligne $errline)"]);
    exit;
});

// ── DB (lit config.php si présent) ───────────────────────────
$_db = null;
function getDb(): PDO {
    global $_db;
    if ($_db) return $_db;

    $host = '127.0.0.1'; $port = '3306';
    $name = 'senmarket';  $user = 'root'; $pass = '';

    if (file_exists(__DIR__ . '/config.php')) {
        $c = file_get_contents(__DIR__ . '/config.php');
        foreach (['DB_HOST'=>&$host,'DB_NAME'=>&$name,'DB_USER'=>&$user,'DB_PASS'=>&$pass] as $k=>&$v)
            if (preg_match("/define\(['\"]$k['\"\s]*,\s*['\"]([^'\"]*)['\"\s]*\)/", $c, $m)) $v = $m[1];
    }

    try {
        $_db = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        ob_clean(); http_response_code(503);
        echo json_encode(['success'=>false,'message'=>'DB: '.$e->getMessage()]); exit;
    }
    return $_db;
}

function cartTotal(): int {
    $t = 0;
    foreach ($_SESSION['cart'] ?? [] as $i) $t += (int)$i['price'] * (int)$i['quantity'];
    return $t;
}
function cartCount(): int { return (int)array_sum(array_column($_SESSION['cart'] ?? [], 'quantity')); }

function ok(array $extra = []): void {
    ob_clean();
    echo json_encode(array_merge(['success'=>true,'cart'=>$_SESSION['cart']??[],'count'=>cartCount(),'total'=>cartTotal()], $extra));
    exit;
}
function err(string $msg, int $code = 400): void {
    ob_clean(); http_response_code($code);
    echo json_encode(['success'=>false,'message'=>$msg]); exit;
}

// ── Router ────────────────────────────────────────────────────
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'get': ok(); break;

    case 'add':
        $id  = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if ($id <= 0) err('ID invalide.');
        $stmt = getDb()->prepare('SELECT id,name,price,original_price,emoji,artisan,stock FROM products WHERE id=? AND is_active=1 LIMIT 1');
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if (!$p) err("Produit #$id introuvable.", 404);
        if ($p['stock'] <= 0) err('Produit épuisé.', 409);
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $cur = (int)($_SESSION['cart'][$id]['quantity'] ?? 0);
        $_SESSION['cart'][$id] = [
            'id'=>(int)$p['id'], 'name'=>$p['name'], 'price'=>(int)$p['price'],
            'original_price'=>$p['original_price']?(int)$p['original_price']:null,
            'emoji'=>$p['emoji'], 'artisan'=>$p['artisan'],
            'quantity'=>min($cur+$qty,(int)$p['stock']), 'stock'=>(int)$p['stock'],
        ];
        ok(['message'=>"« {$p['name']} » ajouté au panier !"]);
        break;

    case 'update':
        $id  = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity']   ?? 0);
        if (isset($_SESSION['cart'][$id])) {
            if ($qty <= 0) unset($_SESSION['cart'][$id]);
            else $_SESSION['cart'][$id]['quantity'] = min($qty, (int)($_SESSION['cart'][$id]['stock'] ?? 999));
        }
        ok(); break;

    case 'remove':
        unset($_SESSION['cart'][(int)($_POST['product_id'] ?? 0)]);
        ok(); break;

    case 'clear':
        $_SESSION['cart'] = [];
        ok(['message'=>'Panier vidé.']); break;

    case 'test':
        ob_clean();
        echo json_encode(['success'=>true,'php'=>PHP_VERSION,'session'=>session_id(),
            'db'=>(function(){ try{getDb();return 'OK ✅';}catch(Exception $e){return $e->getMessage();} })()]);
        exit;

    default:
        err("Action inconnue: '$action'. Valides: get, add, update, remove, clear, test");
}