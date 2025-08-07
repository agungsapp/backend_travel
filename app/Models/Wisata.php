<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wisata extends Model
{
    //
    protected $fillable = [
        'nama',
        'kategori_id',
        'image',
        'deskripsi',
        'alamat',
        'kordinat'
    ];

    protected $casts = [
        'kordinat' => 'array',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorits', 'wisata_id', 'user_id');
    }
}
