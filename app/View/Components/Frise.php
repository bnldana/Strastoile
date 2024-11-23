<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Http\Controllers\FriseController;

class Frise extends Component
{
    public $dates;
    public $selectedDate;

    public function __construct($selectedDate = null)
    {
        $this->selectedDate = $selectedDate ?? today()->format('Y-m-d');

        $friseController = new FriseController();
        $this->dates = $friseController->getFrise();
    }

    public function render()
    {
        return view('components.frise');
    }
}
