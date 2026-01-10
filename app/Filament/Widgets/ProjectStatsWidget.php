<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProjectStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProjects = Project::count();
        $completedProjects = Project::whereNotNull('end_date')->count();

        return [
            Stat::make('Total Proyectos', $totalProjects)
                ->description('Proyectos registrados')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),



            Stat::make('Completados', $completedProjects)
                ->description('Con fecha fin')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
