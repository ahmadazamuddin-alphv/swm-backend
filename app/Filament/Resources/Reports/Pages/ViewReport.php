<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Enums\ReportStatus;
use App\Filament\Resources\Reports\ReportResource;
use App\Models\AdminNotification;
use App\Models\ResponsibleParty;
use App\Services\ReportRecommendationService;
use App\Services\ReportWorkflow;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Storage;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        AdminNotification::query()->where('user_id', auth()->id())
            ->where('report_id', $this->record->id)->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function getSubheading(): ?string
    {
        return $this->record->wasteCategory?->name.' · '.($this->record->zone?->name ?? 'Area not identified');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('review')
                ->label('Start review')->icon('heroicon-o-magnifying-glass')
                ->visible(fn () => $this->record->status === ReportStatus::New)
                ->action(function (ReportWorkflow $workflow) {
                    $workflow->review($this->record, auth()->user());
                    $this->changed('Review started');
                }),
            Action::make('assign')
                ->label(fn () => $this->record->assignment?->status->value === 'active' ? 'Reassign team' : 'Assign team')
                ->icon('heroicon-o-user-group')
                ->visible(fn () => $this->record->isOpen())
                ->modalDescription('Confirm the team, lead driver and resources. Reassignment releases the previous team and records the change.')
                ->fillForm(function (ReportRecommendationService $recommendations) {
                    $suggestion = $recommendations->recommend($this->record);
                    $nearest = $recommendations->availableDrivers($this->record)->first();
                    $party = $nearest ? ResponsibleParty::where('contractor_id', $nearest['driver']->contractor_id)->first() : null;

                    return [
                        'responsible_party_id' => $party?->id ?? $this->record->zone?->responsible_party_id,
                        'driver_id' => $nearest['driver']->id ?? null,
                        'manpower' => $suggestion['suggested_manpower'],
                        'lorries' => $suggestion['suggested_lorries'],
                        'deadline' => $suggestion['suggested_deadline'],
                    ];
                })
                ->schema([
                    Select::make('responsible_party_id')->label('Assigned team')
                        ->options(fn () => ResponsibleParty::whereNotNull('contractor_id')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->required()->live()
                        ->afterStateUpdated(fn (Set $set) => $set('driver_id', null)),
                    Select::make('driver_id')->label('Lead driver / lorry')
                        ->options(fn (Get $get) => app(ReportRecommendationService::class)
                            ->availableDrivers($this->record, $get('responsible_party_id') ? (int) $get('responsible_party_id') : null)
                            ->mapWithKeys(fn ($option) => [$option['driver']->id => $option['driver']->name.' · '.$option['driver']->vehicle_plate
                                .($option['distance'] === null ? ' · depot unknown' : ' · '.$option['distance'].' km from site')]))
                        ->searchable()->required()
                        ->helperText('Available drivers accepting this waste, nearest depot first. Additional lorries are a demo crew estimate.'),
                    TextInput::make('manpower')->label('People')->integer()->minValue(1)->maxValue(100)->required(),
                    TextInput::make('lorries')->label('Lorries')->integer()->minValue(1)->maxValue(20)->required(),
                    DateTimePicker::make('deadline')->label('Intervention deadline')->seconds(false)->required()->after('now')
                        ->helperText('The suggestion starts at submission time. If overdue, enter an achievable new deadline explicitly.'),
                ])
                ->action(function (array $data, ReportWorkflow $workflow) {
                    $workflow->assign($this->record, auth()->user(), $data);
                    $this->changed('Team assigned');
                }),
            Action::make('start')->label('Start clearance')->icon('heroicon-o-truck')
                ->visible(fn () => $this->record->status === ReportStatus::Assigned)
                ->action(function (ReportWorkflow $workflow) {
                    $workflow->start($this->record, auth()->user());
                    $this->changed('Clearance started');
                }),
            Action::make('markSolved')->label('Record resolution')->icon('heroicon-o-check-circle')->color('success')
                ->visible(fn () => $this->record->isOpen())
                ->modalDescription('Attach a clear photo showing the completed clearance. Saving closes the case and completes its active assignment.')
                ->schema([
                    FileUpload::make('path')->label('Clearance photo')->image()->disk('public')
                        ->directory(fn () => 'reports/'.$this->record->id.'/proofs')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(8192)->required()
                        ->helperText('JPEG, PNG or WebP up to 8 MB. Stored with this local case.'),
                    Textarea::make('notes')->label('Clearance notes')->maxLength(2000),
                ])
                ->action(function (array $data, ReportWorkflow $workflow) {
                    try {
                        $workflow->solve($this->record, auth()->user(), $data['path'], $data['notes'] ?? null);
                    } catch (\Throwable $exception) {
                        if (str_starts_with($data['path'], "reports/{$this->record->id}/proofs/")) {
                            Storage::disk('public')->delete($data['path']);
                        }

                        throw $exception;
                    }
                    $this->changed('Resolution recorded');
                }),
            ActionGroup::make([
                Action::make('recommend')->label('Refresh demo recommendations')->icon('heroicon-o-arrow-path')
                    ->visible(fn () => $this->record->isOpen())
                    ->action(function (ReportWorkflow $workflow) {
                        $workflow->recommend($this->record, auth()->user());
                        $this->changed('Demo recommendations updated');
                    }),
                EditAction::make()->label('Edit investigation details')->visible(fn () => $this->record->isOpen()),
                Action::make('markFalse')->label('Mark false report')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn () => $this->record->isOpen())
                    ->modalDescription('Explain the investigation finding. This closes the report and cancels any active clearance assignment.')
                    ->schema([
                        Textarea::make('false_report_reason')->label('Investigation reason')->required()->minLength(5)->maxLength(2000),
                    ])
                    ->action(function (array $data, ReportWorkflow $workflow) {
                        $workflow->markFalse($this->record, auth()->user(), $data['false_report_reason']);
                        $this->changed('False report recorded');
                    }),
            ])->label('More actions')->button()->color('gray')->visible(fn () => $this->record->isOpen()),
        ];
    }

    private function changed(string $message): void
    {
        $this->record->refresh();
        $this->dispatch('operations-updated');
        Notification::make()->title($message)->success()->send();
    }
}
