<?php

namespace App\Filament\Store\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TotalProduct extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Product', Product::where('store_id', auth('store')->id())->count())
                ->description('Product is Available in Store')
                ->color('success'),
        ];
    }
}
