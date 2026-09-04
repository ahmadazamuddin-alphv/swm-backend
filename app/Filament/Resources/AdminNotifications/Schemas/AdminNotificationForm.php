<?php

namespace App\Filament\Resources\AdminNotifications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdminNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('report_id')
                    ->relationship('report', 'reference')
                    ->searchable()
                    ->preload(),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('body')->columnSpanFull(),
            ]);
    }
}
