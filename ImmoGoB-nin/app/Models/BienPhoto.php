<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BienPhoto extends Model
{
    protected $fillable = ['bien_id', 'chemin', 'is_principale'];

    protected function casts(): array
    {
        return ['is_principale' => 'boolean'];
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }
}
