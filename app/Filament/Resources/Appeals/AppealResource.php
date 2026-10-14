<?php

namespace App\Filament\Resources\Appeals;

use App\Filament\Resources\Appeals\Pages\CreateAppeal;
use App\Filament\Resources\Appeals\Pages\EditAppeal;
use App\Filament\Resources\Appeals\Pages\ListAppeals;
use App\Filament\Resources\Appeals\Schemas\AppealForm;
use App\Filament\Resources\Appeals\Tables\AppealsTable;
use App\Models\Appeal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AppealResource extends Resource
{
    protected static ?string $model = Appeal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AppealForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppealsTable::configure($table);
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
            'index' => ListAppeals::route('/'),
            'create' => CreateAppeal::route('/create'),
            'edit' => EditAppeal::route('/{record}/edit'),
        ];
    }
}
