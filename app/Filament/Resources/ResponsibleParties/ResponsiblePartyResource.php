<?php

namespace App\Filament\Resources\ResponsibleParties;

use App\Filament\Resources\ResponsibleParties\Pages\CreateResponsibleParty;
use App\Filament\Resources\ResponsibleParties\Pages\EditResponsibleParty;
use App\Filament\Resources\ResponsibleParties\Pages\ListResponsibleParties;
use App\Filament\Resources\ResponsibleParties\Schemas\ResponsiblePartyForm;
use App\Filament\Resources\ResponsibleParties\Tables\ResponsiblePartiesTable;
use App\Models\ResponsibleParty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResponsiblePartyResource extends Resource
{
    protected static ?string $model = ResponsibleParty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|\UnitEnum|null $navigationGroup = 'Master data';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ResponsiblePartyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResponsiblePartiesTable::configure($table);
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
            'index' => ListResponsibleParties::route('/'),
            'create' => CreateResponsibleParty::route('/create'),
            'edit' => EditResponsibleParty::route('/{record}/edit'),
        ];
    }
}
