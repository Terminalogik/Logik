<?php
// Minimal redirect for hosts that point the domain to the project root
// Sends users to the public PDZ home page: /public/pdzhome

$APPHOME = 'pdzhome';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');

// Build absolute URL to public pdzhome test
$target = $scheme . '://' . $host . '/public/' . $APPHOME;
header('Location: ' . $target, true, 302);
exit;
