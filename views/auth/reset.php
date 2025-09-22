<h2>Reset Password</h2>
<?php if (!empty($error)) { echo '<div class="error">' . htmlspecialchars($error) . '</div>'; } ?>
<form method="post" action="/reset">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? ($_GET['token'] ?? '')) ?>">
    <label>New Password:<br><input type="password" name="password" required></label><br>
    <label>Confirm Password:<br><input type="password" name="confirm" required></label><br>
    <button type="submit">Reset Password</button>
</form>
<a href="/login">Back to login</a>
