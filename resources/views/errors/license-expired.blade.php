<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Inactive — {{ $app_name ?? config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #1f2937;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,.4);
            max-width: 480px;
            width: 100%;
            padding: 52px 44px;
            text-align: center;
        }

        .icon-wrap {
            width: 88px;
            height: 88px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
        }

        .icon-wrap svg { width: 44px; height: 44px; color: #dc2626; }

        h1 { font-size: 26px; font-weight: 700; margin-bottom: 14px; }

        .subtitle { font-size: 15px; color: #6b7280; line-height: 1.7; margin-bottom: 32px; }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 28px;
            text-align: left;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .info-row:last-child { border-bottom: none; }

        .info-label { color: #9ca3af; }

        .info-value { font-weight: 600; color: #111827; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .footer-note { margin-top: 28px; font-size: 12px; color: #d1d5db; }

        .footer-note a { color: #9ca3af; text-decoration: none; }

        .footer-note a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h1>Application License Inactive</h1>

        <p class="subtitle">
            The license for this application has expired or been deactivated.
            Please contact your system administrator to restore access.
        </p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Application</span>
                <span class="info-value">{{ $app_name ?? config('app.name') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Domain</span>
                <span class="info-value">{{ $domain ?? request()->getHost() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="status-badge">
                    <svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="4"/>
                    </svg>
                    Inactive
                </span>
            </div>
        </div>

        <p class="footer-note">
            Protected by <a href="https://tecnozt.com" target="_blank" rel="noopener">Proteczt</a>
            &mdash; License Management System
        </p>
    </div>
</body>
</html>
