<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CircularResource\Pages;
use App\Jobs\SendCircularJob;
use App\Models\Circular;
use Filament\Forms\{Form, Components as F};
use Filament\Resources\Resource;
use Filament\Tables\{Table, Columns as C, Filters, Actions};

class CircularResource extends Resource
{
    protected static ?string $model           = Circular::class;
    protected static ?string $navigationIcon  = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'التواصل';
    protected static ?string $label           = 'تعميم';
    protected static ?string $pluralLabel     = 'التعاميم';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            F\Section::make('بيانات التعميم')->schema([
                F\TextInput::make('title')->label('العنوان')->required(),
                F\Select::make('priority')->label('الأولوية')->options([
                    'normal' => 'عادي', 'high' => 'عالية', 'urgent' => '🔴 عاجل',
                ])->default('normal')->required(),
                F\RichEditor::make('body')->label('المحتوى')->required()->columnSpanFull(),
                F\FileUpload::make('attachment')->label('مرفق')->directory('circulars'),
            ])->columns(2),
            F\Section::make('الاستهداف')->schema([
                F\Select::make('target_branches')->label('الفروع المستهدفة (فارغ = الكل)')
                    ->multiple()->relationship('', 'name', fn($q) => \App\Models\Branch::query())
                    ->preload(),
                F\Select::make('target_levels')->label('المستويات المستهدفة (فارغ = الكل)')
                    ->multiple()->options([
                        1=>'متدرب', 2=>'موظف', 3=>'موظف أول', 4=>'قائد فريق',
                        5=>'مدير فرع', 6=>'مدير إقليمي', 7=>'مدير عمليات',
                        8=>'نائب المدير', 9=>'مدير تنفيذي', 10=>'المدير العام',
                    ]),
                F\DateTimePicker::make('published_at')->label('وقت النشر (فارغ = فوري)'),
                F\DateTimePicker::make('expires_at')->label('ينتهي في'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                C\TextColumn::make('title')->label('العنوان')->searchable()->sortable()->limit(50),
                C\BadgeColumn::make('priority')->label('الأولوية')
                    ->color(fn($s) => match($s) { 'urgent' => 'danger', 'high' => 'warning', default => 'gray' })
                    ->formatStateUsing(fn($s) => match($s) { 'urgent' => '🔴 عاجل', 'high' => 'عالية', default => 'عادي' }),
                C\TextColumn::make('author.name')->label('المُرسل'),
                C\TextColumn::make('reads_count')->label('القراءات')->counts('reads'),
                C\TextColumn::make('published_at')->label('وقت النشر')->dateTime()->sortable(),
            ])
            ->actions([
                Actions\Action::make('publish')
                    ->label('نشر الآن')->icon('heroicon-o-paper-airplane')->color('success')
                    ->visible(fn(Circular $r) => is_null($r->published_at))
                    ->action(function (Circular $circular) {
                        $circular->update(['published_at' => now()]);
                        SendCircularJob::dispatch($circular);
                    }),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCirculars::route('/'),
            'create' => Pages\CreateCircular::route('/create'),
            'edit'   => Pages\EditCircular::route('/{record}/edit'),
        ];
    }
}
