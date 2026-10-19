<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete My Account — Agreonpay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; min-height: 100vh; display: flex; flex-direction: column; }
        header { background: #1a6b3a; padding: 16px 24px; display: flex; align-items: center; gap: 12px; }
        header h1 { color: #fff; font-size: 20px; font-weight: 700; }
        header span { color: #a8d5b5; font-size: 13px; }
        main { flex: 1; max-width: 560px; width: 100%; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 12px; padding: 36px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        h2 { font-size: 22px; font-weight: 700; margin-bottom: 8px; color: #c0392b; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 24px; line-height: 1.5; }
        .warning { background: #fff8e1; border-left: 4px solid #f39c12; padding: 14px 16px; border-radius: 6px; margin-bottom: 24px; font-size: 13px; line-height: 1.6; color: #7d6608; }
        .warning strong { display: block; margin-bottom: 4px; font-size: 14px; color: #5d4e07; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #444; }
        input[type="email"], input[type="password"], textarea {
            width: 100%; padding: 11px 14px; border: 1.5px solid #ddd; border-radius: 8px;
            font-size: 15px; margin-bottom: 18px; outline: none; transition: border-color .2s;
        }
        input:focus, textarea:focus { border-color: #1a6b3a; }
        textarea { resize: vertical; min-height: 80px; font-family: inherit; }
        button { width: 100%; padding: 13px; background: #c0392b; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background .2s; }
        button:hover { background: #a93226; }
        .alert { padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert.success { background: #eafaf1; border: 1px solid #2ecc71; color: #1a7a45; }
        .alert.error   { background: #fdf0ef; border: 1px solid #e74c3c; color: #922b21; }
        footer { text-align: center; padding: 24px; font-size: 12px; color: #999; }
        a { color: #1a6b3a; }
    </style>
</head>
<body>
<header>
    <h1>Agreonpay</h1>
    <span>Agricultural Marketplace</span>
</header>

<main>
    <div class="card">
        <h2>Delete My Account</h2>
        <p class="subtitle">Enter your credentials below to permanently delete your Agreonpay account.</p>

        @if (!empty($success))
            <div class="alert success">{{ $success }}</div>
        @elseif (!empty($error))
            <div class="alert error">{{ $error }}</div>
        @endif

        @if (empty($success))
        <div class="warning">
            <strong>⚠️ This action is permanent and cannot be undone.</strong>
            Deleting your account will remove all your personal information, order history, wallet balance, and product listings from our system. Funds in your wallet will be forfeited if not withdrawn first.
        </div>

        <form method="POST" action="{{ url('/account/delete') }}">
            @csrf
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" required value="{{ old('email') }}">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Your account password" required>

            <label for="reason">Reason for leaving (optional)</label>
            <textarea id="reason" name="reason" placeholder="Help us improve..."></textarea>

            <button type="submit">Permanently Delete My Account</button>
        </form>
        @endif
    </div>
</main>

<footer>
    &copy; {{ date('Y') }} Agreonpay. &nbsp;|&nbsp; <a href="mailto:support@agreonpay.com.ng">support@agreonpay.com.ng</a>
</footer>
</body>
</html>
