<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationImmogo extends Model
{
    protected $table = 'notifications_immogo';

    protected $fillable = ['user_id', 'titre', 'message', 'lien', 'lu'];

    protected function casts(): array
    {
        return ['lu' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
