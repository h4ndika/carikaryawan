<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatSession extends Model
{
    protected $table = 'ai_chat_sessions';

    protected $guarded = ['id'];

    protected $casts = [
        'enable_database_access' => 'boolean',
        'messages' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
