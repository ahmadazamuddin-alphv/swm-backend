<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.reports.investigation')->columnSpanFull(),
        ]);
    }
}
