@php
    use App\Services\PotholeBudgetService;
@endphp

<x-filament-widgets::widget>
    <div class="swm-pothole-overview" wire:poll.15s>
        <div class="swm-overview" aria-label="Pothole service summary">
            @foreach ([
                ['Open cases', $openCount, 'Awaiting repair or inspection'],
                ['Critical risk', $criticalCount, 'AI risk score of 80 or higher'],
                ['Average street risk', $averageRisk.'%', 'Across open mapped cases'],
                ['Budget remaining', PotholeBudgetService::formatRm($budget['remaining_rm']), number_format($budget['utilization_pct'], 1).'% used this year'],
            ] as [$label, $count, $description])
                <div class="swm-overview-item">
                    <span>{{ $label }}</span>
                    <strong>{{ $count }}</strong>
                    <small>{{ $description }}</small>
                </div>
            @endforeach
        </div>

        <section class="swm-panel swm-map-panel">
            <div class="swm-section-heading">
                <div>
                    <h2>Current pothole activity</h2>
                    <p class="swm-muted">Mapped open cases, ranked by road-condition impact.</p>
                </div>
                <a class="swm-text-button" href="{{ $caseIndexUrl }}">All cases</a>
            </div>

            @if (count($mapData['points']) > 0)
                <div wire:key="pothole-overview-map-{{ md5(json_encode($mapData)) }}">
                    <swm-operations-map wire:ignore class="swm-map" aria-label="Map of current pothole cases">
                        <script type="application/json">@json($mapData)</script>
                        <div class="swm-map-canvas" data-map-canvas></div>
                        <p class="swm-map-status" data-map-status role="status">Loading map…</p>
                    </swm-operations-map>
                </div>
                <div class="swm-map-legend" aria-label="Pothole map legend">
                    <span><i class="swm-map-dot" style="background:#a84848"></i>GDP-led impact</span>
                    <span><i class="swm-map-dot" style="background:#e8c547"></i>Rakyat-led impact</span>
                    <span><i class="swm-map-dot" style="background:#6b9b6e"></i>Balanced impact</span>
                </div>
            @else
                <p class="swm-empty">No pothole cases with valid coordinates are available to map.</p>
            @endif
        </section>

        <div class="swm-pothole-overview-grid">
            <section class="swm-panel">
                <div class="swm-section-heading">
                    <div>
                        <h2>Repair priorities</h2>
                        <p class="swm-muted">Cases with the strongest impact for the estimated repair spend.</p>
                    </div>
                </div>
                <ol class="swm-priority-list">
                    @forelse ($priorities as $item)
                        @php $case = $item['case']; @endphp
                        <li>
                            <a class="swm-priority-item" href="{{ \App\Filament\Resources\PotholeCases\PotholeCaseResource::getUrl('view', ['record' => $case]) }}">
                                @if ($case->photoUrl())
                                    <img class="swm-priority-thumb" src="{{ $case->photoUrl() }}" alt="">
                                @else
                                    <span class="swm-priority-thumb swm-priority-thumb-empty" aria-hidden="true"></span>
                                @endif
                                <span class="swm-priority-copy">
                                    <span class="swm-priority-top">
                                        <strong>{{ $case->road_name }}</strong>
                                        <span class="swm-chip swm-chip-{{ $item['dominant_impact']->value }}">{{ $item['dominant_impact']->label() }}</span>
                                    </span>
                                    <span class="swm-priority-meta">
                                        <span class="swm-muted">{{ $case->area }} · {{ $case->ai_risk_score }}% risk</span>
                                        <span class="swm-stat-pill swm-stat-pill-budget">Est. {{ PotholeBudgetService::formatRm($item['estimated_cost_rm']) }}</span>
                                    </span>
                                    <p>{{ $item['reason'] }}</p>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="swm-empty">No open cases are awaiting priority scoring.</li>
                    @endforelse
                </ol>
            </section>

            <section class="swm-panel">
                <h2>Road-condition outlook</h2>
                <p class="swm-muted">Normal-week forecast based on rainfall, UV, temperature and traffic volume.</p>
                <div class="swm-risk-badge">
                    <span>Formation pressure</span>
                    <strong>{{ $forecast['formation_index'] }}%</strong>
                </div>
                <dl class="swm-forecast-stats">
                    <div><dt>Forecast new cases</dt><dd>{{ $forecast['predicted_new_count'] }}</dd></div>
                    <div><dt>Higher-severity share</dt><dd>{{ $forecast['severity_mix']['high'] }}</dd></div>
                </dl>
                <ul class="swm-corridor-list">
                    @foreach (array_slice($forecast['corridors'], 0, 3) as $corridor)
                        <li>
                            <div><strong>{{ $corridor['name'] }}</strong><span>{{ $corridor['pressure'] }}%</span></div>
                            <div class="swm-meter swm-meter-sm"><div class="swm-meter-fill" style="width: {{ $corridor['pressure'] }}%"></div></div>
                            <small>{{ $corridor['note'] }}</small>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</x-filament-widgets::widget>
