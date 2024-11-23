<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class FriseController extends Controller
{
    public function getFrise()
    {
        Carbon::setLocale('fr');

        $today = Carbon::today();
        $dates = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i);
            $dates[] = [
                'label' => $i === 0 ? "AUJOURD'HUI" : ($i === 1 ? 'DEMAIN' : $date->translatedFormat('D d')),
                'value' => $date->format('Y-m-d')
            ];
        }

        return $dates;
    }
}
