<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;

class CvBuilder extends Component
{
    public ?int $jobPostId = null;
    public ?Post $jobPost = null;

    public function mount(?int $job = null): void
    {
        $this->jobPostId = $job ?: (request()->has('job') ? (int) request()->query('job') : null);

        if ($this->jobPostId) {
            $this->jobPost = Post::query()
                ->where('id', $this->jobPostId)
                ->where('status', 'published')
                ->first();
        }
    }

    public function render()
    {
        return view('livewire.cv-builder', [
            'jobPost' => $this->jobPost,
        ])->layout('layouts.app', [
            'title' => 'ATS CV & Resume Builder · Kurasi Karir DISEKITAR',
            'description' => 'Buat CV ATS-friendly yang rapi, profesional, dan siap cetak. Lengkapi data diri, pengalaman, pendidikan, serta keahlian dengan panduan analisis real-time.',
        ]);
    }
}
