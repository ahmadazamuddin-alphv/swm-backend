<?php

namespace App\Filament\Resources\PotholeCases;

use App\Filament\Resources\PotholeCases\Pages\CreatePotholeCase;
use App\Filament\Resources\PotholeCases\Pages\EditPotholeCase;
use App\Filament\Resources\PotholeCases\Pages\ListPotholeCases;
use App\Filament\Resources\PotholeCases\Pages\ViewPotholeCase;
use App\Filament\Resources\PotholeCases\Schemas\PotholeCaseForm;
use App\Filament\Resources\PotholeCases\Schemas\PotholeCaseInfolist;
use App\Filament\Resources\PotholeCases\Tables\PotholeCasesTable;
use App\Models\PotholeCase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PotholeCaseResource extends Resource
{
    protected static ?string $model = PotholeCase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Cases';

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationParentItem = 'Potholes';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'pothole case';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return PotholeCaseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PotholeCaseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PotholeCasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPotholeCases::route('/'),
            'create' => CreatePotholeCase::route('/create'),
            'view' => ViewPotholeCase::route('/{record}'),
            'edit' => EditPotholeCase::route('/{record}/edit'),
        ];
    }
}
