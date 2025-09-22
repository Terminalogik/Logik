<?php
namespace Code;

use Logik\Data\DB as db;

class AuthCode
{
    public function showLogin(): void
    {
        (new \Logik\Code())->view('auth/login');
    }

    public function login(): void
    {
        // Basic method guard
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        // CSRF
        if (!Csrf::validate($_POST['csrf'] ?? null)) {
            http_response_code(419);
            (new \Logik\Code())->view('auth/login', ['error' => 'Session expired. Please try again.']);
            return;
        }

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            (new \Logik\Code())->view('auth/login', ['error' => 'Please enter username and password.']);
            return;
        }

        // Allow login by username OR email
        $user = db::fetchOne(
            'SELECT * FROM users WHERE username = :u OR email = :u LIMIT 1',
            [':u' => $username]
        );

        $ok = $user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash']);

        if (!$ok) {
            (new \Logik\Code())->view('auth/login', ['error' => 'Invalid credentials']);
            return;
        }

        // Successful login
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (int)$user['is_admin'];
        $_SESSION['last_auth_at'] = time();

        // Change this destination to your admin dashboard route
        header('Location: /app.myevents');
        exit;
    }

    public function showRegister(): void
    {
        (new \Logik\Code())->view('auth/register');
    }

    public function register(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        if (!Csrf::validate($_POST['csrf'] ?? null)) {
            http_response_code(419);
            (new \Logik\Code())->view('auth/register', ['errors' => ['Session expired. Please try again.']]);
            return;
        }

        $username = trim((string)($_POST['username'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['confirm'] ?? '');

        $errors = [];

        // Simple validation
        if ($username === '' || !preg_match('/^[A-Za-z0-9_\.]{3,32}$/', $username)) {
            $errors[] = 'Username must be 3–32 chars (letters, numbers, underscore, dot).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Password confirmation does not match.';
        }

        // Unique checks
        if (!$errors) {
            $exists = DB::fetchOne(
                'SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1',
                [':u' => $username, ':e' => $email]
            );
            if ($exists) {
                $errors[] = 'Username or email already in use.';
            }
        }

        if ($errors) {
            (new \Logik\Code())->view('auth/register', [
                'errors' => $errors,
                'old' => ['username' => $username, 'email' => $email]
            ]);
            return;
        }

        // Create user (non-admin by default)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        DB::run(
            'INSERT INTO users (username, email, password_hash, is_admin, is_active) 
             VALUES (:u, :e, :p, 0, 1)',
            [':u' => $username, ':e' => $email, ':p' => $hash]
        );

        // Auto-login after registration (optional)
        $newUserId = (int)DB::fetchValue('SELECT LAST_INSERT_ID()');
        session_regenerate_id(true);
        $_SESSION['uid'] = $newUserId;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = 0;
        $_SESSION['last_auth_at'] = time();

        header('Location: /app.mytickets'); // or redirect to /profile, etc.
        exit;
    }

    public function logout(): void
    {
        // Use POST for logout in production; keeping GET for simplicity
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: /login');
        exit;
    }
}


?>