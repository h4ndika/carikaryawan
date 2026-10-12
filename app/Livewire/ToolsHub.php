<?php

namespace App\Livewire;

use Livewire\Component;

class ToolsHub extends Component
{
    public function render()
    {
        return view('livewire.tools-hub')->layout('layouts.app', [
            'title' => 'Tools & Resources · CariKaryawan',
            'description' => 'Kumpulan tools dan resources untuk membantu perjalanan karir Anda: CV Builder, Interview Prep, dan lainnya.',
        ]);
    }
}
