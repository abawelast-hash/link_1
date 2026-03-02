<?php

namespace App\Filament\App\Pages;

use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\{On, Computed};

/**
 * صفحة تسجيل الحضور — الصفحة الرئيسية في بوابة الموظف.
 */
class AttendancePage extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'تسجيل الحضور';
    protected static ?string $title           = 'تسجيل الحضور';
    protected static string  $view            = 'filament.app.pages.attendance';
    protected static ?int    $navigationSort  = 1;

    public ?float $latitude  = null;
    public ?float $longitude = null;
    public bool   $locating  = false;
    public bool   $checkedIn = false;

    public function mount(): void
    {
        $today          = today()->toDateString();
        $this->checkedIn = auth()->user()
            ->attendanceLogs()
            ->whereDate('attendance_date', $today)
            ->whereNotNull('check_in_at')
            ->exists();
    }

    public function checkIn(AttendanceService $service): void
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            Notification::make()->title('خطأ')->body('تعذّر تحديد موقعك. يرجى السماح بالوصول إلى GPS.')->danger()->send();
            return;
        }

        $result = $service->checkIn(auth()->user(), $this->latitude, $this->longitude);

        if ($result['success']) {
            $this->checkedIn = true;
            Notification::make()
                ->title('✅ تم تسجيل حضورك')
                ->body("النقاط المكتسبة: {$result['points']}")
                ->success()->send();
        } else {
            Notification::make()->title('❌ فشل التسجيل')->body($result['message'])->danger()->send();
        }
    }

    public function checkOut(AttendanceService $service): void
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            Notification::make()->title('خطأ')->body('تعذّر تحديد موقعك.')->danger()->send();
            return;
        }

        $result = $service->checkOut(auth()->user(), $this->latitude, $this->longitude);

        Notification::make()
            ->title($result['success'] ? '✅ تم تسجيل انصرافك' : '❌ خطأ')
            ->body($result['message'])
            ->{$result['success'] ? 'success' : 'danger'}()
            ->send();
    }

    protected function getActions(): array
    {
        return [];
    }
}
