<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class RevenueStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Today', '$' . number_format(Order::whereDate('order_date', today())->sum('total_amount'), 2))
                ->description('Total revenue for today')
                ->descriptionIcon('heroicon-s-arrow-trending-up')
                ->color('success'),

            Card::make('This Month', '$' . number_format(Order::whereMonth('order_date', today()->format('m'))->sum('total_amount'), 2))
                ->description('Revenue for current month')
                ->descriptionIcon('heroicon-s-calendar')
                ->color('primary'),

            Card::make('Total Revenue', '$' . number_format(Order::sum('total_amount'), 2))
                ->description('All-time total sales')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('gray'),
        ];
    }
}
