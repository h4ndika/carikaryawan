<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolUsage extends Model
{
    protected $fillable = [
        'tool_name',
        'action',
        'post_id',
        'target_position',
        'company_name',
        'ip_address',
        'user_agent',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public static function log(
        string $toolName,
        string $action = 'generate',
        ?int $postId = null,
        ?string $targetPosition = null,
        ?string $companyName = null,
        array $metadata = [],
        string $status = 'success'
    ): self {
        return self::create([
            'tool_name' => $toolName,
            'action' => $action,
            'post_id' => $postId,
            'target_position' => $targetPosition,
            'company_name' => $companyName,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'metadata' => !empty($metadata) ? $metadata : null,
            'status' => $status,
        ]);
    }
}
