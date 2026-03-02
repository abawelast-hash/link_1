<?php
namespace App\Filament\App\Pages;
use Filament\Pages\Page;
use App\Models\{Circular, CircularRead};
class CircularsPage extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = '????????';
    protected static ?string $title = '???????? ??????????';
    protected static string $view = 'filament.app.pages.circulars';
    protected static ?int $navigationSort = 8;
    public function getCircularsProperty() {
        $user = auth()->user();
        return Circular::published()
            ->where(fn($q) => $q->whereNull('target_branches')->orWhereJsonContains('target_branches', $user->branch_id))
            ->where(fn($q) => $q->whereNull('target_levels')->orWhereJsonContains('target_levels', $user->security_level))
            ->latest('published_at')->get();
    }
    public function markRead(int $circularId): void {
        CircularRead::firstOrCreate(['circular_id'=>$circularId, 'user_id'=>auth()->id()], ['read_at'=>now()]);
    }
}
