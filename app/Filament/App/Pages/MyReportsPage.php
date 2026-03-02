<?php
namespace App\Filament\App\Pages;
use Filament\Pages\Page;
use App\Models\MonthlyReport;
class MyReportsPage extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = '??????? ???????';
    protected static ?string $title = '???????? ???????';
    protected static string $view = 'filament.app.pages.my-reports';
    protected static ?int $navigationSort = 4;
    public function getReportsProperty() {
        return MonthlyReport::where('user_id', auth()->id())->orderBy('year', 'desc')->orderBy('month', 'desc')->get();
    }
}
