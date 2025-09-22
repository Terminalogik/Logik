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
            'SELECT * FROM ZOMBIES WHERE ZOMBIE_FNAME = :u OR ZOMBIE_EMAIL = :u LIMIT 1',
            [':u' => $username]
        );

        $ok = $user && (int)$user['ZOMBIE_VALIDATED'] === 1 && password_verify($password, $user['ZOMBIE_PASSWORD']);

        if (!$ok) {
            (new \Logik\Code())->view('auth/login', ['error' => 'Invalid credentials']);
            return;
        }

        // Successful login
        session_regenerate_id(true);
    $_SESSION['uid'] = (int)$user['ZOMBIE_ID'];
    $_SESSION['username'] = $user['ZOMBIE_FNAME'];
    $_SESSION['is_admin'] = ($user['ZOMBIE_ACCTTYPE'] === 'admin' ? 1 : 0);
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
            $errors[] = 'First name must be 3–32 chars (letters, numbers, underscore, dot).';
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
            $exists = db::fetchOne(
                'SELECT ZOMBIE_ID FROM ZOMBIES WHERE ZOMBIE_FNAME = :u OR ZOMBIE_EMAIL = :e LIMIT 1',
                [':u' => $username, ':e' => $email]
            );
            if ($exists) {
                $errors[] = 'First name or email already in use.';
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
        db::run(
            'INSERT INTO ZOMBIES (ZOMBIE_FNAME, ZOMBIE_EMAIL, ZOMBIE_PASSWORD, ZOMBIE_VALIDATED, ZOMBIE_ACCTTYPE) 
             VALUES (:u, :e, :p, 1, "user")',
            [':u' => $username, ':e' => $email, ':p' => $hash]
        );

        // Auto-login after registration (optional)
        $newUserId = (int)db::fetchValue('SELECT LAST_INSERT_ID()');
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

        // Step 1: Show password reset request form
        public function showForgot(): void
        {
            (new \Logik\Code())->view('auth/forgot');
        }

        // Step 2: Handle password reset request (send email with token)
        public function forgot(): void
        {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                http_response_code(405);
                echo 'Method Not Allowed';
                return;
            }
            if (!Csrf::validate($_POST['csrf'] ?? null)) {
                http_response_code(419);
                (new \Logik\Code())->view('auth/forgot', ['error' => 'Session expired. Please try again.']);
                return;
            }
            $email = trim((string)($_POST['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                (new \Logik\Code())->view('auth/forgot', ['error' => 'Please enter a valid email.']);
                return;
            }
        $user = DB::fetchOne('SELECT ZOMBIE_ID, ZOMBIE_FNAME FROM ZOMBIES WHERE ZOMBIE_EMAIL = :e LIMIT 1', [':e' => $email]);
            if (!$user) {
                (new \Logik\Code())->view('auth/forgot', ['error' => 'No account found for that email.']);
                return;
            }
            // Generate token and expiry
            $token = bin2hex(random_bytes(32));
            $expires = time() + 3600; // 1 hour
        DB::run('UPDATE ZOMBIES SET ZOMBIE_RESETTOKEN = :t, ZOMBIE_RESETEXPIRES = :x WHERE ZOMBIE_ID = :id', [':t' => $token, ':x' => $expires, ':id' => $user['ZOMBIE_ID']]);
            // Send email (replace with real mailer)
        $resetUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/reset?token=' . urlencode($token);
        mail($email, 'Password Reset', "Hello {$user['ZOMBIE_FNAME']},\n\nReset your password: $resetUrl\n\nThis link expires in 1 hour.");
            (new \Logik\Code())->view('auth/forgot', ['success' => 'Check your email for a reset link.']);
        }

        // Step 3: Show reset form (from email link)
        public function showReset(): void
        {
            $token = $_GET['token'] ?? '';
            if ($token === '') {
                echo 'Invalid or missing token.';
                return;
            }
        $user = DB::fetchOne('SELECT ZOMBIE_ID FROM ZOMBIES WHERE ZOMBIE_RESETTOKEN = :t AND ZOMBIE_RESETEXPIRES > :now LIMIT 1', [':t' => $token, ':now' => time()]);
            if (!$user) {
                echo 'Invalid or expired token.';
                return;
            }
            (new \Logik\Code())->view('auth/reset', ['token' => $token]);
        }

        // Step 4: Handle password reset submission
        public function reset(): void
        {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                http_response_code(405);
                echo 'Method Not Allowed';
                return;
            }
            if (!Csrf::validate($_POST['csrf'] ?? null)) {
                http_response_code(419);
                (new \Logik\Code())->view('auth/reset', ['error' => 'Session expired. Please try again.']);
                return;
            }
            $token = $_POST['token'] ?? '';
            $password = (string)($_POST['password'] ?? '');
            $confirm  = (string)($_POST['confirm'] ?? '');
            if ($token === '') {
                (new \Logik\Code())->view('auth/reset', ['error' => 'Missing token.']);
                return;
            }
            if (strlen($password) < 8) {
                (new \Logik\Code())->view('auth/reset', ['error' => 'Password must be at least 8 characters.', 'token' => $token]);
                return;
            }
            if ($password !== $confirm) {
                (new \Logik\Code())->view('auth/reset', ['error' => 'Password confirmation does not match.', 'token' => $token]);
                return;
            }
            $user = db::fetchOne('SELECT ZOMBIE_ID FROM ZOMBIES WHERE ZOMBIE_RESETTOKEN = :t AND ZOMBIE_RESETEXPIRES > :now LIMIT 1', [':t' => $token, ':now' => time()]);
            if (!$user) {
                (new \Logik\Code())->view('auth/reset', ['error' => 'Invalid or expired token.']);
                return;
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            db::run('UPDATE ZOMBIES SET ZOMBIE_PASSWORD = :p, ZOMBIE_RESETTOKEN = NULL, ZOMBIE_RESETEXPIRES = NULL WHERE ZOMBIE_ID = :id', [':p' => $hash, ':id' => $user['ZOMBIE_ID']]);
            (new \Logik\Code())->view('auth/login', ['success' => 'Password reset successful. You may now log in.']);
        }
}


?>