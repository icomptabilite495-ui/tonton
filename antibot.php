<?php
// ============================================
// SYSTÈME ANTI-BOT MULTI-COUCHES
// ============================================

// Démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// 1. TOKEN CSRF (Cross-Site Request Forgery)
// ============================================
function genererTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifierTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function regenererTokenCSRF() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================
// 2. CAPTCHA MATHÉMATIQUE
// ============================================
function genererCaptcha() {
    $a = random_int(2, 15);
    $b = random_int(2, 15);
    $operations = ['+', '-', '*'];
    $op = $operations[random_int(0, 2)];
    
    switch ($op) {
        case '+': $resultat = $a + $b; break;
        case '-': $resultat = $a - $b; break;
        case '*': $resultat = $a * $b; break;
    }
    
    $_SESSION['captcha_question'] = "$a $op $b = ?";
    $_SESSION['captcha_reponse'] = $resultat;
    $_SESSION['captcha_id'] = bin2hex(random_bytes(8));
    
    return [
        'question' => $_SESSION['captcha_question'],
        'id' => $_SESSION['captcha_id']
    ];
}

function verifierCaptcha($reponse) {
    if (!isset($_SESSION['captcha_reponse'])) {
        return false;
    }
    $reponse = (int)trim($reponse);
    $correct = ($_SESSION['captcha_reponse'] === $reponse);
    
    // Invalider le captcha après vérification (usage unique)
    unset($_SESSION['captcha_reponse']);
    unset($_SESSION['captcha_question']);
    
    return $correct;
}

// ============================================
// 3. HONEYPOT (champ piège invisible)
// ============================================
function genererHoneypot() {
    $nom_champ = 'hp_' . substr(md5(random_bytes(16)), 0, 10);
    $_SESSION['honeypot_field'] = $nom_champ;
    return $nom_champ;
}

function verifierHoneypot($donnees_post) {
    $champ = $_SESSION['honeypot_field'] ?? null;
    if (!$champ) return true; // Pas de honeypot configuré = OK
    
    // Si le champ piège est rempli, c'est un bot
    if (!empty($donnees_post[$champ])) {
        return false;
    }
    return true;
}

// ============================================
// 4. VÉRIFICATION TEMPS MINIMUM (anti-bot rapide)
// ============================================
function initTimestampEtape() {
    $_SESSION['etape_timestamp'] = time();
}

function verifierTempsMinimum($secondes_minimum = 3) {
    if (!isset($_SESSION['etape_timestamp'])) {
        return false; // Pas de timestamp = suspect
    }
    $temps_ecoule = time() - $_SESSION['etape_timestamp'];
    return $temps_ecoule >= $secondes_minimum;
}

function getTempsRestant($secondes_minimum = 3) {
    if (!isset($_SESSION['etape_timestamp'])) {
        return $secondes_minimum;
    }
    $temps_ecoule = time() - $_SESSION['etape_timestamp'];
    $restant = $secondes_minimum - $temps_ecoule;
    return max(0, $restant);
}

// ============================================
// 5. TOKEN JAVASCRIPT (vérifie que JS est activé)
// ============================================
function genererTokenJS() {
    $_SESSION['js_token_attendu'] = bin2hex(random_bytes(16));
    return $_SESSION['js_token_attendu'];
}

function verifierTokenJS($token_recu) {
    if (!isset($_SESSION['js_token_attendu'])) {
        return false;
    }
    $valide = hash_equals($_SESSION['js_token_attendu'], $token_recu);
    unset($_SESSION['js_token_attendu']);
    return $valide;
}

// ============================================
// 6. RATE LIMITING (limite de tentatives)
// ============================================
function verifierRateLimit($action = 'default', $max_tentatives = 10, $periode_secondes = 300) {
    $cle = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    if (!isset($_SESSION[$cle])) {
        $_SESSION[$cle] = ['count' => 0, 'start' => time()];
    }
    
    $data = $_SESSION[$cle];
    
    // Réinitialiser si la période est écoulée
    if (time() - $data['start'] > $periode_secondes) {
        $_SESSION[$cle] = ['count' => 1, 'start' => time()];
        return true;
    }
    
    $_SESSION[$cle]['count']++;
    
    if ($_SESSION[$cle]['count'] > $max_tentatives) {
        return false; // Trop de tentatives
    }
    
    return true;
}

function getTentativesRestantes($action = 'default', $max_tentatives = 10) {
    $cle = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!isset($_SESSION[$cle])) {
        return $max_tentatives;
    }
    return max(0, $max_tentatives - $_SESSION[$cle]['count']);
}

