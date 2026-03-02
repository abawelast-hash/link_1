<?php namespace App\Filament\Admin\Resources\AttendanceLogResource\Pages;
use App\Filament\Admin\Resources\AttendanceLogResource;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};
class ListAttendanceLogs   extends ListRecords   { protected static string $resource = AttendanceLogResource::class; }
