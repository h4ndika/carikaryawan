<?php

namespace App\Livewire;

use Livewire\Component;

class PageShow extends Component
{
    public string $page = '';

    public function mount(string $page = ''): void
    {
        $this->page = $page;
        
        $validPages = ['tentang-kami', 'kebijakan-privasi', 'syarat-ketentuan', 'kontak', 'disclaimer'];
        if (! in_array($this->page, $validPages, true)) {
            abort(404);
        }
    }

    public function render()
    {
        $meta = match ($this->page) {
            'tentang-kami' => [
                'title' => 'Tentang Kami — Redaksi Jurnal Kurasi Tempat',
                'description' => 'Mengenal DISEKITAR: publikasi kurasi editorial independen yang mengangkat cerita di balik tempat, kuliner, dan kehidupan kota di Indonesia.',
            ],
            'kebijakan-privasi' => [
                'title' => 'Kebijakan Privasi (Privacy Policy)',
                'description' => 'Kebijakan privasi DISEKITAR mengenai pengumpulan data, penggunaan kuki, dan kepatuhan Google AdSense dalam menjaga privasi pengunjung.',
            ],
            'syarat-ketentuan' => [
                'title' => 'Syarat dan Ketentuan Layanan',
                'description' => 'Syarat dan ketentuan umum penggunaan situs dan panduan kurasi editorial di platform DISEKITAR.',
            ],
            'kontak' => [
                'title' => 'Hubungi Kami — Redaksi & Layanan Informasi',
                'description' => 'Hubungi tim redaksi DISEKITAR untuk saran, pertanyaan liputan, kemitraan, atau koreksi data tempat.',
            ],
            'disclaimer' => [
                'title' => 'Penafian (Disclaimer)',
                'description' => 'Pernyataan penafian terkait keakuratan data, jam operasional, harga, dan kepemilikan hak cipta materi di DISEKITAR.',
            ],
            default => [
                'title' => 'Informasi',
                'description' => 'Informasi publikasi DISEKITAR.',
            ],
        };

        $canonical = url('/' . $this->page);

        return view('livewire.page-show', [
            'page' => $this->page,
            'meta' => $meta,
        ])
            ->layout('layouts.app', [
                'title' => $meta['title'] . ' | DISEKITAR',
                'metaDescription' => $meta['description'],
                'canonicalUrl' => $canonical,
                'metaRobots' => 'index,follow',
            ])
            ->title($meta['title'] . ' | DISEKITAR');
    }
}
