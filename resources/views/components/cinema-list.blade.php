{{-- resources/views/components/cinema-list.blade.php --}}
<div class="cinema-navigation bg-dark">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav class="nav cinema-nav">
                    <a href="{{ request()->fullUrlWithQuery(['cinemas' => null]) }}"
                        class="nav-link {{ $selectedCinemas->isEmpty() || $selectedCinemas->count() === $cinemas->count() ? 'active text-primary' : 'text-white' }}">
                        TOUS LES CINÉMAS
                    </a>

                    @foreach($cinemas as $cinema)
                    <a href="{{ request()->fullUrlWithQuery(['cinemas' => [$cinema->id]]) }}"
                        class="nav-link {{ $selectedCinemas->contains($cinema->id) && $selectedCinemas->count() === 1 ? 'active text-primary' : 'text-white' }}">
                        {{ strtoupper($cinema->name) }}
                    </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>
</div>