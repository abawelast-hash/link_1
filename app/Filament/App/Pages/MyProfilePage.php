<?php
namespace App\Filament\App\Pages;
use Filament\Pages\Page;
class MyProfilePage extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = '???? ??????';
    protected static ?string $title = '????? ??????';
    protected static string $view = 'filament.app.pages.my-profile';
    protected static ?int $navigationSort = 6;
}
