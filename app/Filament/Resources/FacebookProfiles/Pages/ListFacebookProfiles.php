<?php

namespace App\Filament\Resources\FacebookProfiles\Pages;

use App\Filament\Resources\FacebookProfiles\FacebookProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFacebookProfiles extends ListRecords
{
    protected static string $resource = FacebookProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
