<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AttendanceLogResource\Pages;
use App\Models\AttendanceLog;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};
use Illuminate\Database\Eloquent\Builder;

class AttendanceLogResource extends Resource
{
    protected static ?string $model           = AttendanceLog::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'الموارد البشرية';
    protected static ?string $label           = 'سجل حضور';
    protected static ?string $pluralLabel     = 'سجلات الحضور';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Section::make('بيانات الحضور')->schema([
                F\Select::make('user_id')->label('الموظف')
                    ->relationship('user', 'name')->searchable()->preload()->required(),
                F\Select::make('branch_id')->label('الفرع')
                    ->relationship('branch', 'name')->searchable()->preload()->required(),
                F\DatePicker::make('attendance_date')->label('التاريخ')->required(),
                F\Select::make('status')->label('الحالة')
                    ->options([
                        'on_time' => '✅ في الموعد',
                        'late'    => '⚠️ متأخر',
                        'absent'  => '❌ غائب',
                        'excused' => '📋 معذور',
                    ])->required(),
            ])->columns(2),
            F\Section::make('التوقيت والتفاصيل')->schema([
                F\DateTimePicker::make('check_in_at')->label('وقت الدخول'),
                F\DateTimePicker::make('check_out_at')->label('وقت الخروج'),
                F\TextInput::make('delay_minutes')->label('التأخير (دقيقة)')->numeric()->default(0),
                F\TextInput::make('financial_deduction')->label('الخصم المالي (ريال)')->numeric()->default(0),
                F\TextInput::make('overtime_hours')->label('ساعات إضافية')->numeric()->default(0),
                F\Toggle::make('is_manual')->label('تسجيل يدوي')->default(false),
                F\Textarea::make('notes')->label('ملاحظات')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('user.name')->label('الموظف')->searchable()->sortable(),
                C\TextColumn::make('branch.name')->label('الفرع')->sortable(),
                C\TextColumn::make('attendance_date')->label('التاريخ')->date('Y-m-d')->sortable(),
                C\BadgeColumn::make('status')->label('الحالة')
                    ->color(fn($state) => match($state) {
                        'on_time' => 'success', 'late' => 'warning',
                        'absent'  => 'danger',  'excused' => 'info',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'on_time' => '✅ في الموعد', 'late' => '⚠️ متأخر',
                        'absent'  => '❌ غائب',      'excused' => '📋 معذور',
                        default   => $state,
                    }),
                C\TextColumn::make('delay_minutes')->label('التأخير (د)')->sortable(),
                C\TextColumn::make('financial_deduction')->label('الخصم (ريال)')->money('SAR')->sortable(),
                C\TextColumn::make('check_in_at')->label('الدخول')->time('H:i'),
                C\TextColumn::make('check_out_at')->label('الخروج')->time('H:i'),
                C\IconColumn::make('is_manual')->label('يدوي')->boolean(),
            ])
            ->filters([
                Filters\SelectFilter::make('branch_id')->label('الفرع')->relationship('branch', 'name'),
                Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'on_time' => 'في الموعد', 'late' => 'متأخر',
                    'absent'  => 'غائب',      'excused' => 'معذور',
                ]),
                Filters\Filter::make('today')
                    ->label('اليوم فقط')
                    ->query(fn(Builder $q) => $q->whereDate('attendance_date', today())),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort('attendance_date', 'desc');
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
            'index'  => Pages\ListAttendanceLogs::route('/'),
            'create' => Pages\CreateAttendanceLog::route('/create'),
            'edit'   => Pages\EditAttendanceLog::route('/{record}/edit'),
        ];
    }
}
