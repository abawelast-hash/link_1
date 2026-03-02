<?php
namespace App\Filament\App\Pages;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\LeaveRequest;
class LeaveRequestPage extends Page implements Forms\Contracts\HasForms {
    use Forms\Concerns\InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = '??? ?????';
    protected static ?string $title = '??? ?????';
    protected static string $view = 'filament.app.pages.leave-request';
    protected static ?int $navigationSort = 5;
    public ?array $data = [];
    public function mount(): void { $this->form->fill(); }
    public function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('type')->label('??? ???????')->options(['annual'=>'?????','sick'=>'?????','emergency'=>'?????','unpaid'=>'???? ????','other'=>'????'])->required(),
            Forms\Components\DatePicker::make('start_date')->label('?? ?????')->required(),
            Forms\Components\DatePicker::make('end_date')->label('??? ?????')->required(),
            Forms\Components\Textarea::make('reason')->label('?????')->required()->columnSpanFull(),
            Forms\Components\FileUpload::make('attachment')->label('???? (???????)')->directory('leave-attachments'),
        ])->statePath('data')->columns(2);
    }
    public function submit(): void {
        $user = auth()->user();
        LeaveRequest::create(array_merge($this->form->getState(), ['user_id'=>$user->id, 'branch_id'=>$user->branch_id, 'status'=>'pending']));
        Notification::make()->title(' ?? ????? ??? ??????? ?????')->success()->send();
        $this->form->fill();
    }
}
