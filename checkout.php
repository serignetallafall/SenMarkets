<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration base de données
define('SM_DB_HOST', '127.0.0.1');
define('SM_DB_PORT', '3306');
define('SM_DB_NAME', 'senmarket');
define('SM_DB_USER', 'root');
define('SM_DB_PASS', '');   // Mettez votre mot de passe MySQL

// Configuration email (simplifiée pour test)
define('ADMIN_EMAIL', 'scadtdevelopers@gmail.com');

// Fonction de connexion à la base
function db() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    try {
        $dsn = 'mysql:host='.SM_DB_HOST.';port='.SM_DB_PORT.';dbname='.SM_DB_NAME.';charset=utf8mb4';
        $pdo = new PDO($dsn, SM_DB_USER, SM_DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// ── PHPMailer via Gmail SMTP ──────────────────────────────────
// Les fonctions sendOrderConfirmationToClient() et sendOrderNotificationToAdmin()
// sont définies dans mailer.php. fmt() doit être déclarée AVANT l'inclusion
// car mailer.php l'utilise dans ses closures d'email.
// → L'inclusion est faite APRÈS la déclaration de fmt() ci-dessous.

// Fonction de formatage
function fmt($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}

// Chargement PHPMailer (après fmt() car mailer.php l'utilise)
require_once __DIR__ . '/mailer.php';

// Initialisation
$cart = $_SESSION['cart'] ?? [];
$errors = [];
$success = false;
$order = null;

// Vérifier si le panier contient des produits valides
if (!empty($cart)) {
    foreach ($cart as $key => $item) {
        if (!isset($item['price']) || !isset($item['quantity'])) {
            unset($_SESSION['cart'][$key]);
        }
    }
    $cart = $_SESSION['cart'] ?? [];
}

// ── SOUMISSION DU FORMULAIRE ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    
    // Debug : log des données reçues
    error_log("=== SOUMISSION COMMANDE ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Cart data: " . print_r($cart, true));
    
    // 1. Validation
    $fields = [
        'client_name'      => 'Nom complet',
        'client_email'     => 'Email',
        'client_phone'     => 'Téléphone',
        'shipping_address' => 'Adresse de livraison',
        'shipping_city'    => 'Ville',
        'shipping_country' => 'Pays',
        'payment_method'   => 'Mode de paiement',
    ];

    foreach ($fields as $key => $label) {
        if (empty(trim($_POST[$key] ?? ''))) {
            $errors[$key] = "Le champ « $label » est requis.";
        }
    }

    if (!empty($_POST['client_email']) && !filter_var($_POST['client_email'], FILTER_VALIDATE_EMAIL)) {
        $errors['client_email'] = 'Adresse email invalide.';
    }

    $allowedPayments = ['orange_money', 'wave', 'card', 'cash'];
    if (!in_array($_POST['payment_method'] ?? '', $allowedPayments, true)) {
        $errors['payment_method'] = 'Mode de paiement invalide.';
    }

    if (empty($cart)) {
        $errors['cart'] = 'Votre panier est vide.';
    }

    error_log("Validation errors: " . print_r($errors, true));

    // 2. Si pas d'erreurs → insérer en base + envoyer email
    if (empty($errors)) {
        try {
            $pdo = db();
            
            // Calcul des totaux
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $shippingCost = (strtolower(trim($_POST['shipping_country'])) === 'sénégal' || 
                            strtolower(trim($_POST['shipping_country'])) === 'senegal') ? 0 : 5000;
            $total = $subtotal + $shippingCost;
            $reference = 'SM-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
            
            error_log("Commande calculée - Subtotal: $subtotal, Shipping: $shippingCost, Total: $total, Ref: $reference");
            
            // ⚠️ Les CREATE TABLE sont exécutés AVANT beginTransaction()
            // MySQL fait un commit implicite sur tout DDL (CREATE/ALTER/DROP).
            // Si on les laisse DANS la transaction, elle est invalidée immédiatement
            // et le rollBack() suivant lève "There is no active transaction".
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    reference VARCHAR(50) NOT NULL UNIQUE,
                    status VARCHAR(50) DEFAULT 'pending',
                    payment_method VARCHAR(50),
                    client_name VARCHAR(255),
                    client_email VARCHAR(255),
                    client_phone VARCHAR(50),
                    shipping_address TEXT,
                    shipping_city VARCHAR(100),
                    shipping_country VARCHAR(100),
                    subtotal INT,
                    shipping_cost INT,
                    total INT,
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS order_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    product_id INT,
                    product_name VARCHAR(255),
                    unit_price INT,
                    quantity INT,
                    subtotal INT,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
                )
            ");

            // ✅ Transaction démarrée ICI — uniquement des INSERT/UPDATE après ce point
            $pdo->beginTransaction();

            // Insérer la commande
            $stmt = $pdo->prepare("
                INSERT INTO orders
                    (reference, status, payment_method, client_name, client_email, client_phone,
                     shipping_address, shipping_city, shipping_country, subtotal, shipping_cost, total, notes)
                VALUES
                    (:ref, 'pending', :payment, :name, :email, :phone,
                     :address, :city, :country, :subtotal, :shipping, :total, :notes)
            ");
            
            // Les données sont insérées brutes en BDD (PDO paramétré protège des injections SQL)
            // htmlspecialchars() est appliqué uniquement à l'affichage HTML
            $params = [
                ':ref'      => $reference,
                ':payment'  => $_POST['payment_method'],
                ':name'     => trim($_POST['client_name']),
                ':email'    => strtolower(trim($_POST['client_email'])),
                ':phone'    => trim($_POST['client_phone']),
                ':address'  => trim($_POST['shipping_address']),
                ':city'     => trim($_POST['shipping_city']),
                ':country'  => trim($_POST['shipping_country']),
                ':subtotal' => $subtotal,
                ':shipping' => $shippingCost,
                ':total'    => $total,
                ':notes'    => trim($_POST['notes'] ?? ''),
            ];
            
            error_log("Insertion commande avec params: " . print_r($params, true));
            $stmt->execute($params);
            
            $orderId = (int)$pdo->lastInsertId();
            error_log("Commande insérée avec ID: $orderId");

            // Insérer les lignes
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal)
                VALUES (:order_id, :product_id, :name, :price, :qty, :subtotal)
            ");
            
            foreach ($cart as $item) {
                $itemParams = [
                    ':order_id'   => $orderId,
                    ':product_id' => $item['id'] ?? null,   // null si absent du panier session
                    ':name'       => $item['name'],
                    ':price'      => $item['price'],
                    ':qty'        => $item['quantity'],
                    ':subtotal'   => $item['price'] * $item['quantity'],
                ];
                error_log("Insertion item: " . print_r($itemParams, true));
                $itemStmt->execute($itemParams);
            }
            
            $pdo->commit();
            error_log("Transaction commitée avec succès");
            
            // ── Préparer les données pour emails et affichage ────────────
            $orderData = [
                'id'               => $orderId,
                'reference'        => $reference,
                'client_name'      => trim($_POST['client_name']),
                'client_email'     => strtolower(trim($_POST['client_email'])),
                'client_phone'     => trim($_POST['client_phone']),
                'shipping_address' => trim($_POST['shipping_address']),
                'shipping_city'    => trim($_POST['shipping_city']),
                'shipping_country' => trim($_POST['shipping_country']),
                'payment_method'   => $_POST['payment_method'],
                'subtotal'         => $subtotal,
                'shipping_cost'    => $shippingCost,
                'total'            => $total,
                'items'            => $cart,
                'notes'            => trim($_POST['notes'] ?? ''),
            ];
            
            sendOrderConfirmationToClient($orderData);
            sendOrderNotificationToAdmin($orderData);
            
            // Vider le panier
            $_SESSION['cart'] = [];
            $cart = [];
            
            $success = true;
            $order = $orderData;
            
            error_log("Commande finalisée avec succès !");
            
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[ERREUR PDO] ' . $e->getMessage());
            error_log('[ERREUR PDO] Trace: ' . $e->getTraceAsString());
            $errors['db'] = 'Erreur base de données : ' . $e->getMessage();
        } catch (Exception $e) {
            error_log('[ERREUR GENERALE] ' . $e->getMessage());
            error_log('[ERREUR GENERALE] Trace: ' . $e->getTraceAsString());
            $errors['db'] = 'Erreur : ' . $e->getMessage();
        }
    }
}

