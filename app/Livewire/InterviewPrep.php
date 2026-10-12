<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;

class InterviewPrep extends Component
{
    public ?int $jobPostId = null;
    public ?Post $jobPost = null;

    public function mount(): void
    {
        $this->jobPostId = request()->has('job') ? (int) request()->query('job') : null;

        if ($this->jobPostId) {
            $this->jobPost = Post::query()
                ->where('id', $this->jobPostId)
                ->where('status', 'published')
                ->first();
        }
    }

    public function render()
    {
        return view('livewire.interview-prep', [
            'jobPost' => $this->jobPost,
        ])->layout('layouts.app', [
            'title' => 'Interview Prep AI · Latihan Wawancara Kerja · CariKaryawan',
            'description' => 'Latih wawancara kerja dengan AI. Dapatkan pertanyaan general, behavioral, dan teknis yang disesuaikan dengan posisi target.',
        ]);
    }
}
