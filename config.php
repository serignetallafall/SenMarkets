<?php


define('DB_HOST',     '127.0.0.1');
define('DB_PORT',     '3306');
define('DB_NAME',     'senmarket');
define('DB_USER',     'root');
define('DB_PASS',     '');          // ← votre mot de passe MySQL
define('DB_CHARSET',  'utf8mb4');

// ── Email ────────────────────────────────────────────────────
define('MAIL_FROM',       'scadtdevelopers@gmail.com');
define('MAIL_FROM_NAME',  'SenMarket');
define('ADMIN_EMAIL',     'scadtdevelopers@gmail.com'); // reçoit une copie

// ── SMTP (si vous utilisez PHPMailer) ──────────────────────
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     'votre@gmail.com');   // ← votre adresse Gmail
define('SMTP_PASS',     'votre_app_password'); // ← mot de passe d'application Google
define('SMTP_SECURE',   'tls');

// ── App ──────────────────────────────────────────────────────
define('APP_URL',  'http://localhost');
define('APP_NAME', 'SenMarket');

// ── Session ──────────────────────────────────────────────────
// session_start() est géré par chaque fichier PHP individuellement