<?php
namespace App\Filament\App\Pages;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\WhistleblowerReport;
class WhistleblowerPage extends Page implements Forms\Contracts\HasForms {
    use Forms\Concerns\InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationLabel = '????? ????';
    protected static ?string $title = '????? ???? ???';
    protected static string $view = 'filament.app.pages.whistleblower';
    protected static ?int $navigationSort = 9;
    public ?array $data = [];
    public ?string $submittedToken = null;
    public function mount(): void { $this->form->fill(); }
    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('subject')->label('???????')->required(),
            Forms\Components\Select::make('category')->label('???????')->options(['financial'=>'????','administrative'=>'?????','harassment'=>'????','corruption'=>'????','other'=>'????'])->required(),
            Forms\Components\Select::make('severity')->label('???????')->options(['low'=>'?????','medium'=>'?????','high'=>'????','critical'=>'???'])->required(),
            Forms\Components\Toggle::make('is_anonymous')->label('????? ????? ??????')->default(false),
            Forms\Components\Textarea::make('body')->label('?????? ??????')->rows(5)->required()->columnSpanFull(),
            Forms\Components\FileUpload::make('attachment')->label('???? (???????)')->directory('whistleblower-attachments'),
        ])->statePath('data')->columns(2);
    }
    public function submit(): void {
        $formData = $this->form->getState();
        $userId = $formData['is_anonymous'] ? null : auth()->id();
        $report = WhistleblowerReport::create(array_merge($formData, ['user_id'=>$userId]));
        $this->submittedToken = $report->token;
        Notification::make()->title(' ?? ????? ??????')->body("???? ??? ????????: {$report->token}")->success()->persistent()->send();
        $this->form->fill();
    }
}
