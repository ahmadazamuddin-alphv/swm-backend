@php
    use App\Services\PotholeBudgetService;
@endphp

<x-filament-panels::page>
<div class="swm-potholes">
    {{-- Budget strip — always visible --}}
    <section class="swm-panel swm-budget" aria-label="Annual pothole budget">
        <div class="swm-budget-head">
            <div>
                <h2>Annual budget</h2>
                <p class="swm-muted">FY demonstration ceiling for Selangor road patching</p>
            </div>
            <strong>{{ PotholeBudgetService::formatRm($budget['annual_budget_rm']) }}</strong>
        </div>
        <div class="swm-meter" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $budget['utilization_pct'] }}" aria-label="Budget utilization">
            <div class="swm-meter-fill" style="width: {{ min(100, $budget['utilization_pct']) }}%"></div>
        </div>
        <dl class="swm-budget-stats">
            <div>
                <dt>Spent</dt>
                <dd>{{ PotholeBudgetService::formatRm($budget['spent_rm']) }}</dd>
            </div>
            <div>
                <dt>Remaining</dt>
                <dd>{{ PotholeBudgetService::formatRm($budget['remaining_rm']) }}</dd>
            </div>
            <div>
                <dt>Utilization</dt>
                <dd>{{ number_format($budget['utilization_pct'], 1) }}%</dd>
            </div>
        </dl>
    </section>

    {{-- Tabs --}}
    <div class="swm-tabs" role="tablist" aria-label="Potholes views">
        <button type="button" role="tab" class="{{ $this->activeTab === 'cases' ? 'is-active' : '' }}" wire:click="switchTab('cases')" aria-selected="{{ $this->activeTab === 'cases' ? 'true' : 'false' }}">Cases</button>
        <button type="button" role="tab" class="{{ $this->activeTab === 'forecast' ? 'is-active' : '' }}" wire:click="switchTab('forecast')" aria-selected="{{ $this->activeTab === 'forecast' ? 'true' : 'false' }}">Forecast</button>
        <a href="{{ $caseIndexUrl }}" class="swm-tabs-link">All cases</a>
    </div>

    @if ($this->activeTab === 'cases')
        <div class="swm-potholes-grid" role="tabpanel">
            <section class="swm-panel swm-map-panel">
                <div class="swm-section-heading">
                    <div>
                        <h2>Case map</h2>
                        <p class="swm-muted">Klang GDP, PJ rakyat, arterial balanced — OpenStreetMap base</p>
                    </div>
                    <span class="swm-count">{{ count($mapData['points']) }} cases</span>
                </div>

                @if (count($mapData['points']) > 0)
                    <div wire:key="pothole-map-{{ $this->activeTab }}-{{ md5(json_encode($mapData['points'])) }}">
                        <swm-operations-map wire:ignore class="swm-map" aria-label="Map of pothole cases">
                            <script type="application/json">@json($mapData)</script>
                            <div class="swm-map-canvas" data-map-canvas></div>
                            <p class="swm-map-status" data-map-status role="status">Loading map…</p>
                        </swm-operations-map>
                    </div>
                    <div class="swm-map-legend" aria-label="Map legend">
                        <span class="swm-tip" tabindex="0" data-tip="GDP-led: port, HGV and industrial corridors — spend protects state economic flow (e.g. Port Klang).">
                            <i class="swm-map-dot" style="background:#a84848"></i> GDP-led
                        </span>
                        <span class="swm-tip" tabindex="0" data-tip="Rakyat-led: dense residential / motorcycle lanes — spend lifts public satisfaction and rider safety (e.g. PJ, Subang).">
                            <i class="swm-map-dot" style="background:#e8c547"></i> Rakyat-led
                        </span>
                        <span class="swm-tip" tabindex="0" data-tip="Balanced: arterial junctions — safety-led cases that help both rakyat commute and GDP logistics.">
                            <i class="swm-map-dot" style="background:#6b9b6e"></i> Balanced
                        </span>
                    </div>
                    <p class="swm-map-note">Hover a legend label for what each impact type means. Click a marker to open impact. Base map © OpenStreetMap contributors.</p>
                @else
                    <p class="swm-empty">No pothole cases with coordinates are available to map.</p>
                @endif
            </section>

            <div class="swm-potholes-side">
                @if ($this->showImpactPanel && $selected)
                    <section class="swm-panel swm-impact-panel" wire:key="impact-{{ $selected->id }}">
                        <div class="swm-section-heading">
                            <button type="button" class="swm-text-button" wire:click="backToPriorities">← Priorities</button>
                            @if ($selectedUrl)
                                <a href="{{ $selectedUrl }}" class="swm-text-button">Open case</a>
                            @endif
                        </div>

                        <div class="swm-impact-header">
                            <div>
                                <h2>{{ $selected->reference }}</h2>
                                <p class="swm-muted">{{ $selected->locationLabel() }}</p>
                            </div>
                            <span
                                class="swm-chip swm-chip-{{ $selected->dominant_impact->value }} swm-tip"
                                tabindex="0"
                                data-tip="{{ $selected->dominant_impact->description() }}"
                            >{{ $selected->dominant_impact->label() }}</span>
                        </div>

                        @if ($selected->photoUrl())
                            <figure class="swm-impact-photo">
                                <img src="{{ $selected->photoUrl() }}" alt="Pothole evidence for {{ $selected->reference }}">
                            </figure>
                        @endif

                        <div class="swm-risk-badge">
                            <span>Overall AI risk</span>
                            <strong>{{ $selected->ai_risk_score }}%</strong>
                        </div>

                        <div class="swm-meters">
                            @foreach ([
                                ['Traffic impact', $selected->traffic_impact],
                                ['Safety risk', $selected->safety_risk],
                                ['Cost to fix', $selected->cost_to_fix],
                            ] as [$label, $value])
                                <div>
                                    <div class="swm-meter-label"><span>{{ $label }}</span><span>{{ $value }}</span></div>
                                    <div class="swm-meter swm-meter-sm"><div class="swm-meter-fill" style="width: {{ $value }}%"></div></div>
                                </div>
                            @endforeach
                        </div>

                        <div class="swm-impact-scores" aria-label="Rakyat {{ $selected->rakyat_impact }} versus GDP {{ $selected->gdp_impact }}">
                            <div class="swm-impact-score swm-impact-score-rakyat">
                                <span>Rakyat</span>
                                <strong>{{ $selected->rakyat_impact }}</strong>
                                <small>satisfaction</small>
                            </div>
                            <div class="swm-impact-score swm-impact-score-gdp">
                                <span>GDP</span>
                                <strong>{{ $selected->gdp_impact }}</strong>
                                <small>state impact</small>
                            </div>
                        </div>

                        <p class="swm-contractor-line"><span class="swm-muted">Contractor</span> <strong>{{ $selected->contractor?->name ?? 'Unassigned' }}</strong></p>
                        <p class="swm-impact-reason">{{ $selected->impact_reason }}</p>
                        <p class="swm-spend-line"><strong>Spend here first because…</strong> {{ str_replace('Spend here first because ', '', $selected->spend_recommendation ?? '') }}</p>
                    </section>
                @else
                    <section class="swm-panel" wire:key="priority-list">
                        <div class="swm-section-heading">
                            <h2>Where should I spend it?</h2>
                            <span class="swm-count">Top {{ $priorities->count() }}</span>
                        </div>
                        <p class="swm-panel-hint swm-muted">Tap a corridor to see impact in this panel — no scroll.</p>
                        <ol class="swm-priority-list">
                            @foreach ($priorities as $item)
                                @php $case = $item['case']; @endphp
                                <li>
                                    <button type="button" wire:click="selectCase({{ $case->id }})" class="swm-priority-item">
                                        @if ($case->photoUrl())
                                            <img class="swm-priority-thumb" src="{{ $case->photoUrl() }}" alt="" loading="lazy">
                                        @else
                                            <span class="swm-priority-thumb swm-priority-thumb-empty" aria-hidden="true"></span>
                                        @endif
                                        <span class="swm-priority-copy">
                                            <span class="swm-priority-top">
                                                <strong>{{ $case->road_name }}</strong>
                                                <span
                                                    class="swm-chip swm-chip-{{ $item['dominant_impact']->value }} swm-tip"
                                                    tabindex="0"
                                                    data-tip="{{ $item['dominant_impact']->description() }}"
                                                >{{ $item['dominant_impact']->label() }}</span>
                                            </span>
                                            <span class="swm-priority-meta">
                                                <span class="swm-muted">{{ $case->area }}</span>
                                                <span
                                                    class="swm-stat-pill swm-stat-pill-budget swm-tip"
                                                    tabindex="0"
                                                    data-tip="Estimated repair cost for this corridor."
                                                >Est. {{ PotholeBudgetService::formatRm($item['estimated_cost_rm']) }}</span>
                                                <span
                                                    class="swm-stat-pill swm-stat-pill-score swm-tip"
                                                    tabindex="0"
                                                    data-tip="Spend score = (street risk + rakyat + GDP) ÷ cost. Higher = better value for remaining budget — not the same as AI risk %."
                                                >Score {{ number_format($item['score'], 1) }}</span>
                                            </span>
                                            <small class="swm-contractor-meta">{{ $case->contractor?->name ?? 'Unassigned' }}</small>
                                            <p>{{ $item['reason'] }}</p>
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif
            </div>
        </div>
    @else
        <div class="swm-forecast-grid" role="tabpanel">
            <section class="swm-panel">
                <div class="swm-section-heading">
                    <h2>Formation forecast</h2>
                    <span class="swm-count">Hardcoded response curves</span>
                </div>

                <div class="swm-presets">
                    @foreach ($presets as $key => $preset)
                        <button type="button" class="{{ $this->activePreset === $key ? 'is-active' : '' }}" wire:click="applyPreset('{{ $key }}')">
                            {{ $preset['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="swm-sliders">
                    @foreach ([
                        ['rainfall', 'Rainfall', $this->rainfall],
                        ['uv', 'UV', $this->uv],
                        ['temperature', 'Temperature (°C scaled)', $this->temperature],
                        ['vehicle_volume', 'Vehicle / HGV volume', $this->vehicle_volume],
                    ] as [$field, $label, $value])
                        <label>
                            <span>{{ $label }} <strong>{{ $value }}</strong></span>
                            <input type="range" min="0" max="100" wire:model.live="{{ $field }}">
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="swm-panel swm-forecast-canvas">
                <div class="swm-section-heading">
                    <h2>Simulation output</h2>
                </div>
                <div class="swm-forecast-kpis">
                    <div>
                        <span>Predicted new potholes</span>
                        <strong>{{ $forecast['predicted_new_count'] }}</strong>
                    </div>
                    <div>
                        <span>Formation index</span>
                        <strong>{{ $forecast['formation_index'] }}/100</strong>
                    </div>
                </div>
                <div class="swm-severity-mix">
                    <h3>Severity mix</h3>
                    <div class="swm-severity-bars">
                        @foreach ($forecast['severity_mix'] as $level => $count)
                            <div>
                                <span>{{ ucfirst($level) }}</span>
                                <div class="swm-meter swm-meter-sm"><div class="swm-meter-fill swm-sev-{{ $level }}" style="width: {{ min(100, $count * 8) }}%"></div></div>
                                <strong>{{ $count }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="swm-corridor-pressure">
                    <h3>Corridors that worsen</h3>
                    @foreach ($forecast['corridors'] as $corridor)
                        <div class="swm-corridor-row">
                            <div class="swm-meter-label">
                                <span>{{ $corridor['name'] }}</span>
                                <span>{{ $corridor['pressure'] }}</span>
                            </div>
                            <div class="swm-meter swm-meter-sm"><div class="swm-meter-fill" style="width: {{ $corridor['pressure'] }}%"></div></div>
                            <small>{{ $corridor['note'] }}</small>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
</div>
</x-filament-panels::page>
