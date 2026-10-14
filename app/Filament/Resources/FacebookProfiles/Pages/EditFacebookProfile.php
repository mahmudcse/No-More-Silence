<?php

namespace App\Filament\Resources\FacebookProfiles\Pages;

use App\Filament\Resources\FacebookProfiles\FacebookProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFacebookProfile extends EditRecord
{
    protected static string $resource = FacebookProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
