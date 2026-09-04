<?php

namespace App\Filament\Resources\DisposalCentres;

use App\Filament\Resources\DisposalCentres\Pages\CreateDisposalCentre;
use App\Filament\Resources\DisposalCentres\Pages\EditDisposalCentre;
use App\Filament\Resources\DisposalCentres\Pages\ListDisposalCentres;
use App\Filament\Resources\DisposalCentres\Schemas\DisposalCentreForm;
use App\Filament\Resources\DisposalCentres\Tables\DisposalCentresTable;
use App\Models\DisposalCentre;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DisposalCentreResource extends Resource
{
    protected static ?string $model = DisposalCentre::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Master data';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DisposalCentreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DisposalCentresTable::configure($table);
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
            'index' => ListDisposalCentres::route('/'),
            'create' => CreateDisposalCentre::route('/create'),
            'edit' => EditDisposalCentre::route('/{record}/edit'),
        ];
    }
}
