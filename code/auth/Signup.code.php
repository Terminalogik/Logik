<?php
namespace App\Code;

use Logik\Code;

class SignupCode extends Code
{
    public function think(): void {
        error_log('SignupCode@joinhorde called');
        $errors = [];
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['txtInfectedEmail'] ?? '');
            $fname = trim($_POST['txtInfectedFirstname'] ?? '');
            $lname = trim($_POST['txtInfectedLastname'] ?? '');
            $password = $_POST['txtInfectedPassword'] ?? '';
            $confirm = $_POST['txtInfectedConfirmPassword'] ?? '';

            // Basic validation
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'A valid email is required.';
            }
            if ($fname === '' || strlen($fname) < 2) {
                $errors[] = 'First name is required.';
            }
            if ($lname === '' || strlen($lname) < 2) {
                $errors[] = 'Last name is required.';
            }
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Password confirmation does not match.';
            }

            // Check for duplicate email
            if (!$errors) {
                try {
                    $exists = \Logik\Data\DB::read('SELECT ZOMBIE_ID FROM ZOMBIES WHERE ZOMBIE_EMAIL = :e LIMIT 1', [':e' => $email], \Logik\Data\DB::READ_ONE);
                    if ($exists) {
                        $errors[] = 'Email is already registered.';
                    } else {
                        error_log('No existing user found with email ' . $email);
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Database error during duplicate check: ' . $e->getMessage();
                    error_log('DB error during duplicate check: ' . $e->getMessage());
                }
            }

            if (!$errors) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $phone = trim($_POST['txtInfectedPhone'] ?? '');
                $lastLogin = date('Y-m-d H:i:s'); // Use current timestamp for new user
                $validated = 0; // Default: not validated
                $createdDate = date('Y-m-d H:i:s');
                $acctType = 'ZOMBIE'; // Or set as needed
                try {
                    $sql = 'INSERT INTO `ZOMBIES` (
                          `ZOMBIE_FNAME`, `ZOMBIE_LNAME`, `ZOMBIE_EMAIL`, `ZOMBIE_PHONE`, 
                          `ZOMBIE_LASTLOGIN`, `ZOMBIE_PASSWORD`, `ZOMBIE_VALIDATED`, 
                          `ZOMBIE_CREATEDATE`, `ZOMBIE_ACCTTYPE`
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
                    $params = [
                        $fname,
                        $lname,
                        $email,
                        $phone,
                        $lastLogin,
                        $hash,
                        $validated,
                        $createdDate,
                        $acctType
                    ];
                    $res = \Logik\Data\DB::write($sql, $params);
                    $rowCount = $res['rowCount'] ?? null;
                    if ($rowCount > 0) {
                        $success = 'Registration successful! You may now log in.';
                    } else {
                        $errors[] = 'Registration failed. Please try again.';
                        $errors[] = 'Debug: Insert returned rowCount=' . ($rowCount ?? 'null');
                        $errors[] = 'Debug: SQL=' . $sql;
                        $errors[] = 'Debug: Params=' . var_export($params, true);
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                    $errors[] = 'Debug: Exception trace: ' . $e->getTraceAsString();
                    $errors[] = 'Debug: SQL=' . $sql;
                    $errors[] = 'Debug: Params=' . var_export($params, true);
                }
            }
        }
        $this->view('auth/signup', [
            'title' => 'Sign Up',
            'errors' => $errors,
            'success' => $success
        ], 'axiom_login');
    }
}