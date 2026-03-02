<?php
namespace App\Filament\App\Widgets;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PointsTransaction;
class MyPointsWidget extends BaseWidget {
    protected static ?int $sort = 2;
    protected function getStats(): array {
        $user = auth()->user();
        $monthPoints = PointsTransaction::where('user_id', $user->id)
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)
            ->sum('points');
        $badgesCount = $user->userBadges()->count();
        return [
            Stat::make('?????? ?????', number_format($user->total_points))
                ->description('?? ????? ?????')->descriptionIcon('heroicon-m-trophy')->color('warning'),
            Stat::make('???? ??? ?????', ($monthPoints >= 0 ? '+' : '') . $monthPoints)
                ->color($monthPoints >= 0 ? 'success' : 'danger'),
            Stat::make('??????? ????????', $badgesCount)
                ->descriptionIcon('heroicon-m-star')->color('info'),
        ];
    }
}
