<x-filament-widgets::widget>
    <section class="swm-panel swm-map-panel" wire:poll.30s>
        <div class="swm-section-heading">
            <div>
                <h2>Operations map</h2>
                <p class="swm-muted">Open reports and configured demo disposal centres across Selangor.</p>
            </div>
            <span class="swm-count">{{ $reportCount }} open · {{ $centreCount }} centres</span>
        </div>

        @if ($reportCount > 0)
            <div wire:key="operations-map-{{ md5(json_encode($mapData)) }}">
                <swm-operations-map wire:ignore class="swm-map" aria-label="Map of open reports and disposal centres">
                    <script type="application/json">@json($mapData)</script>
                    <div class="swm-map-canvas" data-map-canvas></div>
                    <p class="swm-map-status" data-map-status role="status">Loading map…</p>
                </swm-operations-map>
            </div>
            <div class="swm-map-legend" aria-label="Map legend">
                <span><i class="swm-map-dot swm-map-dot-high"></i>High risk</span>
                <span><i class="swm-map-dot swm-map-dot-elevated"></i>Elevated</span>
                <span><i class="swm-map-dot swm-map-dot-standard"></i>Standard</span>
                <span><i class="swm-map-dot swm-map-dot-disposal"></i>Disposal centre</span>
            </div>
            <p class="swm-map-note">Select a report marker to open its investigation. Base map © OpenStreetMap contributors.</p>
        @else
            <p class="swm-empty">No open reports with valid coordinates are available to map.</p>
        @endif
    </section>
</x-filament-widgets::widget>