// ── Recalcul pour l'affichage ─────────────────────────────────
$cartCount = 0;
$cartSubtotal = 0;
foreach ($cart as $item) {
    $cartCount += $item['quantity'];
    $cartSubtotal += $item['price'] * $item['quantity'];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panier & Commande — SenMarket</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    :root { --sen-green:#008751; --sen-red:#CE1126; --sen-yellow:#FDEF42; }
    body { font-family:'Inter',sans-serif; background:#f8f9fa; }
    h1,h2,h3,h4,h5 { font-family:'Poppins',sans-serif; }

    .checkout-header { background:#141718; padding:16px 32px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:15px; }
    .checkout-logo { color:#fff; font-family:'Poppins',sans-serif; font-size:1.4rem; font-weight:500; text-decoration:none; }
    .checkout-logo span { color:var(--sen-yellow); }
    .checkout-steps { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .step { display:flex; align-items:center; gap:6px; font-size:.8rem; font-weight:500; color:rgba(255,255,255,.45); }
    .step.active { color:#fff; }
    .step.done { color:var(--sen-green); }
    .step-num { width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;background:rgba(255,255,255,.1); }
    .step.active .step-num { background:var(--sen-green);color:#fff; }
    .step.done .step-num { background:var(--sen-green);color:#fff; }
    .step-sep { color:rgba(255,255,255,.2); }

    .cart-item { background:#fff;border-radius:12px;padding:16px;margin-bottom:12px;border:1px solid #e8ecef; }
    .cart-item-emoji { width:72px;height:72px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden; }
    .qty-control { display:flex;align-items:center;gap:8px;background:#f3f5f7;border-radius:8px;padding:4px 10px; }
    .qty-btn { width:28px;height:28px;border:none;background:transparent;cursor:pointer;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:background .2s; }
    .qty-btn:hover { background:#e0e0e0; }

    .checkout-card { background:#fff;border-radius:16px;padding:28px;border:1px solid #e8ecef;margin-bottom:24px; }
    .form-control, .form-select { border-radius:10px; border-color:#e8ecef; padding:12px 16px; font-size:.9rem; }
    .form-control:focus, .form-select:focus { border-color:var(--sen-green); box-shadow:0 0 0 3px rgba(0,135,81,.1); }
    .form-label { font-weight:500; font-size:.85rem; color:#343839; margin-bottom:6px; }
    .is-invalid { border-color:var(--sen-red)!important; }
    .invalid-feedback { font-size:.8rem; }

    .payment-option { border:2px solid #e8ecef;border-radius:12px;padding:14px 16px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:12px; }
    .payment-option:hover { border-color:var(--sen-green);background:rgba(0,135,81,.03); }
    input[name="payment_method"]:checked + .payment-option { border-color:var(--sen-green);background:rgba(0,135,81,.06); }
    .payment-icon { width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1rem; }
    .payment-label { font-weight:600;font-size:.9rem; }
    .payment-sub { font-size:.75rem;color:#6c7275; }

    .summary-row { display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0f0f0; }
    .summary-row:last-child { border-bottom:none; }
    .summary-total { font-size:1.2rem;font-weight:700;color:#141718; }

    .success-card { background:#fff;border-radius:20px;padding:48px 32px;text-align:center;border:1px solid #e8ecef;margin:20px auto;max-width:600px; }
    .success-icon { width:80px;height:80px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:2.5rem; }
    .order-ref { background:#f3f5f7;border-radius:10px;padding:12px 24px;font-family:'Poppins',sans-serif;font-size:1.1rem;font-weight:600;display:inline-block; }

    @media (min-width:992px) { .summary-sticky { position:sticky;top:90px; } }
    .alert-danger { background-color:#fee;border-color:#fcc;color:#c00; }
  </style>
</head>
<body>

<header class="checkout-header">
  <a href="index.html" class="checkout-logo">SenMarket<span>.</span></a>
  <div class="checkout-steps d-none d-md-flex">
    <div class="step done">
      <div class="step-num"><i class="bi bi-check"></i></div>
      <span>Panier</span>
    </div>
    <span class="step-sep">›</span>
    <div class="step <?= $success ? 'done' : 'active' ?>">
      <div class="step-num">2</div>
      <span>Informations</span>
    </div>
    <span class="step-sep">›</span>
    <div class="step <?= $success ? 'active' : '' ?>">
      <div class="step-num">3</div>
      <span>Confirmation</span>
    </div>
  </div>
  <a href="index.html" class="btn btn-sm btn-outline-light rounded-3">
    <i class="bi bi-arrow-left"></i> Continuer mes achats
  </a>
</header>

<div class="container-lg py-4 py-md-5">

  <!-- Affichage des erreurs PHP (debug) -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <strong>Erreurs :</strong>
      <ul class="mb-0 mt-2">
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- ── SUCCÈS ─────────────────────────────────────────────── -->
  <?php if ($success && $order): ?>
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="success-card">
        <div class="success-icon">✅</div>
        <h2 class="fw-semibold mb-2">Commande confirmée !</h2>
        <p class="text-muted mb-4">Merci <strong><?= htmlspecialchars($order['client_name']) ?></strong> ! Votre commande a bien été enregistrée.</p>
        <p class="mb-1 text-muted small">Référence de commande</p>
        <div class="order-ref mb-4"><?= htmlspecialchars($order['reference']) ?></div>
        <p class="text-muted small mb-4">
          Un email de confirmation a été envoyé à <strong><?= htmlspecialchars($order['client_email']) ?></strong>.<br />
          Vous serez contacté par notre équipe sous 24h pour les détails de livraison.
        </p>

        <div class="text-start border rounded-3 p-3 mb-4">
          <p class="fw-semibold small mb-2">Récapitulatif :</p>
          <?php foreach ($order['items'] as $item): ?>
            <div class="d-flex justify-content-between small py-1 border-bottom">
              <span>
                <?php if (!empty($item['image'])): ?>
                  <img src="<?= htmlspecialchars($item['image']) ?>" alt="" style="width:28px;height:28px;object-fit:cover;border-radius:4px;vertical-align:middle;margin-right:4px;">
                <?php endif; ?>
                <?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?>
              </span>
              <span class="fw-medium"><?= fmt($item['price'] * $item['quantity']) ?></span>
            </div>
          <?php endforeach; ?>
          <?php if ($order['shipping_cost'] > 0): ?>
            <div class="d-flex justify-content-between small py-1 border-bottom">
              <span>Livraison internationale</span>
              <span><?= fmt($order['shipping_cost']) ?></span>
            </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between fw-bold mt-2">
            <span>Total</span>
            <span style="color:var(--sen-green)"><?= fmt($order['total']) ?></span>
          </div>
        </div>

        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="index.html" class="btn btn-dark rounded-3 px-4 py-2 fw-semibold">
            <i class="bi bi-house me-2"></i>Retour à l'accueil
          </a>
          <a href="index.html#boutique" class="btn btn-outline-dark rounded-3 px-4 py-2 fw-semibold">
            <i class="bi bi-bag me-2"></i>Continuer mes achats
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php elseif (empty($cart)): ?>
    <div class="alert alert-warning rounded-3 border-0 text-center">
      <i class="bi bi-cart-x fs-4 d-block mb-2"></i>
      Votre panier est vide.
      <a href="index.html" class="fw-semibold text-dark d-block mt-2">Retourner à la boutique →</a>
    </div>

  <?php else: ?>

  <div class="row g-4 g-lg-5">

    <!-- Colonne gauche : panier + formulaire -->
    <div class="col-lg-7">

      <!-- ═══ PANIER ═══ -->
      <div class="checkout-card">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="fw-semibold m-0">
            <i class="bi bi-bag me-2" style="color:var(--sen-green)"></i>
            Mon panier
            <span class="badge bg-dark rounded-pill ms-2" style="font-size:.75rem"><?= $cartCount ?></span>
          </h4>
          <a href="cart.php?action=clear" class="text-muted small text-decoration-none"
             onclick="return confirm('Vider le panier ?')">
            <i class="bi bi-trash3 me-1"></i>Vider
          </a>
        </div>

        <?php foreach ($cart as $id => $item): ?>
          <div class="cart-item d-flex gap-3 align-items-start" id="item-<?= $id ?>">
            <div class="cart-item-emoji overflow-hidden" style="background:#f3f5f7;border-radius:10px;flex-shrink:0;">
              <?php if (!empty($item['image'])): ?>
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <i class="bi bi-image text-muted" style="font-size:1.8rem"></i>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <p class="fw-semibold mb-0" style="font-size:.95rem"><?= htmlspecialchars($item['name']) ?></p>
                <button class="btn btn-sm p-0 text-muted ms-2" onclick="removeItem(<?= $id ?>)" title="Supprimer">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
              <p class="text-muted small mb-2">par <?= htmlspecialchars($item['artisan'] ?? 'Artisan local') ?></p>
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="qty-control">
                  <button class="qty-btn" onclick="updateQty(<?= $id ?>, <?= $item['quantity'] - 1 ?>)">−</button>
                  <span class="fw-medium px-1" id="qty-<?= $id ?>"><?= $item['quantity'] ?></span>
                  <button class="qty-btn" onclick="updateQty(<?= $id ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                </div>
                <div class="text-end">
                  <p class="fw-bold mb-0" id="price-<?= $id ?>" style="color:var(--sen-green)">
                    <?= fmt($item['price'] * $item['quantity']) ?>
                  </p>
                  <p class="text-muted small mb-0"><?= fmt($item['price']) ?> / unité</p>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- ═══ FORMULAIRE ═══ -->
      <form method="POST" action="" id="orderForm" novalidate>
        <input type="hidden" name="submit_order" value="1" />

        <div class="checkout-card">
          <h4 class="fw-semibold mb-4">
            <i class="bi bi-person-circle me-2" style="color:var(--sen-green)"></i>Vos coordonnées
          </h4>
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label">Nom complet *</label>
              <input type="text" name="client_name" class="form-control <?= isset($errors['client_name']) ? 'is-invalid' : '' ?>"
                     value="<?= htmlspecialchars($_POST['client_name'] ?? '') ?>"
                     placeholder="Prénom & Nom" required />
            </div>
            <div class="col-sm-6">
              <label class="form-label">Adresse email *</label>
              <input type="email" name="client_email" class="form-control <?= isset($errors['client_email']) ? 'is-invalid' : '' ?>"
                     value="<?= htmlspecialchars($_POST['client_email'] ?? '') ?>"
                     placeholder="utilisateur@email.com" required />
            </div>
            <div class="col-sm-6">
              <label class="form-label">Téléphone *</label>
              <input type="tel" name="client_phone" class="form-control <?= isset($errors['client_phone']) ? 'is-invalid' : '' ?>"
                     value="<?= htmlspecialchars($_POST['client_phone'] ?? '') ?>"
                     placeholder="+221 7X XXX XX XX" required />
            </div>
          </div>
        </div>

        <div class="checkout-card">
          <h4 class="fw-semibold mb-4">
            <i class="bi bi-truck me-2" style="color:var(--sen-green)"></i>Adresse de livraison
          </h4>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Adresse complète *</label>
              <input type="text" name="shipping_address" class="form-control <?= isset($errors['shipping_address']) ? 'is-invalid' : '' ?>"
                     value="<?= htmlspecialchars($_POST['shipping_address'] ?? '') ?>"
                     placeholder="Rue, quartier, numéro..." required />
            </div>
            <div class="col-sm-6">
              <label class="form-label">Ville *</label>
              <input type="text" name="shipping_city" class="form-control <?= isset($errors['shipping_city']) ? 'is-invalid' : '' ?>"
                     value="<?= htmlspecialchars($_POST['shipping_city'] ?? '') ?>"
                     placeholder="Dakar" required />
            </div>
            <div class="col-sm-6">
              <label class="form-label">Pays *</label>
              <select name="shipping_country" class="form-select" id="countrySelect">
                <option value="Sénégal" selected>Sénégal 🇸🇳</option>
                <option value="France">France</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Instructions spéciales</label>
              <textarea name="notes" class="form-control" rows="2"
                        placeholder="Précisions pour la livraison..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="checkout-card">
          <h4 class="fw-semibold mb-4">
            <i class="bi bi-credit-card me-2" style="color:var(--sen-green)"></i>Mode de paiement
          </h4>
          <div class="row g-3">
            <?php
            $payments = [
              'orange_money' => ['img'=>'images/orange_money.png',  'label'=>'Orange Money',           'sub'=>'Paiement mobile sécurisé', 'bg'=>'#FF6600'],
              'wave'         => ['img'=>'images/wave.png',          'label'=>'Wave',                   'sub'=>'Transfert instantané',     'bg'=>'#00BFFF'],
              'card'         => ['img'=>'images/carte_bancaire.png','label'=>'Carte bancaire',          'sub'=>'Visa, Mastercard',         'bg'=>'#2D3748'],
              'cash'         => ['img'=>'images/livraison.jpeg',    'label'=>'Paiement à la livraison','sub'=>'Sénégal uniquement',        'bg'=>'#008751'],
            ];
            foreach ($payments as $value => $p):
              $checked = ($_POST['payment_method'] ?? 'orange_money') === $value ? 'checked' : '';
            ?>
              <div class="col-sm-6">
                <label style="cursor:pointer;display:block">
                  <input type="radio" name="payment_method" value="<?= $value ?>" class="d-none" <?= $checked ?> required />
                  <div class="payment-option">
                    <div class="payment-icon" style="background:<?= $p['bg'] ?>20;overflow:hidden;padding:4px;">
                      <img src="<?= $p['img'] ?>" alt="<?= $p['label'] ?>" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    <div>
                      <p class="payment-label mb-0"><?= $p['label'] ?></p>
                      <p class="payment-sub mb-0"><?= $p['sub'] ?></p>
                    </div>
                  </div>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="btn w-100 py-3 fw-semibold rounded-3 fs-5"
                style="background:var(--sen-green);color:#fff;border:none">
          <i class="bi bi-lock-fill me-2"></i>
          Confirmer ma commande — <span id="btnTotal"><?= fmt($cartSubtotal) ?></span>
        </button>
        <p class="text-center text-muted small mt-2">
          <i class="bi bi-shield-lock me-1"></i>Paiement 100% sécurisé
        </p>
      </form>
    </div>

    <!-- Colonne droite : récapitulatif -->
    <div class="col-lg-5">
      <div class="checkout-card summary-sticky">
        <h4 class="fw-semibold mb-4">Récapitulatif</h4>

        <?php foreach ($cart as $item): ?>
          <div class="summary-row">
            <span class="small d-flex align-items-center gap-1">
              <?php if (!empty($item['image'])): ?>
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="" style="width:22px;height:22px;object-fit:cover;border-radius:3px;flex-shrink:0;">
              <?php endif; ?>
              <?= htmlspecialchars(mb_strimwidth($item['name'], 0, 30, '…')) ?> ×<?= $item['quantity'] ?>
            </span>
            <span class="small fw-medium"><?= fmt($item['price'] * $item['quantity']) ?></span>
          </div>
        <?php endforeach; ?>

        <div class="summary-row">
          <span class="text-muted small">Sous-total</span>
          <span class="small fw-medium"><?= fmt($cartSubtotal) ?></span>
        </div>
        <div class="summary-row">
          <span class="text-muted small">Livraison</span>
          <span class="small fw-medium text-success" id="summaryShipping">Gratuite 🎉</span>
        </div>
        <div class="summary-row py-3">
          <span class="fw-semibold">Total à payer</span>
          <span class="summary-total" style="color:var(--sen-green)"><?= fmt($cartSubtotal) ?></span>
        </div>
      </div>
    </div>

  </div>
  <?php endif; ?>

</div>

<script>
function updateQty(productId, newQty) {
  if (newQty < 0) return;
  fetch('cart.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=update&product_id=${productId}&quantity=${newQty}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      location.reload(); // Recharger la page pour mettre à jour
    }
  });
}

function removeItem(productId) {
  if (confirm('Supprimer cet article ?')) {
    fetch('cart.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: `action=remove&product_id=${productId}`
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        location.reload();
      }
    });
  }
}

// Mettre à jour le total selon le pays
const countrySelect = document.getElementById('countrySelect');
const summaryShipping = document.getElementById('summaryShipping');
const baseSubtotal = <?= $cartSubtotal ?>;

function updateShipping() {
  if (!countrySelect || !summaryShipping) return;
  const isLocal = countrySelect.value.toLowerCase().includes('sénégal') || countrySelect.value.toLowerCase().includes('senegal');
  const shippingCost = isLocal ? 0 : 5000;
  const total = baseSubtotal + shippingCost;
  const formatted = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
  summaryShipping.textContent = isLocal ? 'Gratuite 🎉' : '5 000 FCFA';
  document.querySelector('.summary-total').textContent = formatted;
  const btnTotal = document.getElementById('btnTotal');
  if (btnTotal) btnTotal.textContent = formatted;
}

if (countrySelect) {
  countrySelect.addEventListener('change', updateShipping);
}

// Style des options de paiement
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.payment-option').forEach(el => {
      el.style.borderColor = '#e8ecef';
      el.style.background = '';
    });
    if (this.checked) {
      const opt = this.nextElementSibling;
      opt.style.borderColor = '#008751';
      opt.style.background = 'rgba(0,135,81,.06)';
    }
  });
  if (radio.checked) {
    radio.nextElementSibling.style.borderColor = '#008751';
    radio.nextElementSibling.style.background = 'rgba(0,135,81,.06)';
  }
});
</script>
</body>
</html>