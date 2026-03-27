<?php
// To install dependencies open XAMPP Shell and run:
// cd C:\xampp\htdocs\bocra-website\backend
// composer install

// BOCRA-Website — Validation Helpers

function validateEmail(string $email): bool {
  return filter_var(
    $email, FILTER_VALIDATE_EMAIL
  ) !== false;
}

function validateRequired(
  array $data,
  array $fields
): array {
  $errors = [];
  foreach ($fields as $field) {
    if (empty($data[$field])) {
      $label = ucwords(
        str_replace('_', ' ', $field)
      );
      $errors[$field] = $label . ' is required';
    }
  }
  return $errors;
}

function sanitize(string $value): string {
  return htmlspecialchars(
    strip_tags(trim($value)),
    ENT_QUOTES,
    'UTF-8'
  );
}
