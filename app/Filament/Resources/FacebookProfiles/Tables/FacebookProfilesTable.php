<?php

declare(strict_types=1);

namespace App\Filament\Resources\FacebookProfiles\Tables;

use App\Actions\Profiles\MergeProfiles;
use App\Models\FacebookProfile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacebookProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('current_display_name')->label('Name')->searchable(),
                TextColumn::make('canonical_url')->label('Canonical URL')->limit(40),
                TextColumn::make('profile_status')->badge(),
                TextColumn::make('public_reports_count')->label('Public records')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('merge')
                    ->label('Merge into')
                    ->color('danger')
                    ->form(fn (FacebookProfile $record): array => [
                        Select::make('primary_id')
                            ->label('Keep this profile')
                            ->options(
                                FacebookProfile::query()
                                    ->whereKeyNot($record->getKey())
                                    ->pluck('current_display_name', 'id')
                                    ->toArray(),
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data, FacebookProfile $record): void {
                        $primary = FacebookProfile::findOrFail($data['primary_id']);
                        app(MergeProfiles::class)->handle($primary, $record, auth()->user());
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
