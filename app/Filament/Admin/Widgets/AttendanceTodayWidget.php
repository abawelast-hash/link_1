<?php

namespace App\Filament\Admin\Widgets;

use App\Models\AttendanceLog;
use Filament\Tables\{Table, Columns as C};
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AttendanceTodayWidget extends BaseWidget
{
    protected static ?int    $sort    = 2;
    protected int | string   $columnSpan = 'full';
    protected static ?string $heading = 'سجل الحضور اليوم';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AttendanceLog::query()
                    ->whereDate('attendance_date', today())
                    ->with(['user', 'branch'])
                    ->when(
                        ! auth()->user()?->isGodMode() && auth()->user()?->security_level < 7,
                        fn(Builder $q) => $q->where('branch_id', auth()->user()->branch_id)
                    )
            )
            ->columns([
                C\TextColumn::make('user.name')->label('الموظف'),
                C\TextColumn::make('branch.name')->label('الفرع'),
                C\TextColumn::make('check_in_at')->label('الدخول')->time('H:i'),
                C\TextColumn::make('check_out_at')->label('الخروج')->time('H:i')->placeholder('—'),
                C\BadgeColumn::make('status')->label('الحالة')
                    ->color(fn($s) => match($s) {
                        'on_time' => 'success', 'late' => 'warning',
                        'absent'  => 'danger',  'excused' => 'info',
                    })
                    ->formatStateUsing(fn($s) => match($s) {
                        'on_time' => '✅ في الموعد', 'late' => '⚠️ متأخر',
                        'absent'  => '❌ غائب',      'excused' => '📋 معذور',
                        default   => $s,
                    }),
                C\TextColumn::make('delay_minutes')->label('التأخير (د)'),
                C\TextColumn::make('financial_deduction')->label('الخصم')->money('SAR'),
            ]);
    }
}
