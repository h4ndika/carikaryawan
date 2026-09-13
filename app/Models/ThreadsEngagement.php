<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreadsEngagement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'replied_at' => 'datetime',
    ];
}
