<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Security Helper

function sanitizeInput(string $input): string {
    return htmlspecialchars(
        strip_tags(trim($input)),
        ENT_QUOTES,
        'UTF-8'
    );
}

function validatePassword(string $password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    return $errors;
}

function generateSecureToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

function isSecureConnection(): bool {
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        $_SERVER['SERVER_PORT'] == 443 ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
}

function rateLimitCheck(string $identifier, int $maxAttempts = 5, int $windowSeconds = 300): bool {
    $cacheFile = STORAGE_PATH . 'logs' . DIRECTORY_SEPARATOR . 'rate_limit_' . md5($identifier) . '.json';
    
    if (!file_exists($cacheFile)) {
        $data = [
            'attempts' => 1,
            'first_attempt' => time(),
            'last_attempt' => time()
        ];
        file_put_contents($cacheFile, json_encode($data));
        return true;
    }
    
    $data = json_decode(file_get_contents($cacheFile), true);
    $now = time();
    
    // Reset if window expired
    if ($now - $data['first_attempt'] > $windowSeconds) {
        $data = [
            'attempts' => 1,
            'first_attempt' => $now,
            'last_attempt' => $now
        ];
        file_put_contents($cacheFile, json_encode($data));
        return true;
    }
    
    // Check limit
    if ($data['attempts'] >= $maxAttempts) {
        return false;
    }
    
    // Increment attempts
    $data['attempts']++;
    $data['last_attempt'] = $now;
    file_put_contents($cacheFile, json_encode($data));
    return true;
}
