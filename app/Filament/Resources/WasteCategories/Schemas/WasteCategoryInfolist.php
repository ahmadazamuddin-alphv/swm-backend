<?php

namespace App\Filament\Resources\WasteCategories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WasteCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('description')->placeholder('—'),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
