<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget\Card;

class OrderStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('All Orders', Order::count()),
            Card::make('Pending Orders', Order::where('status', 'pending')->count()),
            Card::make('Completed Orders', Order::where('status', 'completed')->count()),
        ];
    }
}
