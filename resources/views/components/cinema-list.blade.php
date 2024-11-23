{{-- resources/views/components/cinema-list.blade.php --}}
<div class="cinema-navigation bg-dark">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                {{-- Version Desktop --}}
                <nav class="nav cinema-nav desktop-nav">
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

                {{-- Version Mobile Select --}}
                <div class="mobile-nav">
                    <select class="form-select bg-dark text-white" onchange="window.location.href=this.value;">
                        <option value="{{ request()->fullUrlWithQuery(['cinemas' => null]) }}"
                            {{ ($selectedCinemas->isEmpty() || $selectedCinemas->count() === $cinemas->count()) ? 'selected' : '' }}>
                            TOUS LES CINÉMAS
                        </option>
                        @foreach($cinemas as $cinema)
                        <option value="{{ request()->fullUrlWithQuery(['cinemas' => [$cinema->id]]) }}"
                            {{ ($selectedCinemas->contains($cinema->id) && $selectedCinemas->count() === 1) ? 'selected' : '' }}>
                            {{ strtoupper($cinema->name) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>