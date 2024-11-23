{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<x-frise :selectedDate="$selectedDate" />
<x-cinema-list :selected-cinemas="$selectedCinemas" :cinemas="$cinemas" />

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
                @if($film->release_date)
                <p>{{ \Carbon\Carbon::parse($film->release_date)->locale('fr')->isoFormat('YYYY') }}</p>
                @endif
            </div>
            @if($film->duration)
            <p>{{ $film->duration }}</p>
            @endif

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
                                    @foreach(json_decode($showtimesPerDay->first()->horaires) as $horaire)
                                    <span class="time-slot">{{ $horaire }}</span>
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
@endsection