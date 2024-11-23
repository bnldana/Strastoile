<div class="frise">
    @foreach($dates as $date)
    <a href="?date={{ $date['value'] }}"
        class="date-item {{ $selectedDate === $date['value'] ? 'active' : '' }}">
        {{-- Version Desktop --}}
        <span class="desktop-date">
            {{ $date['label'] }}
        </span>

        {{-- Version Mobile --}}
        <span class="mobile-date">
            <span class="weekday">{{ \Carbon\Carbon::parse($date['value'])->locale('fr')->isoFormat('ddd') }}</span>
            <span class="day-number">{{ \Carbon\Carbon::parse($date['value'])->format('d') }}</span>
        </span>
    </a>
    @endforeach
</div>