<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AnomalyLogResource\Pages;
use App\Models\AnomalyLog;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};

class AnomalyLogResource extends Resource
{
    protected static ?string $model           = AnomalyLog::class;
    protected static ?string $navigationIcon  = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'الأمان';
    protected static ?string $label           = 'شذوذ مكتشف';
    protected static ?string $pluralLabel     = 'الشذوذات المكتشفة';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Select::make('user_id')->label('الموظف')->relationship('user', 'name')->searchable()->preload()->required(),
            F\TextInput::make('type')->label('النوع')->required(),
            F\Select::make('severity')->label('الخطورة')->options([
                'low' => '🟢 منخفض', 'medium' => '🟡 متوسط', 'high' => '🟠 عالي',
            ])->required(),
            F\Textarea::make('description')->label('الوصف')->required()->columnSpanFull(),
            F\Toggle::make('is_reviewed')->label('تمت المراجعة')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('user.name')->label('الموظف')->searchable()->sortable(),
                C\TextColumn::make('type')->label('النوع'),
                C\BadgeColumn::make('severity')->label('الخطورة')
                    ->color(fn($s) => match($s) { 'high' => 'danger', 'medium' => 'warning', default => 'gray' }),
                C\TextColumn::make('description')->label('الوصف')->limit(60),
                C\IconColumn::make('is_reviewed')->label('مُراجَع')->boolean(),
                C\TextColumn::make('created_at')->label('التاريخ')->dateTime()->sortable(),
            ])
            ->filters([
                Filters\SelectFilter::make('severity')->label('الخطورة')->options([
                    'low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'عالي',
                ]),
                Filters\TernaryFilter::make('is_reviewed')->label('المراجعة'),
            ])
            ->actions([
                Actions\Action::make('mark_reviewed')
                    ->label('تمت المراجعة')->icon('heroicon-o-check')->color('success')
                    ->visible(fn(AnomalyLog $r) => ! $r->is_reviewed)
                    ->action(fn(AnomalyLog $r) => $r->update([
                        'is_reviewed' => true, 'reviewed_by' => auth()->id(),
                    ])),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAnomalyLogs::route('/')];
    }
}
