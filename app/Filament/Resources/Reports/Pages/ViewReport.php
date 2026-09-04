<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Enums\AssignmentStatus;
use App\Enums\ReportStatus;
use App\Filament\Resources\Reports\ReportResource;
use App\Models\Assignment;
use App\Models\ResolutionProof;
use App\Services\ReportRecommendationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recommend')
                ->label('Generate AI recommendations')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->action(function (ReportRecommendationService $service): void {
                    $service->applyToReport($this->record);
                    $this->refreshFormData([
                        'suggested_manpower',
                        'suggested_lorries',
                        'suggested_deadline',
                        'suggested_disposal_centre_id',
                    ]);
                    Notification::make()->title('Recommendations updated')->success()->send();
                }),
            Action::make('markFalse')
                ->label('Mark false report')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn (): bool => $this->record->status !== ReportStatus::FalseReport
                    && $this->record->status !== ReportStatus::Solved)
                ->form([
                    Textarea::make('false_report_reason')
                        ->label('Reason')
                        ->required()
                        ->minLength(5),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => ReportStatus::FalseReport,
                        'false_report_reason' => $data['false_report_reason'],
                        'resolved_at' => now(),
                    ]);
                    Notification::make()->title('Marked as false report')->success()->send();
                }),
            Action::make('markSolved')
                ->label('Mark solved')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (): bool => $this->record->status !== ReportStatus::Solved
                    && $this->record->status !== ReportStatus::FalseReport)
                ->form([
                    FileUpload::make('path')
                        ->label('Proof of resolution')
                        ->image()
                        ->required()
                        ->directory('reports/proofs'),
                    Textarea::make('notes')->label('Notes'),
                ])
                ->action(function (array $data): void {
                    ResolutionProof::create([
                        'report_id' => $this->record->id,
                        'path' => $data['path'],
                        'notes' => $data['notes'] ?? null,
                        'uploaded_by' => auth()->id(),
                    ]);

                    $this->record->update([
                        'status' => ReportStatus::Solved,
                        'resolved_at' => now(),
                    ]);

                    Notification::make()->title('Report marked solved')->success()->send();
                }),
            Action::make('assign')
                ->label('Assign')
                ->icon('heroicon-o-user-plus')
                ->visible(fn (): bool => ! in_array($this->record->status, [
                    ReportStatus::Solved,
                    ReportStatus::FalseReport,
                ], true))
                ->fillForm(fn (): array => [
                    'responsible_party_id' => $this->record->zone?->responsible_party_id,
                    'manpower' => $this->record->suggested_manpower,
                    'lorries' => $this->record->suggested_lorries,
                    'deadline' => $this->record->suggested_deadline,
                    'status' => AssignmentStatus::Active->value,
                ])
                ->form([
                    Select::make('responsible_party_id')
                        ->label('Responsible party')
                        ->options(fn () => \App\Models\ResponsibleParty::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('driver_id')
                        ->label('Driver (abang lori)')
                        ->options(fn () => \App\Models\Driver::query()->pluck('name', 'id'))
                        ->searchable(),
                    TextInput::make('manpower')->numeric()->minValue(1)->required(),
                    TextInput::make('lorries')->numeric()->minValue(1)->required(),
                    DateTimePicker::make('deadline')->required(),
                ])
                ->action(function (array $data): void {
                    Assignment::create([
                        'report_id' => $this->record->id,
                        'responsible_party_id' => $data['responsible_party_id'],
                        'driver_id' => $data['driver_id'] ?? null,
                        'manpower' => $data['manpower'],
                        'lorries' => $data['lorries'],
                        'deadline' => $data['deadline'],
                        'status' => AssignmentStatus::Active,
                    ]);

                    $this->record->update([
                        'status' => ReportStatus::Assigned,
                    ]);

                    Notification::make()->title('Assignment created')->success()->send();
                }),
            EditAction::make(),
        ];
    }
}
