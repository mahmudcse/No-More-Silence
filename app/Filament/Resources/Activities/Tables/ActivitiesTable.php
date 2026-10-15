<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('log_name')->badge(),
                TextColumn::make('description')->label('Event'),
                TextColumn::make('causer.name')->label('Actor'),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '' : Str::afterLast($state, '\\')),
            ])
            ->filters([
                //
            ]);
    }
}
