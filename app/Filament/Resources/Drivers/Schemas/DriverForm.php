<?php

namespace App\Filament\Resources\Drivers\Schemas;

use App\Models\WasteCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                TextInput::make('base_latitude')->label('Depot latitude')->numeric()->minValue(-90)->maxValue(90),
                TextInput::make('base_longitude')->label('Depot longitude')->numeric()->minValue(-180)->maxValue(180),
                Toggle::make('is_available')->label('Available for assignment')->default(true),
                Select::make('accepted_category_ids')->label('Suitable waste categories')->multiple()
                    ->options(fn () => WasteCategory::pluck('name', 'id'))->required()
                    ->helperText('A driver is suggested only for a listed waste category and when not active on another case.'),
            ]);
    }
}
