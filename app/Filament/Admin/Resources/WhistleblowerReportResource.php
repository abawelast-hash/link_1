<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WhistleblowerReportResource\Pages;
use App\Models\WhistleblowerReport;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};

class WhistleblowerReportResource extends Resource
{
    protected static ?string $model           = WhistleblowerReport::class;
    protected static ?string $navigationIcon  = 'heroicon-o-lock-closed';
    protected static ?string $navigationGroup = 'الأمان';
    protected static ?string $label           = 'بلاغ';
    protected static ?string $pluralLabel     = 'قبو البلاغات';
    protected static ?int    $navigationSort  = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('access-whistleblower-vault') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Section::make('تفاصيل البلاغ')->schema([
                F\TextInput::make('token')->label('رمز المتابعة')->disabled(),
                F\TextInput::make('subject')->label('الموضوع')->required(),
                F\Select::make('category')->label('التصنيف')->options([
                    'financial' => 'مالي', 'administrative' => 'إداري',
                    'harassment' => 'تحرش', 'corruption' => 'فساد', 'other' => 'أخرى',
                ])->required(),
                F\Select::make('severity')->label('الخطورة')->options([
                    'low' => '🟢 منخفض', 'medium' => '🟡 متوسط',
                    'high' => '🟠 عالي',  'critical' => '🔴 حرج',
                ])->required(),
                F\Textarea::make('body')->label('المحتوى')->rows(5)->columnSpanFull(),
            ])->columns(2),
            F\Section::make('المتابعة (للمستوى 10 فقط)')->schema([
                F\Select::make('status')->label('الحالة')->options([
                    'received'     => 'مُستلم',      'under_review' => 'قيد المراجعة',
                    'escalated'    => 'مُصعَّد',      'resolved'     => '✅ مُحلول',
                    'closed'       => 'مُغلق',
                ])->required(),
                F\Select::make('assigned_to')->label('مُكلَّف لـ')
                    ->relationship('assignedTo', 'name')->searchable()->preload(),
                F\Textarea::make('internal_notes')->label('ملاحظات داخلية (سرية)')->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('token')->label('الرمز')->limit(12)->copyable(),
                C\TextColumn::make('subject')->label('الموضوع')->limit(40)->searchable(),
                C\TextColumn::make('category')->label('التصنيف'),
                C\BadgeColumn::make('severity')->label('الخطورة')
                    ->color(fn($s) => match($s) {
                        'critical' => 'danger', 'high' => 'warning',
                        'medium'   => 'info',    default => 'gray',
                    }),
                C\BadgeColumn::make('status')->label('الحالة')
                    ->color(fn($s) => match($s) {
                        'resolved' => 'success', 'closed' => 'gray',
                        'escalated' => 'danger',  default => 'warning',
                    }),
                C\IconColumn::make('is_anonymous')->label('مجهول')->boolean(),
                C\TextColumn::make('created_at')->label('تاريخ البلاغ')->date()->sortable(),
            ])
            ->filters([
                Filters\SelectFilter::make('severity')->label('الخطورة')->options([
                    'low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'عالي', 'critical' => 'حرج',
                ]),
                Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'received' => 'مُستلم', 'under_review' => 'قيد المراجعة',
                    'escalated' => 'مُصعَّد', 'resolved' => 'مُحلول', 'closed' => 'مُغلق',
                ]),
            ])
            ->actions([Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhistleblowerReports::route('/'),
            'edit'  => Pages\EditWhistleblowerReport::route('/{record}/edit'),
        ];
    }
}
