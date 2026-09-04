<?php

namespace App\Filament\Resources\DisposalCentres\Schemas;

use App\Models\WasteCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DisposalCentreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('latitude')->numeric()->required(),
                TextInput::make('longitude')->numeric()->required(),
                TextInput::make('address')->maxLength(255)->columnSpanFull(),
                Select::make('accepted_category_ids')
                    ->label('Accepted categories')
                    ->multiple()
                    ->options(fn () => WasteCategory::query()->pluck('name', 'id'))
                    ->columnSpanFull(),
            ]);
    }
}
