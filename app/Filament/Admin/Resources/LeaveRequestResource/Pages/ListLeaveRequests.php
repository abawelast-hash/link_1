<?php namespace App\Filament\Admin\Resources\LeaveRequestResource\Pages;
use App\Filament\Admin\Resources\LeaveRequestResource;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};
class ListLeaveRequests  extends ListRecords  { protected static string $resource = LeaveRequestResource::class; }
