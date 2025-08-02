<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostDetail extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public function post() {
        return $this->belongsTo(Post::class);
    }
}
