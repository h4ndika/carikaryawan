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
                'title' => 'Tentang Kami',
                'description' => 'Mengenal CariKaryawan: agregator lowongan kerja Indonesia dengan tools CV, surat lamaran, dan persiapan interview.',
            ],
            'kebijakan-privasi' => [
                'title' => 'Kebijakan Privasi (Privacy Policy)',
                'description' => 'Kebijakan privasi CariKaryawan mengenai pengumpulan data, penggunaan kuki, iklan, dan pemrosesan data pada tools karier.',
            ],
            'syarat-ketentuan' => [
                'title' => 'Syarat dan Ketentuan Layanan',
                'description' => 'Syarat dan ketentuan penggunaan situs dan tools karier CariKaryawan.',
            ],
            'kontak' => [
                'title' => 'Hubungi Kami',
                'description' => 'Hubungi CariKaryawan untuk pertanyaan, kemitraan, atau permintaan penghapusan/koreksi lowongan.',
            ],
            'disclaimer' => [
                'title' => 'Penafian (Disclaimer)',
                'description' => 'Penafian CariKaryawan terkait keakuratan lowongan, tautan sumber asli, dan hasil tools berbasis AI.',
            ],
            default => [
                'title' => 'Informasi',
                'description' => 'Informasi CariKaryawan.',
            ],
        };

        $canonical = url('/' . $this->page);

        return view('livewire.page-show', [
            'page' => $this->page,
            'meta' => $meta,
        ])
            ->layout('layouts.app', [
                'title' => $meta['title'] . ' | CariKaryawan',
                'metaDescription' => $meta['description'],
                'canonicalUrl' => $canonical,
                'metaRobots' => 'index,follow',
            ])
            ->title($meta['title'] . ' | CariKaryawan');
    }
}
