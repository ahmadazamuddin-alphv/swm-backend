<?php

namespace App\Filament\Resources\AdminNotifications\Tables;

use App\Filament\Resources\Reports\ReportResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('report.reference')->label('Report'),
                IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->read_at !== null),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('markRead')
                    ->label('Mark read')
                    ->icon('heroicon-o-check')
                    ->visible(fn ($record) => $record->read_at === null)
                    ->action(function ($record): void {
                        $record->markAsRead();
                        Notification::make()->title('Notification marked as read')->success()->send();
                    }),
                Action::make('investigate')
                    ->label('Investigate')
                    ->icon('heroicon-o-magnifying-glass')
                    ->url(fn ($record) => ReportResource::getUrl('view', ['record' => $record->report_id])),
            ])
            ->emptyStateHeading('No notifications yet')
            ->emptyStateDescription('New simulated and citizen reports will appear here.');
    }
}
