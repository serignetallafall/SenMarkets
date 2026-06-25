<?php
// Désactiver tout affichage d'erreur natif
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Buffer + session + header
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

// Capturer TOUTES les erreurs et les retourner en JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>"PHP Error $errno: $errstr on line $errline"]);
    exit;
});

register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'Fatal: '.$e['message'].' line '.$e['line']]);
    }
});

set_exception_handler(function($e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Exception: '.$e->getMessage()]);
    exit;
});

// ── Fonctions ─────────────────────────────────────────────────
function ok($d=[]){ob_clean();echo json_encode(['success'=>true]+$d);exit;}
function err($m,$c=400){ob_clean();http_response_code($c);echo json_encode(['success'=>false,'message'=>$m]);exit;}

function db() {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=senmarket;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
    );
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        phone VARCHAR(25) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    return $pdo;
}

// ── Routing ───────────────────────────────────────────────────
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'me') {
    if (!empty($_SESSION['user']))
        ok(['loggedIn'=>true, 'user'=>$_SESSION['user']]);
    else
        ok(['loggedIn'=>false]);
}

if ($action === 'login') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) err('Email et mot de passe requis.');
    $s = db()->prepare('SELECT id,name,email,password FROM users WHERE email=? LIMIT 1');
    $s->execute([$email]);
    $u = $s->fetch();
    if (!$u || !password_verify($password, $u['password'])) err('Email ou mot de passe incorrect.', 401);
    $_SESSION['user'] = ['id'=>(int)$u['id'], 'name'=>$u['name'], 'email'=>$u['email']];
    ok(['user'=>$_SESSION['user']]);
}

if ($action === 'register') {
    $name     = trim($_POST['name']     ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $phone    = trim($_POST['phone']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';
    if (mb_strlen($name) < 2)                       err('Nom trop court (min. 2 caractères).');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) err('Adresse email invalide.');
    if (mb_strlen($password) < 6)                   err('Mot de passe trop court (min. 6 caractères).');
    if ($password !== $confirm)                      err('Les mots de passe ne correspondent pas.');
    $s = db()->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
    $s->execute([$email]);
    if ($s->fetch()) err('Cette adresse email est déjà utilisée.');
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>10]);
    $s = db()->prepare('INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)');
    $s->execute([$name, $email, $phone ?: null, $hash]);
    $_SESSION['user'] = ['id'=>(int)db()->lastInsertId(), 'name'=>$name, 'email'=>$email];
    ok(['user'=>$_SESSION['user']]);
}

if ($action === 'logout') {
    unset($_SESSION['user']);
    ok(['message'=>'Déconnecté.']);
}

if ($action === 'forgot') {
    ok(['message'=>'Un email vous a été envoyé.']);
}

err("Action inconnue: '$action'");
