<x-filament-panels::page>
    <div class="swm-pothole-detail">
        <div class="swm-pothole-hero">
            <section class="swm-panel swm-pothole-photo-card">
                @if ($case->photoUrl())
                    <img src="{{ $case->photoUrl() }}" alt="Pothole at {{ $case->locationLabel() }}">
                @else
                    <div class="swm-pothole-photo-empty">No photo</div>
                @endif
            </section>

            <section class="swm-panel swm-pothole-summary">
                <div class="swm-pothole-summary-top">
                    <div>
                        <p class="swm-muted swm-kicker">{{ $case->area }} · {{ $case->corridor_type->label() }}</p>
                        <h2>{{ $case->road_name }}</h2>
                    </div>
                    <div class="swm-pothole-badges">
                        <span class="swm-chip swm-chip-status">{{ $case->status->label() }}</span>
                        <span
                            class="swm-chip swm-chip-{{ $case->dominant_impact->value }} swm-tip"
                            tabindex="0"
                            data-tip="{{ $case->dominant_impact->description() }}"
                        >{{ $case->dominant_impact->label() }}</span>
                    </div>
                </div>

                <dl class="swm-pothole-facts">
                    <div>
                        <dt>Ref</dt>
                        <dd>{{ $case->reference }}</dd>
                    </div>
                    <div>
                        <dt>Contractor</dt>
                        <dd>{{ $case->contractor?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt>Est. cost</dt>
                        <dd>{{ $budgetLabel }}</dd>
                    </div>
                    <div>
                        <dt>Spent</dt>
                        <dd>{{ $spentLabel }}</dd>
                    </div>
                    <div>
                        <dt>Reported</dt>
                        <dd>{{ $case->reported_at?->format('j M Y') ?? '—' }}</dd>
                    </div>
                </dl>

                @if ($case->spend_recommendation)
                    <p class="swm-spend-line"><strong>Spend:</strong> {{ $case->spend_recommendation }}</p>
                @endif
            </section>
        </div>

        <div class="swm-pothole-analytics">
            <section class="swm-panel">
                <div class="swm-section-heading">
                    <h2>Risk meters</h2>
                    <span class="swm-count">AI {{ $case->ai_risk_score }}%</span>
                </div>

                <div class="swm-pothole-ai">
                    <div class="swm-meter-label"><span>AI risk</span><span>{{ $case->ai_risk_score }}</span></div>
                    <div class="swm-meter"><div class="swm-meter-fill" style="width: {{ $case->ai_risk_score }}%"></div></div>
                </div>

                <div class="swm-meters swm-meters-tight">
                    @foreach ([
                        ['Traffic', $case->traffic_impact],
                        ['Safety', $case->safety_risk],
                        ['Cost to fix', $case->cost_to_fix],
                        ['Street', $case->street_risk],
                    ] as [$label, $value])
                        <div>
                            <div class="swm-meter-label"><span>{{ $label }}</span><span>{{ $value }}</span></div>
                            <div class="swm-meter swm-meter-sm"><div class="swm-meter-fill" style="width: {{ $value }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="swm-panel">
                <div class="swm-section-heading">
                    <h2>Rakyat vs GDP</h2>
                    <span
                        class="swm-chip swm-chip-{{ $case->dominant_impact->value }} swm-tip"
                        tabindex="0"
                        data-tip="{{ $case->dominant_impact->description() }}"
                    >{{ $case->dominant_impact->label() }}-led</span>
                </div>

                <div class="swm-impact-scores" aria-label="Rakyat {{ $case->rakyat_impact }} versus GDP {{ $case->gdp_impact }}">
                    <div class="swm-impact-score swm-impact-score-rakyat">
                        <span>Rakyat</span>
                        <strong>{{ $case->rakyat_impact }}</strong>
                        <small>satisfaction</small>
                    </div>
                    <div class="swm-impact-score swm-impact-score-gdp">
                        <span>GDP</span>
                        <strong>{{ $case->gdp_impact }}</strong>
                        <small>state impact</small>
                    </div>
                </div>

                @if ($case->impact_reason)
                    <p class="swm-impact-reason">{{ $case->impact_reason }}</p>
                @endif
            </section>
        </div>
    </div>
</x-filament-panels::page>
