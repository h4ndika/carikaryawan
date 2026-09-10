<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured on DISEKITAR - {{ $post->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }
        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #0f172a;
            color: #ffffff;
            border: 1px solid rgba(245, 158, 11, 0.4);
            padding: 8px 14px;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s, background 0.2s;
        }
        .badge-pill:hover {
            transform: scale(1.02);
            background: #1e293b;
        }
        .star-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.4);
        }
        .badge-content {
            display: flex;
            flex-direction: column;
            text-align: left;
        }
        .badge-title {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #f59e0b;
            font-weight: 800;
        }
        .badge-brand {
            font-size: 12px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .rating-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            color: #fbbf24;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <a href="{{ $post->url() }}" target="_blank" rel="noopener" class="badge-pill" title="Ulasan Rekomendasi Terpilih di DISEKITAR">
        <div class="star-icon">
            <i class="fa-solid fa-award"></i>
        </div>
        <div class="badge-content">
            <span class="badge-title">REKOMENDASI TERPILIH 2026</span>
            <div class="badge-brand">
                <span>DISEKITAR</span>
                <span class="rating-badge">★ {{ number_format($post->google_rating ?? 4.8, 1) }}</span>
            </div>
        </div>
    </a>

</body>
</html>
