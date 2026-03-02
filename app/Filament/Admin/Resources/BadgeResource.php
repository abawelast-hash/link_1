<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Actions};

class BadgeResource extends Resource
{
    protected static ?string $model           = Badge::class;
    protected static ?string $navigationIcon  = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'الموارد البشرية';
    protected static ?string $label           = 'شارة';
    protected static ?string $pluralLabel     = 'الشارات';
    protected static ?int    $navigationSort  = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\TextInput::make('name')->label('اسم الشارة')->required(),
            F\TextInput::make('slug')->label('المعرف')->unique(ignoreRecord: true)->required(),
            F\TextInput::make('icon')->label('الأيقونة (Emoji)')->default('🏅'),
            F\TextInput::make('points_reward')->label('النقاط المكافئة')->numeric()->default(0),
            F\Select::make('condition_type')->label('نوع الشرط')->options([
                'streak_days'       => 'سلسلة أيام متتالية',
                'monthly_no_late'   => 'شهر بدون تأخير',
                'monthly_no_absent' => 'شهر بدون غياب',
                'monthly_overtime'  => 'ساعات إضافية شهرية',
                'manual'            => 'يدوي فقط',
            ])->required(),
            F\KeyValue::make('condition_params')->label('معاملات الشرط')->columnSpanFull(),
            F\Textarea::make('description')->label('الوصف')->columnSpanFull(),
            F\Toggle::make('is_active')->label('نشطة')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('icon')->label(''),
                C\TextColumn::make('name')->label('الشارة')->searchable()->sortable(),
                C\TextColumn::make('points_reward')->label('النقاط')->sortable(),
                C\TextColumn::make('condition_type')->label('الشرط'),
                C\TextColumn::make('user_badges_count')->label('مُمنوحة')->counts('userBadges')->sortable(),
                C\ToggleColumn::make('is_active')->label('نشطة'),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit'   => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
