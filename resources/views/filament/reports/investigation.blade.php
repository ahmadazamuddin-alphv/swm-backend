@php
    $report = $getRecord();
    $report->loadMissing(['wasteCategory', 'zone.responsibleParty.contractor', 'assignment.responsibleParty.contractor', 'assignment.driver', 'resolutionProofs.uploader', 'activities.user']);
    $recommendations = app(\App\Services\ReportRecommendationService::class);
    $suggestion = $recommendations->recommend($report);
    $centres = $recommendations->disposalOptions($report->latitude === null ? null : (float) $report->latitude, $report->longitude === null ? null : (float) $report->longitude, $report->waste_category_id);
    $destination = $centres->first();
    $driverOption = $recommendations->availableDrivers($report)->first();
    $owner = $report->zone?->responsibleParty;
    $assignment = $report->assignment;
    $team = $assignment?->responsibleParty;
    $deadline = $assignment?->deadline ?? $suggestion['suggested_deadline'];
    $routeDriver = $assignment?->driver ?? ($driverOption['driver'] ?? null);
    $routeDriverIsMapped = $routeDriver && $recommendations->validCoordinates($routeDriver->base_latitude, $routeDriver->base_longitude);
    $reportMapTone = $report->risk_score >= 80 ? 'high' : ($report->risk_score >= 60 ? 'elevated' : 'standard');
    $routePoints = $destination ? [
        [
            'id' => 'report', 'kind' => 'report',
            'tone' => $reportMapTone,
            'latitude' => (float) $report->latitude, 'longitude' => (float) $report->longitude,
            'title' => $report->reference, 'description' => $report->zone?->name ?? 'Report location',
            'meta' => $report->risk_score.'/100 · '.$report->status->label(),
        ],
        [
            'id' => 'disposal', 'kind' => 'disposal', 'tone' => 'disposal',
            'latitude' => (float) $destination['centre']->latitude, 'longitude' => (float) $destination['centre']->longitude,
            'title' => $destination['centre']->name, 'description' => $destination['centre']->address,
            'meta' => $destination['distance'].' km straight-line from report',
        ],
    ] : [];
    if ($routeDriverIsMapped) {
        array_unshift($routePoints, [
            'id' => 'driver', 'kind' => 'driver', 'tone' => 'driver',
            'latitude' => (float) $routeDriver->base_latitude, 'longitude' => (float) $routeDriver->base_longitude,
            'title' => $routeDriver->name, 'description' => $routeDriver->contractor?->name.' · '.$routeDriver->vehicle_plate,
            'meta' => $assignment ? 'Assigned lead driver depot' : 'Suggested available driver depot',
        ]);
    }
    $routeMapData = [
        'points' => $routePoints,
        'lines' => array_values(array_filter([
            $routeDriverIsMapped ? ['from' => 'driver', 'to' => 'report', 'tone' => 'approach'] : null,
            $destination ? ['from' => 'report', 'to' => 'disposal', 'tone' => 'clearance'] : null,
        ])),
        'routing' => $destination ? ['endpoint' => 'https://router.project-osrm.org/route/v1/driving'] : null,
    ];
    $mediaUrl = fn ($path) => str_starts_with($path, '/demo/') ? url($path) : url('/storage/'.ltrim($path, '/'));
@endphp

