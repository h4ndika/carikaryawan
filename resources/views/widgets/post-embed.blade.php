<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} - DISEKITAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #ffffff;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }
        .widget-card {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .widget-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.08);
        }
        .img-container {
            width: 100%;
            height: 180px;
            position: relative;
            background: #f1f5f9;
            overflow: hidden;
        }
        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .badge-cat {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            color: #f8fafc;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-verified {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: #d97706;
            color: #ffffff;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .location {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .rating-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        .stars { color: #f59e0b; }
        .rating-num { font-weight: 700; color: #0f172a; }
        .reviews-count { font-size: 11px; color: #64748b; }
        .excerpt {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-footer {
            margin-top: 4px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand-seal {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #b45309;
            text-transform: uppercase;
        }
        .btn-view {
            background: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            transition: background 0.15s;
        }
        .btn-view:hover {
            background: #d97706;
        }
    </style>
</head>
<body>

    <div class="widget-card">
        <div class="img-container">
            <img src="{{ $post->primary_image_url ?: asset('images/placeholder.svg') }}" 
                 alt="{{ $post->title }}"
                 loading="lazy">
            @if ($post->category)
                <span class="badge-cat">{{ $post->category->name }}</span>
            @endif
            <span class="badge-verified">
                <i class="fa-solid fa-award"></i> Pilihan Terkurasi
            </span>
        </div>

        <div class="card-body">
            <div class="rating-row">
                <span class="stars"><i class="fa-solid fa-star"></i></span>
                <span class="rating-num">{{ number_format($post->google_rating ?? 4.8, 1) }}</span>
                <span class="reviews-count">({{ $post->google_reviews_count ?? 120 }}+ ulasan)</span>
            </div>

            <h2 class="card-title">{{ $post->title }}</h2>

            @if ($post->city)
                <div class="location">
                    <i class="fa-solid fa-location-dot" style="color: #d97706;"></i>
                    <span>{{ $post->city->name }}@if($post->province), {{ $post->province->name }}@endif</span>
                </div>
            @endif

            <p class="excerpt">
                {{ str($post->excerpt ?: strip_tags($post->content))->limit(90) }}
            </p>

            <div class="card-footer">
                <div class="brand-seal">
                    <i class="fa-solid fa-compass"></i>
                    <span>DISEKITAR</span>
                </div>
                <a href="{{ $post->url() }}" target="_blank" rel="noopener" class="btn-view">
                    <span>Lihat Ulasan</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                </a>
            </div>
        </div>
    </div>

</body>
</html>
