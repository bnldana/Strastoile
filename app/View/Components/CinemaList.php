<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use App\Models\Cinema;

class CinemaList extends Component
{
    public $selectedCinemas;

    public function __construct($selectedCinemas)
    {
        // Convertir en collection si ce n'est pas déjà une collection
        $this->selectedCinemas = $selectedCinemas instanceof Collection
            ? $selectedCinemas
            : collect($selectedCinemas);
    }

    public function render()
    {
        // Récupérer les cinémas directement dans le composant
        $cinemas = Cinema::orderBy('name')->get();

        return view('components.cinema-list', [
            'cinemas' => $cinemas
        ]);
    }
}
