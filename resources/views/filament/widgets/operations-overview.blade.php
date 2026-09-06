<x-filament-widgets::widget>
    <!-- THESIS: Evidence-led clearance desk. OWN-WORLD: Figtree, soft Selangor red and gold, Filament controls.
    STORY: Receive, investigate, dispatch and retain the outcome. FIRST VIEWPORT: summary strip, wide risk queue and narrow report inbox; simulate action in header.
    FORM: Existing admin extension, agreed government scope. FINISH: code and endpoint verification; Takip owns visual review. -->
    <div class="swm-overview" aria-label="Case summary" wire:poll.15s>
        @foreach ([['Open cases', $open, 'Awaiting closure'], ['High risk', $high, 'Score of 80 or higher'], ['Overdue', $overdue, 'Past intervention deadline'], ['Solved', $solved, 'Clearance recorded']] as [$label, $count, $description])
            <div class="swm-overview-item">
                <span>{{ $label }}</span>
                <strong>{{ number_format($count) }}</strong>
                <small>{{ $description }}</small>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
