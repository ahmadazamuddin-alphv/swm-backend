<x-filament-widgets::widget>
    <section class="swm-panel swm-inbox" wire:poll.15s aria-label="New-report notifications">
        <div class="swm-section-heading">
            <h2>Report inbox <span class="swm-count">{{ $unread }} unread</span></h2>
            @if ($unread > 0)
                <button type="button" wire:click="markAllRead" wire:loading.attr="disabled" class="swm-text-button">Mark all read</button>
            @endif
        </div>
        <p class="swm-muted">New submissions appear here. Open one to begin investigating.</p>
        <ul class="swm-inbox-list">
            @forelse ($notifications as $notice)
                <li wire:key="notice-{{ $notice->id }}">
                    <button type="button" wire:click="openNotification({{ $notice->id }})" wire:loading.attr="disabled" class="swm-inbox-link">
                        <span class="swm-inbox-title">
                            <strong>{{ $notice->report?->reference ?? $notice->title }}</strong>
                            @if (!$notice->read_at)<span class="swm-unread">New</span>@endif
                        </span>
                        <span>{{ $notice->report?->wasteCategory?->name ?? $notice->title }}</span>
                        <small>{{ $notice->report?->zone?->name ?? 'Area not identified' }} · {{ $notice->created_at->diffForHumans() }}</small>
                    </button>
                </li>
            @empty
                <li class="swm-empty">No reports yet. Use “Simulate new report” to demonstrate intake.</li>
            @endforelse
        </ul>
        <a class="swm-text-button" href="{{ \App\Filament\Resources\AdminNotifications\AdminNotificationResource::getUrl() }}">View all notifications</a>
    </section>
</x-filament-widgets::widget>
