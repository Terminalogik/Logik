<?php
/** @var \Logik\Router $vector */

// Landing
$vector->get('/', 'WelcomeCode@index', 'home');

// Auth vectors
// Login system routes
$vector->get('/login',  'LoginCode@think', 'login');
$vector->post('/login', 'LoginCode@think', 'login.zombie');

$vector->get('/signup', 'SignupCode@think', 'signup');
$vector->post('/signup', 'SignupCode@think', 'signup');

$vector->get('/logout', 'LogoutCode@think', 'logout');
$vector->get('/forgot', 'ForgotCode@think', 'forgot');


$vector->get('/_debug', function () {
    header('Content-Type: text/plain');
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '') . PHP_EOL;
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL;
});
