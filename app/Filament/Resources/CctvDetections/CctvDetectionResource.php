<?php

namespace App\Filament\Resources\CctvDetections;

use App\Filament\Resources\CctvDetections\Pages\CreateCctvDetection;
use App\Filament\Resources\CctvDetections\Pages\EditCctvDetection;
use App\Filament\Resources\CctvDetections\Pages\ListCctvDetections;
use App\Filament\Resources\CctvDetections\Pages\ViewCctvDetection;
use App\Filament\Resources\CctvDetections\Schemas\CctvDetectionForm;
use App\Filament\Resources\CctvDetections\Schemas\CctvDetectionInfolist;
use App\Filament\Resources\CctvDetections\Tables\CctvDetectionsTable;
use App\Models\CctvDetection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CctvDetectionResource extends Resource
{
    protected static ?string $model = CctvDetection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationParentItem = 'Illegal dumping';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'CCTV Detections';

    protected static ?string $modelLabel = 'CCTV detection';

    public static function form(Schema $schema): Schema
    {
        return CctvDetectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CctvDetectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CctvDetectionsTable::configure($table);
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
            'index' => ListCctvDetections::route('/'),
            'create' => CreateCctvDetection::route('/create'),
            'view' => ViewCctvDetection::route('/{record}'),
            'edit' => EditCctvDetection::route('/{record}/edit'),
        ];
    }
}
