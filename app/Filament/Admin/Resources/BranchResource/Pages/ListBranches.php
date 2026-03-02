<?php namespace App\Filament\Admin\Resources\BranchResource\Pages;
use App\Filament\Admin\Resources\BranchResource;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};
class ListBranches  extends ListRecords  { protected static string $resource = BranchResource::class; }
