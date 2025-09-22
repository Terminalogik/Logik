<!-- views/errors/csrf.php -->
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Security Check Failed (CSRF)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root { color-scheme: dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
           background:#0f172a; color:#e5e7eb; display:grid; place-items:center;
           min-height:100vh; margin:0; }
    .card { background:#111827; padding:28px; border-radius:16px;
            width:min(520px,92vw); box-shadow:0 10px 30px rgba(0,0,0,.35); }
    h1 { margin:0 0 8px 0; font-size:22px; }
    p { margin:8px 0; color:#cbd5e1; line-height:1.5; }
    .hint { font-size:13px; color:#94a3b8; }
    .btns { display:flex; gap:10px; margin-top:16px; flex-wrap:wrap; }
    .btn { appearance:none; border:none; border-radius:10px; padding:10px 14px; cursor:pointer;
           font-weight:600; }
    .primary { background:#2563eb; color:#fff; }
    .secondary { background:#0b1220; color:#e5e7eb; border:1px solid #334155; }
    a.btn { text-decoration:none; display:inline-block; }
    code { background:#0b1220; padding:2px 6px; border-radius:6px; border:1px solid #334155; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Security Check Failed</h1>
    <p>We couldn’t verify this request (<code>CSRF</code> protection). This usually happens if the form sat open too long, the tab was duplicated, or you navigated from another site.</p>

    <p class="hint">Fixes to try:</p>
    <ul class="hint">
      <li>Go back and reload the form, then submit again.</li>
      <li>Ensure the page is open only once (close other tabs), then retry.</li>
      <li>If the issue persists, sign out and sign back in.</li>
    </ul>

    <div class="btns">
      <button class="btn primary" onclick="history.length ? history.back() : window.location.assign('<?= htmlspecialchars($backUrl ?? '/login', ENT_QUOTES) ?>')">
        Go Back & Retry
      </button>
      <a class="btn secondary" href="<?= htmlspecialchars($backUrl ?? '/login', ENT_QUOTES) ?>">Open Form Fresh</a>
      <a class="btn secondary" href="<?= htmlspecialchars($homeUrl ?? '/', ENT_QUOTES) ?>">Home</a>
    </div>
  </main>

  <script>
    // Optional: if we arrived via POST and the browser blocks history.back(),
    // fall back to the provided backUrl after a short delay.
    setTimeout(function () {
      if (document.referrer === '') {
        window.location.href = '<?= htmlspecialchars($backUrl ?? '/login', ENT_QUOTES) ?>';
      }
    }, 8000);
  </script>
</body>
</html>
