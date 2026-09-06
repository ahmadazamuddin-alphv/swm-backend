<x-filament-panels::page>
    <div class="swm-analytics" wire:key="analytics-{{ $this->analyticsType() }}-{{ $this->service }}">
        <section class="swm-panel swm-analytics-filter" aria-label="Analytics filters">
            <div>
                <p class="swm-eyebrow">{{ $serviceLabel }} analytics</p>
                <h2>{{ $title }}</h2>
                <p class="swm-muted">Filters update the chart, heatmap and CSV export together.</p>
            </div>
            <div class="swm-analytics-controls">
                <label>
                    <span>District / area</span>
                    <select wire:model.live="area">
                        <option value="all">All areas</option>
                        @foreach ($this->areas() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Status</span>
                    <select wire:model.live="status">
                        <option value="all">All statuses</option>
                        @foreach ($this->statuses() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </section>

        @if ($this->analyticsType() === 'priority')
            <section class="swm-panel swm-analytics-chart" aria-label="{{ $serviceLabel }} district priority chart">
                <div class="swm-section-heading">
                    <div>
                        <h2>Cases by district and {{ $this->service === 'dumping' ? 'risk' : 'severity' }}</h2>
                        <p class="swm-muted">Sorted by total filtered cases. Use the CSV export for the briefing appendix.</p>
                    </div>
                    <span class="swm-count">{{ array_sum(array_column($analytics['rows'], 'total')) }} cases</span>
                </div>
                <div class="swm-analytics-legend">
                    @foreach ($analytics['keys'] as $key)
                        <span class="swm-analytics-key swm-analytics-key-{{ strtolower($key) }}"><i></i>{{ $key }}</span>
                    @endforeach
                </div>
                <div class="swm-priority-bars">
                    @forelse ($analytics['rows'] as $row)
                        <div class="swm-priority-bar-row">
                            <strong>{{ $row['label'] }}</strong>
                            <div class="swm-priority-track" aria-label="{{ $row['label'] }}: {{ $row['total'] }} cases">
                                @foreach ($analytics['keys'] as $key)
                                    @if ($row[$key] > 0)
                                        <span class="swm-priority-segment swm-analytics-key-{{ strtolower($key) }}" style="width: {{ ($row[$key] / $analytics['max']) * 100 }}%" title="{{ $key }}: {{ $row[$key] }}"></span>
                                    @endif
                                @endforeach
                            </div>
                            <span>{{ $row['total'] }}</span>
                        </div>
                    @empty
                        <p class="swm-empty">No records match the selected filters.</p>
                    @endforelse
                </div>
            </section>
        @elseif ($this->analyticsType() === 'performance')
            @php $max = max(1, collect($analytics['weeks'])->max(fn ($week) => max($week['received'], $week['resolved']))); @endphp
            <section class="swm-panel swm-analytics-chart" aria-label="{{ $serviceLabel }} response performance chart">
                <div class="swm-section-heading">
                    <div>
                        <h2>Received and resolved cases</h2>
                        <p class="swm-muted">Six-week view. Closure-time labels show the median completed-case duration.</p>
                    </div>
                </div>
                <div class="swm-performance-bars">
                    @foreach ($analytics['weeks'] as $week)
                        <div class="swm-performance-week">
                            <div class="swm-performance-columns" aria-label="{{ $week['label'] }}: {{ $week['received'] }} received, {{ $week['resolved'] }} resolved">
                                <span
                                    class="is-received swm-tip"
                                    tabindex="0"
                                    style="height: {{ max(5, ($week['received'] / $max) * 100) }}%"
                                    data-tip="{{ $week['label'] }} · Received: {{ $week['received'] }} · Resolved: {{ $week['resolved'] }} · Median closure: {{ $week['median'] === null ? 'No completed cases' : $week['median'].' hours' }}"
                                ></span>
                                <span
                                    class="is-resolved swm-tip"
                                    tabindex="0"
                                    style="height: {{ max(5, ($week['resolved'] / $max) * 100) }}%"
                                    data-tip="{{ $week['label'] }} · Resolved: {{ $week['resolved'] }} · Received: {{ $week['received'] }} · Median closure: {{ $week['median'] === null ? 'No completed cases' : $week['median'].' hours' }}"
                                ></span>
                            </div>
                            <strong>{{ $week['label'] }}</strong>
                            <small>{{ $week['median'] === null ? '—' : $week['median'].'h median' }}</small>
                        </div>
                    @endforeach
                </div>
                <div class="swm-analytics-legend">
                    <span class="swm-analytics-key is-received"><i></i>Received</span>
                    <span class="swm-analytics-key is-resolved"><i></i>Resolved</span>
                </div>
            </section>
        @else
            <section class="swm-panel swm-analytics-chart" aria-label="{{ $serviceLabel }} hotspot heatmap">
                <div class="swm-section-heading">
                    <div>
                        <h2>Hotspot movement</h2>
                        <p class="swm-muted">Higher-risk records create larger, warmer halos. Select a point for its reference and area.</p>
                    </div>
                    <span class="swm-count">{{ $analytics['count'] }} mapped cases</span>
                </div>
                @if ($analytics['count'])
                    <swm-analytics-heatmap wire:ignore class="swm-analytics-map">
                        <script type="application/json">@json($analytics['points'])</script>
                        <div data-analytics-map-canvas></div>
                    </swm-analytics-heatmap>
                    <div class="swm-analytics-legend">
                        <span class="swm-analytics-key swm-analytics-key-standard"><i></i>Lower risk</span>
                        <span class="swm-analytics-key swm-analytics-key-elevated"><i></i>Elevated</span>
                        <span class="swm-analytics-key swm-analytics-key-high"><i></i>High risk</span>
                    </div>
                @else
                    <p class="swm-empty">No coordinates match the selected filters.</p>
                @endif
            </section>
        @endif
    </div>
</x-filament-panels::page>
