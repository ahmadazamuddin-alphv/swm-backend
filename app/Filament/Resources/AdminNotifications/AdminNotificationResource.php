<?php

namespace App\Filament\Resources\AdminNotifications;

use App\Filament\Resources\AdminNotifications\Pages\CreateAdminNotification;
use App\Filament\Resources\AdminNotifications\Pages\EditAdminNotification;
use App\Filament\Resources\AdminNotifications\Pages\ListAdminNotifications;
use App\Filament\Resources\AdminNotifications\Schemas\AdminNotificationForm;
use App\Filament\Resources\AdminNotifications\Tables\AdminNotificationsTable;
use App\Models\AdminNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminNotificationResource extends Resource
{
    protected static ?string $model = AdminNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = -9;

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->whereNull('read_at')->count();

        return $count ? (string) $count : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AdminNotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminNotificationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminNotifications::route('/'),
            'create' => CreateAdminNotification::route('/create'),
            'edit' => EditAdminNotification::route('/{record}/edit'),
        ];
    }
}
