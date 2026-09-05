<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReport extends Model
{
    protected $table = 'ai_reports';

    protected $guarded = ['id'];

    protected $casts = [
        'report_data' => 'array',
        'tool_calls' => 'array',
        'tokens' => 'array',
        'duration' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
