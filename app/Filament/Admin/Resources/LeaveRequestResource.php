<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model           = LeaveRequest::class;
    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'الموارد البشرية';
    protected static ?string $label           = 'طلب إجازة';
    protected static ?string $pluralLabel     = 'طلبات الإجازة';
    protected static ?int    $navigationSort  = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Select::make('user_id')->label('الموظف')->relationship('user', 'name')->searchable()->preload()->required(),
            F\Select::make('type')->label('نوع الإجازة')->options([
                'annual'    => 'سنوية',
                'sick'      => 'مرضية',
                'emergency' => 'طارئة',
                'unpaid'    => 'بدون راتب',
                'other'     => 'أخرى',
            ])->required(),
            F\DatePicker::make('start_date')->label('من تاريخ')->required(),
            F\DatePicker::make('end_date')->label('إلى تاريخ')->required()->afterOrEqual('start_date'),
            F\Textarea::make('reason')->label('السبب')->required()->columnSpanFull(),
            F\Select::make('status')->label('الحالة')->options([
                'pending'  => '⏳ قيد المراجعة',
                'approved' => '✅ مقبول',
                'rejected' => '❌ مرفوض',
            ])->required()->default('pending'),
            F\Textarea::make('review_notes')->label('ملاحظات المراجع')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('user.name')->label('الموظف')->searchable()->sortable(),
                C\TextColumn::make('type')->label('النوع')
                    ->formatStateUsing(fn($state) => match($state) {
                        'annual' => 'سنوية', 'sick' => 'مرضية',
                        'emergency' => 'طارئة', 'unpaid' => 'بدون راتب',
                        default => 'أخرى',
                    }),
                C\TextColumn::make('start_date')->label('من')->date()->sortable(),
                C\TextColumn::make('end_date')->label('إلى')->date()->sortable(),
                C\TextColumn::make('days_count')->label('الأيام'),
                C\BadgeColumn::make('status')->label('الحالة')
                    ->color(fn($state) => match($state) {
                        'approved' => 'success', 'rejected' => 'danger',
                        default    => 'warning',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending'  => '⏳ قيد المراجعة',
                        'approved' => '✅ مقبول',
                        'rejected' => '❌ مرفوض',
                        default    => $state,
                    }),
                C\TextColumn::make('created_at')->label('تاريخ الطلب')->date()->sortable(),
            ])
            ->filters([
                Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'pending' => 'قيد المراجعة', 'approved' => 'مقبول', 'rejected' => 'مرفوض',
                ]),
                Filters\SelectFilter::make('branch_id')->label('الفرع')->relationship('branch', 'name'),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('قبول')->icon('heroicon-o-check')->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(LeaveRequest $r) => $r->status === 'pending')
                    ->action(fn(LeaveRequest $r) => $r->update([
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
                Actions\Action::make('reject')
                    ->label('رفض')->icon('heroicon-o-x-mark')->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(LeaveRequest $r) => $r->status === 'pending')
                    ->action(fn(LeaveRequest $r) => $r->update([
                        'status' => 'rejected',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
                Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        if (! $user->isGodMode() && $user->security_level < 7) {
            $query->where('branch_id', $user->branch_id);
        }
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit'   => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
