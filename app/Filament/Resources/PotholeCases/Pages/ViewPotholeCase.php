<?php

namespace App\Filament\Resources\PotholeCases\Pages;

use App\Filament\Pages\PotholesDashboard;
use App\Filament\Resources\PotholeCases\PotholeCaseResource;
use App\Services\PotholeBudgetService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewPotholeCase extends ViewRecord
{
    protected static string $resource = PotholeCaseResource::class;

    protected string $view = 'filament.resources.pothole-cases.pages.view-pothole-case';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboard')
                ->label('Potholes dashboard')
                ->color('gray')
                ->url(PotholesDashboard::getUrl()),
            EditAction::make(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $case = $this->getRecord()->loadMissing('contractor');

        return [
            'case' => $case,
            'budgetLabel' => PotholeBudgetService::formatRm((int) $case->estimated_cost_rm),
            'spentLabel' => PotholeBudgetService::formatRm((int) $case->budget_spent_rm),
        ];
    }
}
