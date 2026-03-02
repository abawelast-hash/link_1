<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Branch;
use Filament\Tables\{Table, Columns as C};
use Filament\Widgets\TableWidget as BaseWidget;

class CompanyLeaderboardWidget extends BaseWidget
{
    protected static ?int    $sort    = 3;
    protected int | string   $columnSpan = 'full';
    protected static ?string $heading = '🏅 لوحة المنافسة — ترتيب الفروع';

    public function table(Table $table): Table
    {
        return $table
            ->query(Branch::query()->where('is_active', true)->orderBy('total_points', 'desc'))
            ->columns([
                C\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                C\TextColumn::make('name')->label('الفرع')->searchable(),
                C\TextColumn::make('city')->label('المدينة'),
                C\TextColumn::make('total_points')->label('النقاط')->sortable(),
                C\BadgeColumn::make('level')->label('المستوى')
                    ->color(fn($s) => match($s) {
                        'أسطوري' => 'danger', 'ألماسي' => 'info',
                        'ذهبي'   => 'warning', default => 'gray',
                    }),
                C\TextColumn::make('users_count')->label('الموظفون')->counts('users'),
            ]);
    }
}
