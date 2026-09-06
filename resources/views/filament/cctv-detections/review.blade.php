@php
    $record = $getRecord();
    $record->loadMissing(['wasteCategory', 'report']);
    $result = $record->raw_result ?? [];
    $events = collect($result['events'] ?? []);
    $location = $result['location'] ?? null;
    $videoUrl = str_starts_with((string) $record->video_path, '/demo/') ? url($record->video_path) : \Illuminate\Support\Facades\Storage::disk('public')->url($record->video_path);
@endphp
<div class="swm-cctv-review swm-cctv-console" data-cctv-review-root>
    <div class="swm-cctv-context"><span>{{ data_get($result, 'camera.label', 'Camera metadata unavailable') }}</span><x-filament::badge color="gray">Simulated analysis</x-filament::badge></div>
    <div class="swm-cctv-desk">
        <section class="swm-cctv-player-panel swm-cctv-evidence" aria-label="Video and incident timeline">
            <div class="swm-cctv-section-title"><h2>Play the source clip</h2><span data-cctv-time>00:00 / --:--</span></div>
            <swm-cctv-review class="swm-cctv-player" aria-label="CCTV video with simulated detection overlays">
                <script type="application/json">@json(['events' => $result['events'] ?? []])</script>
                <div class="swm-cctv-stage" data-cctv-stage>
                    <div class="swm-cctv-video-shell" data-cctv-video-shell>
                        <video controls preload="metadata" playsinline aria-label="CCTV source video"><source src="{{ $videoUrl }}">Your browser cannot play this video format.</video>
                        <div class="swm-cctv-overlay" data-cctv-overlay aria-hidden="true"></div>
                        <p class="swm-cctv-video-status" data-cctv-video-status role="status" hidden></p>
                    </div>
                </div>
                <div class="swm-cctv-filter">
                    <label for="cctv-confidence-{{ $record->id }}">Minimum confidence <output data-cctv-confidence-value>50%</output></label>
                    <input id="cctv-confidence-{{ $record->id }}" type="range" min="0" max="100" value="50" step="5" data-cctv-confidence>
                </div>
            </swm-cctv-review>
            <div class="swm-cctv-section-title"><h2>Incident timeline</h2><span>Select an observation to play</span></div>
            <ol class="swm-cctv-events">
                @forelse ($events as $event)
                    <li><button type="button" class="swm-cctv-event" data-cctv-event="{{ $loop->index }}">
                        <span class="swm-cctv-event-time" data-cctv-event-time>{{ number_format((float) $event['timestamp_ratio'] * 100, 0) }}% of clip</span>
                        <strong>{{ $event['label'] }}</strong>
                        <span>{{ number_format((float) $event['confidence'], 1) }}% · {{ $event['incident'] ? 'Incident evidence' : 'Observation' }}</span>
                    </button></li>
                @empty
                    <li>No detections were stored for this run.</li>
                @endforelse
            </ol>
            <p class="swm-cctv-filter-empty" data-cctv-filter-empty role="status" hidden>No observations meet this threshold. Lower the minimum confidence to see more.</p>
            <noscript><p>JavaScript is required for timestamp jumps and detection overlays. Use the video controls to review the clip.</p></noscript>
        </section>
        <aside class="swm-cctv-finding" aria-label="Detection finding">
            <div class="swm-cctv-section-title"><h2>Detection finding</h2></div>
            <div class="swm-cctv-result">
                <x-filament::badge :color="$record->activity_detected ? 'danger' : 'gray'">{{ $record->activity_detected ? 'Activity detected' : 'No activity detected' }}</x-filament::badge>
                <strong>{{ $record->wasteCategory?->name ?? 'Not classified' }}</strong>
                <p>{{ number_format((float) ($record->confidence ?? 0), 1) }}% confidence · {{ $events->where('incident', true)->count() }} incident events</p>
            </div>
            <p class="swm-cctv-summary">{{ data_get($result, 'summary', 'No analysis summary is available.') }}</p>
            <dl class="swm-cctv-facts">
                <div><dt>Coordinates</dt><dd>{{ $record->latitude ?? 'Unavailable' }}{{ $record->longitude !== null ? ', '.$record->longitude : '' }}</dd></div>
                <div><dt>Location source</dt><dd>{{ $location['source'] ?? 'No metadata' }}</dd></div>
                <div><dt>Uploaded</dt><dd>{{ $record->created_at?->timezone('Asia/Kuala_Lumpur')->format('d M Y, H:i') }} MYT</dd></div>
            </dl>
            @if ($record->report)
                <a class="swm-cctv-report-link" href="{{ \App\Filament\Resources\Reports\ReportResource::getUrl('view', ['record' => $record->report]) }}">Open report {{ $record->report->reference }} <x-heroicon-o-arrow-up-right /></a>
            @elseif ($record->activity_detected)
                <p class="swm-cctv-next-step">Review the clip, then use “Create report from detection” to add this incident to the government queue.</p>
            @else
                <p class="swm-cctv-next-step">No incident was detected in this scenario. Review the clip or rerun the demo analysis with another profile.</p>
            @endif
            <details class="swm-cctv-details">
                <summary>Evidence notes &amp; analysis details</summary>
                <p>{{ data_get($result, 'evidence_note', 'Review the clip before creating a case.') }}</p>
                <p>{{ data_get($result, 'scenario_label', 'Analysis run') }} · {{ data_get($result, 'engine', 'SWM Vision POC') }}</p>
                <ul>@foreach (($result['limitations'] ?? []) as $limitation)<li>{{ $limitation }}</li>@endforeach</ul>
            </details>
            <p class="swm-cctv-demo-note">This is a deterministic computer-vision simulation. An officer must review the evidence; it is not a trained model or a legal finding.</p>
        </aside>
    </div>
</div>
