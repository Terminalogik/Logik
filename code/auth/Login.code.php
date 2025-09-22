<?php
namespace App\Code;

use Logik\Code;

class LoginCode extends Code
{
    public function think(): void
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['txtInfectedEmail'] ?? '');
            $password = $_POST['txtInfectedPassword'] ?? '';

            if ($email === '' || $password === '') {
                $errors[] = 'Email and password are required.';
            } else {
                $user = \Logik\Data\DB::read(
                    'SELECT ZOMBIE_ID, ZOMBIE_FNAME, ZOMBIE_LNAME,ZOMBIE_EMAIL, ZOMBIE_PASSWORD, ZOMBIE_ACCTTYPE FROM ZOMBIES WHERE ZOMBIE_EMAIL = :e LIMIT 1',
                    [':e' => $email],
                    \Logik\Data\DB::READ_ONE
                );
                if ($user && password_verify($password, $user['ZOMBIE_PASSWORD'])) {
                    if (session_status() !== PHP_SESSION_ACTIVE) {
                        session_start();
                    }
                    $_SESSION['zombie_id'] = $user['ZOMBIE_ID'];
                    $_SESSION['zombie_email'] = $user['ZOMBIE_EMAIL'];
                    $_SESSION['zombie_fname'] = $user['ZOMBIE_FNAME'];
                    $_SESSION['zombie_lname'] = $user['ZOMBIE_LNAME'];
                    $_SESSION['zombie_accttype'] = $user['ZOMBIE_ACCTTYPE'];
                    $_SESSION['zombie_name'] = trim($user['ZOMBIE_FNAME'] . ' ' . $user['ZOMBIE_LNAME']);
                    header('Location: ' . atlas('pdzoverview'));
                    exit;
                } else {
                    $errors[] = 'Invalid email or password.';
                }
            }
        }
        $this->view('auth/login', [
            'title' => 'Login',
            'errors' => $errors
        ], 'axiom_login');
    }
}   