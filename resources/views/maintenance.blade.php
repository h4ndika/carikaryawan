<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sedang Dalam Pemeliharaan' }} - CariKaryawan</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        .bg-glow {
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            pointer-events: none;
        }
        .card {
            position: relative;
            z-index: 2;
            background: rgba(17, 24, 39, 0.85);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 20px;
            padding: 48px 36px;
            max-width: 540px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6), 0 0 30px rgba(245, 158, 11, 0.1);
            backdrop-filter: blur(12px);
        }
        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 18px;
            margin-bottom: 24px;
            color: #f59e0b;
        }
        .badge {
            display: inline-block;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 6px 14px;
            border-radius: 9999px;
            border: 1px solid rgba(245, 158, 11, 0.3);
            margin-bottom: 16px;
        }
        h1 {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 14px;
            line-height: 1.3;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 24px 0;
        }
        .footer-note {
            font-size: 13px;
            color: #64748b;
        }
        .footer-note strong {
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="card">
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
        </div>

        <div class="badge">Sistem Pemeliharaan</div>

        <h1>{{ $title ?? 'Sistem Sedang Dalam Pemeliharaan' }}</h1>

        <p>
            {!! nl2br(e($message ?? 'Kami sedang melakukan peningkatan performa dan pemeliharaan berkala untuk memberikan pengalaman terbaik. Layanan akan segera kembali normal.')) !!}
        </p>

        <div class="divider"></div>

        <div class="footer-note">
            Perkiraan selesai: <strong>{{ $estimated_time ?? 'Segera / Dalam beberapa saat' }}</strong>
        </div>
    </div>
</body>
</html>
