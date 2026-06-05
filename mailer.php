<?php
// mailer.php — Configuration PHPMailer + Gmail pour SenMarket
// À inclure dans checkout.php avec : require_once 'mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

// ══════════════════════════════════════════════════════════════
//  CONFIGURATION GMAIL SMTP  ←  Modifiez ces 3 lignes
// ══════════════════════════════════════════════════════════════
define('MAIL_USER', 'serignetallafall83@gmail.com');          // Votre adresse Gmail
define('MAIL_PASS', 'spbz ocos ioel natf');             // Mot de passe d'application (16 cars)
define('MAIL_FROM_NAME', 'SenMarket');                  // Nom affiché dans les emails
// ══════════════════════════════════════════════════════════════

/**
 * Crée et retourne une instance PHPMailer prête à l'emploi.
 */
function createMailer(): PHPMailer {
    $mail = new PHPMailer(true); // true = active les exceptions

    // Serveur SMTP Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Expéditeur par défaut
    $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
    $mail->addReplyTo(MAIL_USER, MAIL_FROM_NAME);

    return $mail;
}

/**
 * Envoie l'email de confirmation de commande au client.
 *
 * @param  array $orderData  Données de la commande (issues de checkout.php)
 * @return bool              true si envoi réussi, false sinon
 */
function sendOrderConfirmationToClient(array $orderData): bool {
    try {
        $mail = createMailer();

        // Destinataire
        $mail->addAddress($orderData['client_email'], $orderData['client_name']);

        // Sujet
        $mail->Subject = 'Confirmation de commande — ' . $orderData['reference'];

        // ── Corps HTML ────────────────────────────────────────
        $itemsHtml = '';
        foreach ($orderData['items'] as $item) {
            $emoji    = htmlspecialchars($item['emoji'] ?? '📦');
            $name     = htmlspecialchars($item['name']);
            $qty      = (int)$item['quantity'];
            $subtotal = fmt($item['price'] * $item['quantity']);
            $itemsHtml .= "
            <tr>
                <td style='padding:8px 12px;border-bottom:1px solid #f0f0f0'>
                    {$emoji} {$name} × {$qty}
                </td>
                <td style='padding:8px 12px;border-bottom:1px solid #f0f0f0;text-align:right;font-weight:600'>
                    {$subtotal}
                </td>
            </tr>";
        }

        $shippingRow = '';
        if ($orderData['shipping_cost'] > 0) {
            $shippingRow = "
            <tr>
                <td style='padding:8px 12px;border-bottom:1px solid #f0f0f0'>🚚 Livraison internationale</td>
                <td style='padding:8px 12px;border-bottom:1px solid #f0f0f0;text-align:right'>"
                    . fmt($orderData['shipping_cost']) . "</td>
            </tr>";
        }

        $paymentLabels = [
            'orange_money' => '📱 Orange Money',
            'wave'         => '🌊 Wave',
            'card'         => '💳 Carte bancaire',
            'cash'         => '💵 Paiement à la livraison',
        ];
        $paymentLabel = $paymentLabels[$orderData['payment_method']] ?? $orderData['payment_method'];

        $clientName    = htmlspecialchars($orderData['client_name']);
        $reference     = htmlspecialchars($orderData['reference']);
        $address       = htmlspecialchars($orderData['shipping_address']);
        $city          = htmlspecialchars($orderData['shipping_city']);
        $country       = htmlspecialchars($orderData['shipping_country']);
        $total         = fmt($orderData['total']);
        $notes         = !empty($orderData['notes'])
                         ? '<p style="margin:0;color:#6c7275;font-size:.85rem">Note : ' . htmlspecialchars($orderData['notes']) . '</p>'
                         : '';

        $mail->isHTML(true);
        $mail->Body = <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#f8f9fa;font-family:'Helvetica Neue',Arial,sans-serif">
          <div style="max-width:600px;margin:32px auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e8ecef">

            <!-- Header -->
            <div style="background:#141718;padding:24px 32px;text-align:center">
              <span style="color:#fff;font-size:1.5rem;font-weight:700">
                SenMarket<span style="color:#FDEF42">.</span>
              </span>
            </div>

            <!-- Succès -->
            <div style="padding:32px;text-align:center;border-bottom:1px solid #f0f0f0">
              <div style="font-size:3rem;margin-bottom:12px">✅</div>
              <h1 style="margin:0 0 8px;font-size:1.4rem;color:#141718">Commande confirmée !</h1>
              <p style="margin:0;color:#6c7275">
                Bonjour <strong>{$clientName}</strong>, votre commande a bien été enregistrée.
              </p>
            </div>

            <!-- Référence -->
            <div style="padding:24px 32px;text-align:center;border-bottom:1px solid #f0f0f0">
              <p style="margin:0 0 8px;color:#6c7275;font-size:.85rem">Référence de commande</p>
              <div style="display:inline-block;background:#f3f5f7;border-radius:10px;padding:10px 24px;font-size:1.1rem;font-weight:700;letter-spacing:1px">
                {$reference}
              </div>
            </div>

            <!-- Articles -->
            <div style="padding:24px 32px;border-bottom:1px solid #f0f0f0">
              <h2 style="margin:0 0 16px;font-size:1rem;color:#141718">Récapitulatif</h2>
              <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                {$itemsHtml}
                {$shippingRow}
                <tr style="background:#f8f9fa">
                  <td style="padding:12px;font-weight:700;color:#141718">Total à payer</td>
                  <td style="padding:12px;font-weight:700;color:#008751;text-align:right;font-size:1.1rem">{$total}</td>
                </tr>
              </table>
            </div>

            <!-- Livraison & paiement -->
            <div style="padding:24px 32px;border-bottom:1px solid #f0f0f0;font-size:.9rem">
              <h2 style="margin:0 0 12px;font-size:1rem;color:#141718">Informations de livraison</h2>
              <p style="margin:0 0 4px;color:#343839">📍 {$address}, {$city} — {$country}</p>
              <p style="margin:0 0 4px;color:#343839">💳 Mode de paiement : {$paymentLabel}</p>
              {$notes}
            </div>

            <!-- Footer -->
            <div style="padding:24px 32px;text-align:center;background:#f8f9fa">
              <p style="margin:0 0 8px;color:#6c7275;font-size:.85rem">
                Notre équipe vous contactera sous 24h pour les détails de livraison.
              </p>
              <p style="margin:0;color:#6c7275;font-size:.8rem">
                © SenMarket — Merci de votre confiance !
              </p>
            </div>

          </div>
        </body>
        </html>
        HTML;

        // Version texte (fallback)
        $mail->AltBody = "Bonjour {$clientName},\n\n"
            . "Votre commande {$reference} a été confirmée.\n"
            . "Total : {$total}\n"
            . "Livraison : {$address}, {$city} - {$country}\n"
            . "Paiement : {$paymentLabel}\n\n"
            . "Notre équipe vous contactera sous 24h.\n\nSenMarket.";

        $mail->send();
        error_log("[SenMarket] ✅ Email client envoyé à : " . $orderData['client_email']);
        return true;

    } catch (Exception $e) {
        error_log("[SenMarket] ❌ Échec email client : " . $e->getMessage());
        return false;
    }
}

