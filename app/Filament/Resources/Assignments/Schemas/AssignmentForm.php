<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Enums\AssignmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('report_id')
                    ->relationship('report', 'reference')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('responsible_party_id')
                    ->relationship('responsibleParty', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('driver_id')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('deadline'),
                TextInput::make('manpower')->numeric()->minValue(1),
                TextInput::make('lorries')->numeric()->minValue(1),
                Select::make('status')
                    ->options(collect(AssignmentStatus::cases())->mapWithKeys(
                        fn (AssignmentStatus $status) => [$status->value => $status->label()]
                    ))
                    ->required()
                    ->default(AssignmentStatus::Pending->value),
            ]);
    }
}
