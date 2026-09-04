<?php

namespace App\Filament\Resources\Contractors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContractorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('contact_person')->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(50),
                TextInput::make('email')->email()->maxLength(255),
            ]);
    }
}
