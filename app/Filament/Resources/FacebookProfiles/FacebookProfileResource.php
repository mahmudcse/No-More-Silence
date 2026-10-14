<?php

namespace App\Filament\Resources\FacebookProfiles;

use App\Filament\Resources\FacebookProfiles\Pages\CreateFacebookProfile;
use App\Filament\Resources\FacebookProfiles\Pages\EditFacebookProfile;
use App\Filament\Resources\FacebookProfiles\Pages\ListFacebookProfiles;
use App\Filament\Resources\FacebookProfiles\Schemas\FacebookProfileForm;
use App\Filament\Resources\FacebookProfiles\Tables\FacebookProfilesTable;
use App\Models\FacebookProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FacebookProfileResource extends Resource
{
    protected static ?string $model = FacebookProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FacebookProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacebookProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacebookProfiles::route('/'),
            'create' => CreateFacebookProfile::route('/create'),
            'edit' => EditFacebookProfile::route('/{record}/edit'),
        ];
    }
}
