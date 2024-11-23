<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    protected $fillable = ['cinema_id', 'film_id', 'day', 'horaires'];

    protected $casts = [
        'day' => 'date',
        'horaires' => 'json',
    ];

    public function cinema()
    {
        return $this->belongsTo(Cinema::class);
    }

    public function film()
    {
        return $this->belongsTo(Film::class);
    }
}
