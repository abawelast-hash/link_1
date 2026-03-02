<?php
namespace App\Filament\App\Pages;
use Filament\Pages\Page;
use App\Models\Message;
class InboxPage extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = '???????';
    protected static ?string $title = '????? ??????';
    protected static string $view = 'filament.app.pages.inbox';
    protected static ?int $navigationSort = 7;
    public function getMessagesProperty() {
        return Message::where('receiver_id', auth()->id())->with('sender')->latest()->get();
    }
}
