<?php

namespace App\Filament\Widgets;

use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TotalStore extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Store', Store::count())
                ->description('Total Number of Registered Stores')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('success'),
        ];
    }
}
