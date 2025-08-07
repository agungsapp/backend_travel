<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorit extends Model
{
    //
    protected $fillable = [
        'user_id',
        'wisata_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wisata(): BelongsTo
    {
        return $this->belongsTo(Wisata::class);
    }
}
