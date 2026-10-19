<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password — AgreonPay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f4f7f5;
            color: #2d3748;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: #1a6b3a;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header h1 {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        header span {
            color: #a8d5b5;
            font-size: 13px;
        }
        main {
            flex: 1;
            max-width: 460px;
            width: 100%;
            margin: 40px auto;
            padding: 0 16px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 32px 28px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a6b3a;
        }
        .subtitle {
            color: #718096;
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #4a5568;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #cbd5e0;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 18px;
            outline: none;
            transition: border-color .2s;
        }
        input:focus {
            border-color: #1a6b3a;
            box-shadow: 0 0 0 3px rgba(26, 107, 58, 0.15);
        }
        .btn {
            width: 100%;
            padding: 13px;
            background: #1a6b3a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        .btn:hover {
            background: #14552e;
        }
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert.success {
            background: #eafaf1;
            border: 1px solid #2ecc71;
            color: #1a7a45;
        }
        .alert.error {
            background: #fdf0ef;
            border: 1px solid #e74c3c;
            color: #922b21;
        }
        .hints {
            font-size: 12px;
            color: #718096;
            margin-top: -12px;
            margin-bottom: 18px;
        }
        footer {
            text-align: center;
            padding: 24px;
            font-size: 12px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
<header>
    <h1>AgreonPay</h1>
    <span>Agricultural Commerce</span>
</header>

<main>
    <div class="card">
        <h2>Set New Password</h2>
        <p class="subtitle">Enter your account email and choose a strong new password.</p>

        @if (session('status'))
            <div class="alert success">
                <strong>Success!</strong> {{ session('status') }}
                <p style="margin-top: 8px; font-size: 13px;">You can now open the Agreon mobile app and log in with your new password.</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert error">
                <strong>Please check the errors below:</strong>
                <ul style="margin-left: 18px; margin-top: 6px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!session('status'))
        <form method="POST" action="{{ url('/reset-password') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token ?? request()->query('token') }}">

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email', request()->query('email')) }}" required placeholder="you@example.com" autocomplete="email">

            <label for="password">New Password</label>
            <input type="password" id="password" name="password" required placeholder="Minimum 8 characters" autocomplete="new-password">
            <div class="hints">Must be at least 8 characters with mixed case, numbers, and symbols.</div>

            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Confirm new password" autocomplete="new-password">

            <button type="submit" class="btn">Reset Password</button>
        </form>
        @endif
    </div>
</main>

<footer>
    &copy; {{ date('Y') }} AgreonPay. All rights reserved.
</footer>
</body>
</html>
