<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    /**
     * Tampilan Widget Kartu Interaktif untuk di-embed via iframe
     */
    public function postWidget(string $slug)
    {
        $post = Post::with(['category', 'city', 'province'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()
            ->view('widgets.post-embed', compact('post'))
            ->header('X-Frame-Options', 'ALLOWALL');
    }

    /**
     * Tampilan Lencana / Badge Mini untuk di-embed via iframe
     */
    public function badgeWidget(string $slug)
    {
        $post = Post::with(['category', 'city'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()
            ->view('widgets.badge-embed', compact('post'))
            ->header('X-Frame-Options', 'ALLOWALL');
    }
}
