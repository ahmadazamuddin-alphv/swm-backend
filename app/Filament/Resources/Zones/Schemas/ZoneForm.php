<?php

namespace App\Filament\Resources\Zones\Schemas;

use App\Enums\AreaType;
use App\Enums\SocioeconomicGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('postcode')->maxLength(20),
                TextInput::make('taman')->maxLength(255),
                Select::make('area_type')
                    ->options(collect(AreaType::cases())->mapWithKeys(
                        fn (AreaType $type) => [$type->value => $type->label()]
                    )),
                Select::make('socioeconomic_group')
                    ->options(collect(SocioeconomicGroup::cases())->mapWithKeys(
                        fn (SocioeconomicGroup $group) => [$group->value => $group->label()]
                    )),
                Select::make('responsible_party_id')
                    ->relationship('responsibleParty', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
