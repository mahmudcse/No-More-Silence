<?php

namespace App\Filament\Resources\FacebookProfiles\Pages;

use App\Filament\Resources\FacebookProfiles\FacebookProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFacebookProfile extends CreateRecord
{
    protected static string $resource = FacebookProfileResource::class;
}