// ============================================
// 7. VÉRIFICATION USER-AGENT
// ============================================
function verifierUserAgent() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // User-Agent vide = suspect
    if (empty($ua)) {
        return false;
    }
    
    // Liste de bots connus à bloquer
    $bots_connus = [
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 
        'python', 'php', 'java', 'ruby', 'perl', 'headless'
    ];
    
    $ua_lower = strtolower($ua);
    foreach ($bots_connus as $bot) {
        if (strpos($ua_lower, $bot) !== false) {
            // Exception pour les vrais moteurs de recherche (optionnel)
            if (strpos($ua_lower, 'googlebot') !== false || 
                strpos($ua_lower, 'bingbot') !== false) {
                continue;
            }
            return false;
        }
    }
    
    return true;
}

// ============================================
// 8. VÉRIFICATION COMPLÈTE (toutes les couches)
// ============================================
function verifierAntiBot($donnees_post = [], $options = []) {
    $resultats = [
        'csrf'         => false,
        'captcha'      => false,
        'honeypot'     => false,
        'temps'        => false,
        'js_token'     => false,
        'rate_limit'   => false,
        'user_agent'   => false,
        'bloque'       => false,
        'raison'       => ''
    ];
    
    // 1. Vérification User-Agent
    $resultats['user_agent'] = verifierUserAgent();
    if (!$resultats['user_agent']) {
        $resultats['bloque'] = true;
        $resultats['raison'] = 'User-Agent suspect détecté';
        return $resultats;
    }
    
    // 2. Rate limiting
    $action = $options['action'] ?? 'form_submit';
    $max_tentatives = $options['max_tentatives'] ?? 10;
    $resultats['rate_limit'] = verifierRateLimit($action, $max_tentatives);
    if (!$resultats['rate_limit']) {
        $resultats['bloque'] = true;
        $resultats['raison'] = 'Trop de tentatives. Réessayez dans 5 minutes.';
        return $resultats;
    }
    
    // 3. Token CSRF
    $csrf_token = $donnees_post['csrf_token'] ?? '';
    $resultats['csrf'] = verifierTokenCSRF($csrf_token);
    if (!$resultats['csrf']) {
        $resultats['bloque'] = true;
        $resultats['raison'] = 'Token de sécurité invalide';
        return $resultats;
    }
    
    // 4. Honeypot
    $resultats['honeypot'] = verifierHoneypot($donnees_post);
    if (!$resultats['honeypot']) {
        $resultats['bloque'] = true;
        $resultats['raison'] = 'Activité suspecte détectée';
        return $resultats;
    }
    
    // 5. Temps minimum
    $temps_min = $options['temps_minimum'] ?? 3;
    $resultats['temps'] = verifierTempsMinimum($temps_min);
    if (!$resultats['temps']) {
        $resultats['bloque'] = true;
        $resultats['raison'] = 'Soumission trop rapide';
        return $resultats;
    }
    
    // 6. Token JavaScript
    $js_token = $donnees_post['js_token'] ?? '';
    $resultats['js_token'] = verifierTokenJS($js_token);
    if (!$resultats['js_token']) {
        $resultats['bloque'] = true;
        $resultats['raison'] = 'JavaScript non activé ou bloqué';
        return $resultats;
    }
    
    // 7. Captcha (optionnel - seulement si demandé)
    if ($options['avec_captcha'] ?? false) {
        $captcha_reponse = $donnees_post['captcha'] ?? '';
        $resultats['captcha'] = verifierCaptcha($captcha_reponse);
        if (!$resultats['captcha']) {
            $resultats['bloque'] = true;
            $resultats['raison'] = 'Captcha incorrect';
            return $resultats;
        }
    } else {
        $resultats['captcha'] = true; // Non requis
    }
    
    // Toutes les vérifications passent
    $resultats['bloque'] = false;
    regenererTokenCSRF();
    initTimestampEtape();
    
    return $resultats;
}

// ============================================
// 9. LOG DES TENTATIVES SUSPECTES
// ============================================
function loggerTentativeSuspecte($raison, $donnees = []) {
    $log_file = __DIR__ . '/antibot_logs.txt';
    $log_entry = date('Y-m-d H:i:s') . " | IP: " . ($_SERVER['REMOTE_ADDR'] ?? '?') . 
                 " | Raison: $raison | UA: " . ($_SERVER['HTTP_USER_AGENT'] ?? '?') . "\n";
    
    if (!empty($donnees)) {
        $log_entry .= "Données: " . json_encode($donnees, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    $log_entry .= str_repeat('-', 50) . "\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?>