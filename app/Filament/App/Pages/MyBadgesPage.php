<?php
namespace App\Filament\App\Pages;
use Filament\Pages\Page;
class MyBadgesPage extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = '??????';
    protected static ?string $title = '??????? ??????????';
    protected static string $view = 'filament.app.pages.my-badges';
    protected static ?int $navigationSort = 3;
    public function getBadgesProperty() {
        return auth()->user()->userBadges()->with('badge')->latest()->get();
    }
}
