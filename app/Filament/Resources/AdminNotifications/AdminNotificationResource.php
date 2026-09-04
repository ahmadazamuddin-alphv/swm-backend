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

class AdminNotificationResource extends Resource
{
    protected static ?string $model = AdminNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

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