/**
 * Envoie la notification de nouvelle commande à l'admin.
 *
 * @param  array $orderData  Données de la commande
 * @return bool              true si envoi réussi, false sinon
 */
function sendOrderNotificationToAdmin(array $orderData): bool {
    try {
        $mail = createMailer();

        $mail->addAddress(ADMIN_EMAIL, 'Admin SenMarket');
        $mail->Subject = '🛒 Nouvelle commande — ' . $orderData['reference'];

        $paymentLabels = [
            'orange_money' => 'Orange Money',
            'wave'         => 'Wave',
            'card'         => 'Carte bancaire',
            'cash'         => 'Paiement à la livraison',
        ];
        $paymentLabel = $paymentLabels[$orderData['payment_method']] ?? $orderData['payment_method'];

        $itemsList = '';
        foreach ($orderData['items'] as $item) {
            $itemsList .= '• ' . $item['name'] . ' × ' . $item['quantity']
                       . ' = ' . fmt($item['price'] * $item['quantity']) . "\n";
        }

        $mail->isHTML(false);
        $mail->Body = "=== NOUVELLE COMMANDE SENMARKET ===\n\n"
            . "Référence  : " . $orderData['reference'] . "\n"
            . "Client     : " . $orderData['client_name'] . "\n"
            . "Email      : " . $orderData['client_email'] . "\n"
            . "Téléphone  : " . $orderData['client_phone'] . "\n\n"
            . "--- Articles ---\n"
            . $itemsList
            . "Livraison  : " . fmt($orderData['shipping_cost']) . "\n"
            . "TOTAL      : " . fmt($orderData['total']) . "\n\n"
            . "--- Livraison ---\n"
            . $orderData['shipping_address'] . "\n"
            . $orderData['shipping_city'] . " — " . $orderData['shipping_country'] . "\n\n"
            . "Paiement   : " . $paymentLabel . "\n"
            . (!empty($orderData['notes']) ? "Notes      : " . $orderData['notes'] . "\n" : "")
            . "\n=== FIN ===";

        $mail->send();
        error_log("[SenMarket] ✅ Notification admin envoyée pour : " . $orderData['reference']);
        return true;

    } catch (Exception $e) {
        error_log("[SenMarket] ❌ Échec notification admin : " . $e->getMessage());
        return false;
    }
}