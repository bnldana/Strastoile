<div class="frise">
    @foreach($dates as $date)
    <a href="?date={{ $date['value'] }}"
        class="date-item {{ $selectedDate === $date['value'] ? 'active' : '' }}">
        {{ $date['label'] }}
    </a>
    @endforeach
</div>