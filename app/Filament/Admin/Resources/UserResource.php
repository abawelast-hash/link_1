<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\{User, Branch};
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'الموارد البشرية';
    protected static ?string $label           = 'موظف';
    protected static ?string $pluralLabel     = 'الموظفون';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Section::make('البيانات الأساسية')->schema([
                F\TextInput::make('name')->label('الاسم الكامل')->required(),
                F\TextInput::make('email')->label('البريد الإلكتروني')->email()->required()->unique(ignoreRecord: true),
                F\TextInput::make('employee_id')->label('رقم الموظف')->unique(ignoreRecord: true),
                F\TextInput::make('phone')->label('الهاتف'),
                F\TextInput::make('position')->label('المنصب'),
                F\TextInput::make('department')->label('القسم'),
            ])->columns(2),

            F\Section::make('الصلاحيات والفرع')->schema([
                F\Select::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                F\Select::make('security_level')
                    ->label('مستوى الأمان')
                    ->options([
                        1 => '١ - متدرب',   2 => '٢ - موظف',
                        3 => '٣ - موظف أول', 4 => '٤ - قائد فريق',
                        5 => '٥ - مدير فرع', 6 => '٦ - مدير إقليمي',
                        7 => '٧ - مدير عمليات', 8 => '٨ - نائب المدير العام',
                        9 => '٩ - مدير تنفيذي', 10 => '١٠ - المدير العام',
                    ])
                    ->required()
                    ->default(2),
                F\Toggle::make('is_active')->label('نشط')->default(true),
                F\Toggle::make('is_super_admin')->label('مدير نظام')->hidden(fn() => ! auth()->user()?->isGodMode()),
            ])->columns(2),

            F\Section::make('المالية')->schema([
                F\TextInput::make('hourly_rate')->label('معدل الساعة (ريال)')->numeric()->default(0),
            ]),

            F\Section::make('كلمة المرور')->schema([
                F\TextInput::make('password')->label('كلمة المرور')
                    ->password()->revealable()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $operation) => $operation === 'create'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('employee_id')->label('رقم الوظيفي')->searchable()->sortable(),
                C\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                C\TextColumn::make('branch.name')->label('الفرع')->sortable(),
                C\BadgeColumn::make('security_level')->label('المستوى')
                    ->color(fn($state) => match(true) {
                        $state >= 10 => 'danger',
                        $state >= 7  => 'warning',
                        $state >= 4  => 'info',
                        default      => 'gray',
                    }),
                C\TextColumn::make('total_points')->label('النقاط')->sortable(),
                C\ToggleColumn::make('is_active')->label('نشط'),
                C\TextColumn::make('created_at')->label('تاريخ الإضافة')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filters\SelectFilter::make('branch_id')->label('الفرع')->relationship('branch', 'name'),
                Filters\SelectFilter::make('security_level')->label('المستوى')->options([
                    1=>'متدرب',2=>'موظف',3=>'موظف أول',4=>'قائد فريق',
                    5=>'مدير فرع',6=>'مدير إقليمي',7=>'مدير عمليات',
                    8=>'نائب المدير',9=>'مدير تنفيذي',10=>'المدير العام',
                ]),
                Filters\TernaryFilter::make('is_active')->label('الحالة'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        // مدير الفرع يرى موظفي فرعه فقط
        if (! $user->isGodMode() && $user->security_level < 7) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
