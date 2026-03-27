<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Logging Helper

function logError(string $message, array $context = []): void {
    $logFile = STORAGE_PATH . 'logs' . DIRECTORY_SEPARATOR . 'error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] ERROR: {$message}{$contextStr}" . PHP_EOL;
    
    error_log($logEntry, 3, $logFile);
}

function logInfo(string $message, array $context = []): void {
    $logFile = STORAGE_PATH . 'logs' . DIRECTORY_SEPARATOR . 'info.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
    $logEntry = "[{$timestamp}] INFO: {$message}{$contextStr}" . PHP_EOL;
    
    error_log($logEntry, 3, $logFile);
}

function logApiRequest(string $endpoint, string $method, array $data = []): void {
    $logFile = STORAGE_PATH . 'logs' . DIRECTORY_SEPARATOR . 'api.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $dataStr = !empty($data) ? ' | Data: ' . json_encode($data) : '';
    
    $logEntry = "[{$timestamp}] {$ip} {$method} {$endpoint}{$dataStr} | UA: {$userAgent}" . PHP_EOL;
    error_log($logEntry, 3, $logFile);
}
