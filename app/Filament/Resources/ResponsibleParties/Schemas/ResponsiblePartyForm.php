<?php

namespace App\Filament\Resources\ResponsibleParties\Schemas;

use App\Enums\PartyType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ResponsiblePartyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('type')
                    ->options(collect(PartyType::cases())->mapWithKeys(
                        fn (PartyType $type) => [$type->value => $type->label()]
                    ))
                    ->required(),
                TextInput::make('phone')->tel()->maxLength(50),
                TextInput::make('email')->email()->maxLength(255),
                Select::make('contractor_id')
                    ->relationship('contractor', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }
}
