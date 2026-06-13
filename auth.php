<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

function db(): PDO {
    static $p = null;
    if ($p) return $p;
    $p = new PDO('mysql:host=127.0.0.1;port=3306;dbname=senmarket;charset=utf8mb4','root','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $p->exec("CREATE TABLE IF NOT EXISTS `users`(
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) NOT NULL UNIQUE,
        `phone` VARCHAR(25) DEFAULT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    return $p;
}
function ok($d=[]){ob_clean();echo json_encode(['success'=>true]+$d);exit;}
function err($m,$c=400){ob_clean();http_response_code($c);echo json_encode(['success'=>false,'message'=>$m]);exit;}

try {
    $a = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($a==='me') {
        !empty($_SESSION['user']) ? ok(['loggedIn'=>true,'user'=>$_SESSION['user']]) : ok(['loggedIn'=>false]);
    }
    if ($a==='login') {
        $e=strtolower(trim($_POST['email']??'')); $p=$_POST['password']??'';
        if(!$e||!$p) err('Champs requis.');
        $s=db()->prepare('SELECT id,name,email,password FROM users WHERE email=? LIMIT 1');
        $s->execute([$e]); $u=$s->fetch();
        if(!$u||!password_verify($p,$u['password'])) err('Email ou mot de passe incorrect.',401);
        $_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email']];
        ok(['user'=>$_SESSION['user']]);
    }
    if ($a==='register') {
        $n=trim($_POST['name']??''); $e=strtolower(trim($_POST['email']??''));
        $ph=trim($_POST['phone']??''); $p=$_POST['password']??''; $c=$_POST['confirm']??'';
        if(mb_strlen($n)<2) err('Nom trop court.');
        if(!filter_var($e,FILTER_VALIDATE_EMAIL)) err('Email invalide.');
        if(mb_strlen($p)<6) err('Mot de passe : min. 6 caractères.');
        if($p!==$c) err('Mots de passe différents.');
        $s=db()->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $s->execute([$e]); if($s->fetch()) err('Email déjà utilisé.');
        $h=password_hash($p,PASSWORD_BCRYPT,['cost'=>10]);
        $s=db()->prepare('INSERT INTO users(name,email,phone,password)VALUES(?,?,?,?)');
        $s->execute([$n,$e,$ph?:null,$h]);
        $_SESSION['user']=['id'=>(int)db()->lastInsertId(),'name'=>$n,'email'=>$e];
        ok(['user'=>$_SESSION['user']]);
    }
    if ($a==='logout') { unset($_SESSION['user']); ok(); }
    if ($a==='forgot') { ok(['message'=>'Si ce compte existe, un email vous a été envoyé.']); }
    err("Action inconnue: '$a'");

} catch (PDOException $e) {
    err('DB: '.$e->getMessage(), 503);
} catch (Throwable $e) {
    err('Erreur: '.$e->getMessage(), 500);
}
