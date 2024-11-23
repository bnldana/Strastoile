{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="options">
    <x-frise :selectedDate="$selectedDate" />
    <x-cinema-list :selected-cinemas="$selectedCinemas" :cinemas="$cinemas" />
</div>

@if($showtimes->isEmpty())
<p>Aucun film disponible pour la période sélectionnée.</p>
@else
<div class="film-wrapper">
    @foreach($films as $film)
    @php
    $filmShowtimes = $showtimes->where('film_id', $film->id);
    $filmShowtimes = $filmShowtimes->whereIn('cinema_id', $selectedCinemas);
    $showtimesByCinema = $filmShowtimes->groupBy('cinema_id');
    @endphp

    @if($showtimesByCinema->isNotEmpty())
    <div class="film-card card">
        <div class="poster-container">
            <img src="{{ $film->poster_url }}" alt="{{ $film->title }} Poster" class="film-poster card-img-top">
        </div>
        <div class="card-body">
            <div class="film-title">
                <h5 class="film-title">{{ $film->title }}</h5>
            </div>
            <div class="film-info">
                @if($film->release_date || $film->duration)
                <p>
                    {{ $film->release_date ? \Carbon\Carbon::parse($film->release_date)->locale('fr')->isoFormat('YYYY') : '' }}
                    {{ $film->release_date && $film->duration ? '·' : '' }}
                    {{ $film->duration ?: '' }}
                </p>
                @endif
            </div>

            <div class="film-showtimes">
                <ul class="nav nav-tabs" id="filmTabs-{{ $film->id }}" role="tablist">
                    @foreach($showtimesByCinema as $cinemaId => $cinemaShowtimes)
                    <li class="nav-item">
                        <a class="nav-link @if($loop->first) active @endif"
                            id="tab-{{ $film->id }}-{{ $cinemaId }}"
                            data-toggle="tab"
                            href="#tab-content-{{ $film->id }}-{{ $cinemaId }}"
                            role="tab">
                            {{ $cinemaShowtimes->first()->cinema->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="filmTabsContent-{{ $film->id }}">
                    @foreach($showtimesByCinema as $cinemaId => $cinemaShowtimes)
                    <div class="tab-pane fade @if($loop->first) show active @endif"
                        id="tab-content-{{ $film->id }}-{{ $cinemaId }}"
                        role="tabpanel">
                        <ul class="cinema-showtime-list">
                            @foreach($cinemaShowtimes->groupBy('day') as $day => $showtimesPerDay)
                            <li class="cinema-showtime-item">
                                <div class="showtime-slots">
                                    @foreach(json_decode($cinemaShowtimes->first()->horaires) as $horaire)
                                    @php
                                    // Debug - afficher les formats de date
                                    \Log::info("Date format:", [
                                    'day' => $day,
                                    'carbon_format' => \Carbon\Carbon::parse($day)->format('Y-m-d')
                                    ]);
                                    @endphp
                                    <span class="time-slot"
                                        data-time="{{ $horaire }}"
                                        data-date="{{ \Carbon\Carbon::parse($day)->format('Y-m-d') }}">{{ $horaire }}</span>
                                    @endforeach
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endif

<script>
    function updateTimeSlots() {
        const now = new Date();
        const today = now.toISOString().split('T')[0]; // Format YYYY-MM-DD
        const currentHours = now.getHours();
        const currentMinutes = now.getMinutes();

        console.log('Date actuelle:', today); // Debug

        document.querySelectorAll('.time-slot').forEach(slot => {
            const time = slot.dataset.time;
            const date = slot.dataset.date;

            console.log('Comparaison:', {
                slotDate: date,
                today,
                time
            }); // Debug

            if (date === today) {
                const [hours, minutes] = time.split('h').map(num => parseInt(num || 0));

                if (hours < currentHours || (hours === currentHours && minutes <= currentMinutes)) {
                    slot.classList.add('past');
                    console.log('Marqué comme passé:', time); // Debug
                } else {
                    slot.classList.remove('past');
                }
            } else {
                slot.classList.remove('past');
            }
        });
    }

    // Mettre à jour initialement
    updateTimeSlots();

    // Mettre à jour toutes les minutes
    setInterval(updateTimeSlots, 60000);
</script>
@endsection