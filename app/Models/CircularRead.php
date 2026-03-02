<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircularRead extends Model
{
    protected $fillable = ['circular_id', 'user_id', 'read_at'];
    protected $casts    = ['read_at' => 'datetime'];

    public function circular(): BelongsTo { return $this->belongsTo(Circular::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
}
