<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Showtime;

class Cinema extends Model
{
    protected $fillable = ['name'];

    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
}
