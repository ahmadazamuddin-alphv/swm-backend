<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('contractor_id')
                    ->relationship('contractor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(50),
                TextInput::make('vehicle_plate')->maxLength(50),
            ]);
    }
}
