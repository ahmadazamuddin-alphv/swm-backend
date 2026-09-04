<?php

namespace App\Filament\Resources\WasteCategories;

use App\Filament\Resources\WasteCategories\Pages\CreateWasteCategory;
use App\Filament\Resources\WasteCategories\Pages\EditWasteCategory;
use App\Filament\Resources\WasteCategories\Pages\ListWasteCategories;
use App\Filament\Resources\WasteCategories\Pages\ViewWasteCategory;
use App\Filament\Resources\WasteCategories\Schemas\WasteCategoryForm;
use App\Filament\Resources\WasteCategories\Schemas\WasteCategoryInfolist;
use App\Filament\Resources\WasteCategories\Tables\WasteCategoriesTable;
use App\Models\WasteCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WasteCategoryResource extends Resource
{
    protected static ?string $model = WasteCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Master data';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WasteCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WasteCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WasteCategoriesTable::configure($table);
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
            'index' => ListWasteCategories::route('/'),
            'create' => CreateWasteCategory::route('/create'),
            'view' => ViewWasteCategory::route('/{record}'),
            'edit' => EditWasteCategory::route('/{record}/edit'),
        ];
    }
}
