<x-filament-widgets::widget>
    <section class="swm-panel swm-map-panel" wire:poll.30s>
        <div class="swm-section-heading">
            <div>
                <h2>Current citizen reports</h2>
                <p class="swm-muted">Open illegal-dumping submissions received from the public.</p>
            </div>
            <span class="swm-count">{{ $reportCount }} reports · {{ $locationCount }} locations</span>
        </div>

        @if ($reportCount > 0)
            <div wire:key="current-dumping-map-{{ md5(json_encode($mapData)) }}">
                <swm-operations-map wire:ignore class="swm-map" aria-label="Map of current citizen dumping reports">
                    <script type="application/json">@json($mapData)</script>
                    <div class="swm-map-canvas" data-map-canvas></div>
                    <p class="swm-map-status" data-map-status role="status">Loading map…</p>
                </swm-operations-map>
            </div>
            <div class="swm-map-legend" aria-label="Citizen reports legend">
                <span><i class="swm-map-dot swm-map-dot-high"></i>High risk</span>
                <span><i class="swm-map-dot swm-map-dot-elevated"></i>Elevated</span>
                <span><i class="swm-map-dot swm-map-dot-standard"></i>Standard</span>
                <span>Numbered pins contain multiple reports at one location</span>
            </div>
        @else
            <p class="swm-empty">No current reports with valid coordinates are available to map.</p>
        @endif
    </section>
</x-filament-widgets::widget>
