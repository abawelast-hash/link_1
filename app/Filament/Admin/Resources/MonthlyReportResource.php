<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MonthlyReportResource\Pages;
use App\Models\MonthlyReport;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};
use Illuminate\Database\Eloquent\Builder;

class MonthlyReportResource extends Resource
{
    protected static ?string $model           = MonthlyReport::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'التقارير والمالية';
    protected static ?string $label           = 'تقرير شهري';
    protected static ?string $pluralLabel     = 'التقارير الشهرية';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Select::make('user_id')->label('الموظف')->relationship('user', 'name')->searchable()->preload()->required(),
            F\TextInput::make('year')->label('السنة')->numeric()->required(),
            F\Select::make('month')->label('الشهر')->options([
                1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',
                5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',
                9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
            ])->required(),
            F\TextInput::make('present_days')->label('أيام الحضور')->numeric(),
            F\TextInput::make('late_days')->label('أيام التأخير')->numeric(),
            F\TextInput::make('absent_days')->label('أيام الغياب')->numeric(),
            F\TextInput::make('total_overtime_hours')->label('ساعات إضافية')->numeric(),
            F\TextInput::make('total_deduction')->label('إجمالي الخصومات')->numeric(),
            F\TextInput::make('base_salary')->label('الراتب الأساسي')->numeric(),
            F\TextInput::make('net_salary')->label('صافي الراتب')->numeric(),
            F\TextInput::make('total_points')->label('النقاط')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('user.name')->label('الموظف')->searchable()->sortable(),
                C\TextColumn::make('branch.name')->label('الفرع')->sortable(),
                C\TextColumn::make('year')->label('السنة')->sortable(),
                C\TextColumn::make('month')->label('الشهر')->sortable()
                    ->formatStateUsing(fn($s) => [
                        1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',
                        5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',
                        9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
                    ][$s] ?? $s),
                C\TextColumn::make('present_days')->label('حضور'),
                C\TextColumn::make('absent_days')->label('غياب'),
                C\TextColumn::make('total_deduction')->label('الخصومات')->money('SAR')->sortable(),
                C\TextColumn::make('net_salary')->label('صافي الراتب')->money('SAR')->sortable(),
                C\TextColumn::make('total_points')->label('النقاط')->sortable(),
            ])
            ->filters([
                Filters\SelectFilter::make('branch_id')->label('الفرع')->relationship('branch', 'name'),
                Filters\Filter::make('current_month')
                    ->label('الشهر الحالي')
                    ->query(fn(Builder $q) => $q->where('year', now()->year)->where('month', now()->month)),
            ])
            ->actions([Actions\ViewAction::make(), Actions\EditAction::make()])
            ->defaultSort('year', 'desc');
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
            'index' => Pages\ListMonthlyReports::route('/'),
            'view'  => Pages\ViewMonthlyReport::route('/{record}'),
            'edit'  => Pages\EditMonthlyReport::route('/{record}/edit'),
        ];
    }
}
