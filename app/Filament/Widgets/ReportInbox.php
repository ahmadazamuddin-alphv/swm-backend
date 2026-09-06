<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\AdminNotification;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class ReportInbox extends Widget
{
    protected string $view = 'filament.widgets.report-inbox';

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    #[On('operations-updated')]
    public function refreshInbox(): void {}

    public function markAllRead(): void
    {
        AdminNotification::query()->where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function openNotification(int $id): void
    {
        $notification = AdminNotification::query()->where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();
        if ($notification->report_id !== null) {
            $this->redirect(ReportResource::getUrl('view', ['record' => $notification->report_id]));
        }
    }

    protected function getViewData(): array
    {
        $query = AdminNotification::query()->where('user_id', auth()->id());

        return [
            'unread' => (clone $query)->whereNull('read_at')->count(),
            'notifications' => $query->with('report.wasteCategory', 'report.zone')->orderByRaw('read_at IS NOT NULL')
                ->latest('id')->limit(6)->get(),
        ];
    }
}
