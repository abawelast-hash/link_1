<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};

class BranchResource extends Resource
{
    protected static ?string $model           = Branch::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'الموارد البشرية';
    protected static ?string $label           = 'فرع';
    protected static ?string $pluralLabel     = 'الفروع';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Section::make('بيانات الفرع')->schema([
                F\TextInput::make('name')->label('اسم الفرع')->required(),
                F\TextInput::make('code')->label('رمز الفرع')->required()->unique(ignoreRecord: true),
                F\TextInput::make('city')->label('المدينة'),
                F\Textarea::make('address')->label('العنوان')->rows(2),
            ])->columns(2),

            F\Section::make('إعدادات السياج الجغرافي')->schema([
                F\TextInput::make('latitude')->label('خط العرض')->numeric()->required(),
                F\TextInput::make('longitude')->label('خط الطول')->numeric()->required(),
                F\TextInput::make('geofence_radius')->label('نطاق التسجيل (متر)')->numeric()->default(17)->required(),
            ])->columns(3),

            F\Section::make('الإدارة')->schema([
                F\Select::make('manager_id')
                    ->label('مدير الفرع')
                    ->relationship('users', 'name')
                    ->searchable()
                    ->preload(),
                F\Toggle::make('is_active')->label('نشط')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('code')->label('الرمز')->searchable()->sortable(),
                C\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                C\TextColumn::make('city')->label('المدينة'),
                C\TextColumn::make('users_count')->label('الموظفون')
                    ->counts('users')->sortable(),
                C\TextColumn::make('total_points')->label('النقاط')->sortable(),
                C\BadgeColumn::make('level')->label('المستوى')
                    ->color(fn($state) => match($state) {
                        'أسطوري' => 'danger', 'ألماسي' => 'info',
                        'ذهبي'   => 'warning', 'فضي'   => 'gray',
                        'برونزي' => 'warning', default => 'gray',
                    }),
                C\ToggleColumn::make('is_active')->label('نشط'),
            ])
            ->filters([
                Filters\TernaryFilter::make('is_active')->label('الحالة'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort('total_points', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
