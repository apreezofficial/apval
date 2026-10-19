<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgreonPay — Tier 0 Verification</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background-color: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid #E2E8F0;
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            text-align: center;
        }
        .header {
            background-color: #1E7B42;
            padding: 32px 24px 28px;
            color: #FFFFFF;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 36px 28px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            background-color: #EAF6EE;
            color: #1E7B42;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .icon-circle {
            width: 68px;
            height: 68px;
            background-color: #EAF6EE;
            color: #1E7B42;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        .icon-circle.error {
            background-color: #FEE2E2;
            color: #DC2626;
        }
        .title {
            font-size: 22px;
            font-weight: 800;
            color: #1E293B;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 14.5px;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .limits-box {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 28px;
            text-align: left;
        }
        .limit-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            border-bottom: 1px solid #F1F5F9;
        }
        .limit-row:last-child {
            border-bottom: none;
        }
        .limit-label {
            color: #64748B;
        }
        .limit-val {
            font-weight: 700;
            color: #0F172A;
        }
        .btn-primary {
            display: block;
            background-color: #1E7B42;
            color: #FFFFFF;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(30, 123, 66, 0.28);
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #166534;
        }
        .footer {
            background-color: #F8FAFC;
            padding: 16px 20px;
            font-size: 12px;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1 class="brand-title">AgreonPay</h1>
        </div>
        <div class="content">
            @if ($status === 'success')
                <div class="icon-circle">✓</div>
                <div class="status-badge">Tier 0 Active</div>
                <h2 class="title">Email Verified Successfully!</h2>
                <p class="subtitle">
                    Welcome, <strong>{{ $user->first_name }}</strong>! Your AgreonPay Tier 0 account is active. You can now use basic wallet services or upgrade to Tier 1 to unlock dedicated virtual bank accounts.
                </p>

                <div class="limits-box">
                    <div class="limit-row">
                        <span class="limit-label">Daily Limit</span>
                        <span class="limit-val">₦20,000.00</span>
                    </div>
                    <div class="limit-row">
                        <span class="limit-label">Max Wallet Balance</span>
                        <span class="limit-val">₦50,000.00</span>
                    </div>
                    <div class="limit-row">
                        <span class="limit-label">Tier 1 Upgrade</span>
                        <span class="limit-val" style="color: #1E7B42;">Verify BVN in App</span>
                    </div>
                </div>

                <a href="agreonpay://kyc/status" class="btn-primary">
                    Open AgreonPay App
                </a>
            @else
                <div class="icon-circle error">✕</div>
                <h2 class="title">Verification Failed</h2>
                <p class="subtitle">{{ $message }}</p>
                <a href="agreonpay://kyc/status" class="btn-primary">
                    Return to AgreonPay App
                </a>
            @endif
        </div>
        <div class="footer">
            &copy; 2026 AgreonPay Nigeria. All rights reserved.
        </div>
    </div>
</body>
</html>