<div class="swm-case">
    <div class="swm-case-status">
        <x-filament::badge :color="$report->status->getColor()">{{ $report->status->label() }}</x-filament::badge>
        <span><strong>{{ $report->risk_score }}/100</strong> · {{ $report->priorityLabel() }}</span>
        <span>Submitted {{ $report->submitted_at?->timezone('Asia/Kuala_Lumpur')->format('d M Y, H:i') ?? 'Unknown' }} MYT</span>
        @if (str_starts_with($report->reference, 'OPS-'))
            <span class="swm-demo-label">Fictional demo case</span>
        @endif
    </div>
    @error('status')<p class="swm-error" role="alert">{{ $message }}</p>@enderror

    @if (!$report->isOpen())
        <div class="swm-outcome" role="status">
            <x-filament::icon :icon="$report->status === \App\Enums\ReportStatus::Solved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" class="swm-icon" />
            <div>
                <h2>{{ $report->status === \App\Enums\ReportStatus::Solved ? 'Clearance recorded' : 'Closed as a false report' }}</h2>
                <p>{{ $report->resolved_at?->timezone('Asia/Kuala_Lumpur')->format('d M Y, H:i') }} MYT</p>
                @if ($report->false_report_reason)<p>{{ $report->false_report_reason }}</p>@endif
            </div>
        </div>
    @endif

    <div class="swm-case-grid">
        <div class="swm-case-main">
            <section class="swm-panel">
                <div class="swm-section-heading"><h2>Report evidence</h2><span class="swm-muted">{{ count($report->photos ?? []) }} photo(s)</span></div>
                @if ($report->photos)
                    <div class="swm-evidence-gallery">
                        @foreach ($report->photos as $photo)
                            <a href="{{ $mediaUrl($photo) }}" target="_blank" rel="noopener noreferrer" aria-label="Open report photograph {{ $loop->iteration }}">
                                <img src="{{ $mediaUrl($photo) }}" alt="Report evidence for {{ $report->reference }}, photo {{ $loop->iteration }}" width="960" height="720" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="swm-empty">No photograph attached to this report. Review the description and location before assigning a team.</p>
                @endif
                <p class="swm-description">{{ $report->notes ?: 'No description provided.' }}</p>
                <dl class="swm-facts">
                    <div><dt>Waste type</dt><dd>{{ $report->wasteCategory?->name ?? 'Not classified' }}</dd></div>
                    <div><dt>Source</dt><dd>{{ $report->source->label() }}</dd></div>
                    <div><dt>Area</dt><dd>{{ $report->zone?->name ?? 'Not identified' }}</dd></div>
                    <div><dt>Taman / postcode</dt><dd>{{ $report->zone?->taman ?? 'Not recorded' }} / {{ $report->zone?->postcode ?? 'Not recorded' }}</dd></div>
                    <div><dt>Latitude</dt><dd>{{ $report->latitude ?? 'Not recorded' }}</dd></div>
                    <div><dt>Longitude</dt><dd>{{ $report->longitude ?? 'Not recorded' }}</dd></div>
                </dl>
            </section>

            <section class="swm-panel">
                <h2>Reporter</h2>
                <dl class="swm-facts">
                    <div><dt>Name</dt><dd>{{ $report->reporter_name ?: 'Not provided' }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $report->reporter_phone ?: 'Not provided' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $report->reporter_email ?: 'Not provided' }}</dd></div>
                </dl>
                <p class="swm-muted">Reporter details are for investigation. Demo contacts are fictional.</p>
            </section>

            <section class="swm-panel">
                <div class="swm-section-heading"><h2>Resolution proof</h2><span class="swm-muted">{{ $report->resolutionProofs->count() }} attachment(s)</span></div>
                @forelse ($report->resolutionProofs as $proof)
                    <figure class="swm-proof">
                        <a href="{{ $mediaUrl($proof->path) }}" target="_blank" rel="noopener noreferrer" aria-label="Open clearance photograph">
                            <img src="{{ $mediaUrl($proof->path) }}" alt="Clearance proof for {{ $report->reference }}" width="960" height="720" loading="lazy">
                        </a>
                        <figcaption>
                            <strong>{{ $proof->uploader?->name ?? 'Demo operations team' }}</strong>
                            <span>{{ $proof->created_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, H:i') }} MYT</span>
                            @if ($proof->notes)<p>{{ $proof->notes }}</p>@endif
                        </figcaption>
                    </figure>
                @empty
                    <p class="swm-empty">{{ $report->isOpen() ? 'Use “Record resolution” after clearance. A photo is required before the case can be marked solved.' : 'No clearance photograph was recorded for this case.' }}</p>
                @endforelse
            </section>

            <section class="swm-panel">
                <h2>Case history</h2>
                <ol class="swm-timeline">
                    @foreach ($report->activities as $activity)
                        <li>
                            <div><strong>{{ $activity->event }}</strong><time>{{ $activity->created_at->timezone('Asia/Kuala_Lumpur')->format('d M, H:i') }} MYT</time></div>
                            <p>{{ $activity->description }}</p>
                            <small>{{ $activity->user?->name ?? 'Demo operations team' }}</small>
                        </li>
                    @endforeach
                    <li>
                        <div><strong>Report received</strong><time>{{ $report->submitted_at?->timezone('Asia/Kuala_Lumpur')->format('d M, H:i') }} MYT</time></div>
                        <p>{{ $report->source->label() }} report entered the case queue.</p>
                    </li>
                </ol>
            </section>
        </div>

        <aside class="swm-case-side" aria-label="Clearance coordination">
            <section class="swm-panel">
                <h2>Accountability</h2>
                <div class="swm-party">
                    <h3>Responsible for the area</h3>
                    <strong>{{ $owner?->name ?? 'Area owner not identified' }}</strong>
                    <p>{{ $owner?->type?->label() ?? 'Assign an area in investigation details.' }}</p>
                    @if ($owner?->contractor?->contact_person)<p>Contact: {{ $owner->contractor->contact_person }}</p>@endif
                    <small>{{ $owner?->phone ?: 'Phone not recorded' }}</small>
                    @if ($owner?->email)<small>{{ $owner->email }}</small>@endif
                </div>
                <div class="swm-party">
                    <h3>Assigned to this case</h3>
                    @if ($assignment)
                        <strong>{{ $team?->name ?? 'Team no longer available' }}</strong>
                        <p>{{ $assignment->driver?->name ?? 'Driver not recorded' }} · {{ $assignment->driver?->vehicle_plate ?? 'No vehicle plate' }}</p>
                        <p>{{ $assignment->manpower }} people · {{ $assignment->lorries }} lorries · {{ $assignment->status->label() }}</p>
                        <small>{{ $team?->contractor?->contact_person ?? 'Team contact' }} · {{ $team?->phone ?: 'Phone not recorded' }}</small>
                        <small>Deadline {{ $assignment->deadline?->timezone('Asia/Kuala_Lumpur')->format('d M Y, H:i') }} MYT</small>
                    @else
                        <p>Unassigned. Review the evidence, then use “Assign team”.</p>
                    @endif
                </div>
            </section>

            @if ($report->isOpen())
                <section class="swm-panel">
                    <h2>Clearance recommendation</h2>
                    <p class="swm-muted">Demo recommendation · rules-based</p>
                    <dl class="swm-recommendation">
                        <div><dt>People</dt><dd>{{ $suggestion['suggested_manpower'] }}</dd></div>
                        <div><dt>Lorries</dt><dd>{{ $suggestion['suggested_lorries'] }}</dd></div>
                    </dl>
                    <p>{{ $recommendations->rationale($report) }}</p>
                    <p class="swm-deadline {{ $deadline?->isPast() ? 'swm-overdue' : '' }}">
                        {{ $deadline?->isPast() ? 'Overdue' : 'Intervene by' }} · {{ $deadline?->timezone('Asia/Kuala_Lumpur')->format('d M, H:i') }} MYT
                    </p>
                    <p class="swm-muted">Assigning applies your confirmed resources and deadline. It does not book a real crew.</p>
                </section>
            @endif

            <section class="swm-panel">
                <h2>Disposal plan</h2>
                <p class="swm-muted">Interactive road route · live demo estimate</p>
                @if ($destination)
                    <div wire:key="route-map-{{ md5(json_encode($routeMapData)) }}">
                        <swm-operations-map wire:ignore class="swm-map swm-map-route" aria-label="Map from driver depot through the report location to the recommended disposal centre">
                            <script type="application/json">@json($routeMapData)</script>
                            <div class="swm-map-canvas" data-map-canvas></div>
                            <p class="swm-map-status" data-map-status role="status">Loading map…</p>
                            <p class="swm-map-route-status" data-map-route-status role="status">Calculating road route…</p>
                        </swm-operations-map>
                    </div>
                    <div class="swm-map-legend swm-map-legend-route" aria-label="Route map legend">
                        @if ($routeDriverIsMapped)<span><i class="swm-map-dot swm-map-dot-driver"></i>Driver depot</span>@endif
                        <span><i class="swm-map-dot swm-map-dot-{{ $reportMapTone }}"></i>Report</span>
                        <span><i class="swm-map-dot swm-map-dot-disposal"></i>Disposal centre</span>
                    </div>
                    <div class="swm-route-stop">
                        <h3>{{ $assignment ? 'Assigned lead driver' : 'Suggested available driver' }}</h3>
                        @if ($routeDriver)
                            <strong>{{ $routeDriver->name }}</strong>
                            <p>{{ $routeDriver->contractor?->name ?? 'Contractor not recorded' }} · {{ $routeDriver->vehicle_plate }}</p>
                            <small>{{ !$routeDriverIsMapped ? 'Depot coordinates not recorded.' : round($recommendations->haversineKm((float) $routeDriver->base_latitude, (float) $routeDriver->base_longitude, (float) $report->latitude, (float) $report->longitude), 1).' km from depot to report, straight-line.' }}</small>
                        @else
                            <p>No available suitable driver. Review driver availability and waste categories before assigning.</p>
                        @endif
                    </div>
                    <div class="swm-route-stop">
                        <h3>Recommended destination</h3>
                        <strong>{{ $destination['centre']->name }}</strong>
                        <p>{{ $destination['centre']->address }}</p>
                        <p><strong>{{ $destination['distance'] }} km</strong> from report to disposal, straight-line.</p>
                        <small>{{ $destination['centre']->latitude }}, {{ $destination['centre']->longitude }}</small>
                    </div>
                    @if ($centres->count() > 1)
                        <details class="swm-alternatives">
                            <summary>Compare {{ $centres->count() }} suitable centres</summary>
                            <ul>
                                @foreach ($centres as $option)
                                    <li><span>{{ $option['centre']->name }}</span><strong>{{ $option['distance'] }} km</strong></li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                    <p class="swm-muted">The centre is selected by straight-line distance among locations accepting {{ $report->wasteCategory?->name }}. The highlighted line follows roads when the OSRM demo service is available; a dashed direct line remains as fallback. Base map © OpenStreetMap contributors. Depot locations and acceptance rules are demo fixtures. Traffic and cost are not calculated.</p>
                @else
                    <p class="swm-empty">No suitable disposal centre can be suggested. Check the report coordinates, waste category and centre acceptance list.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
