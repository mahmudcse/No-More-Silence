<?php

declare(strict_types=1);

namespace App\Filament\Resources\Feedback\Tables;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('description')->limit(50),
                TextColumn::make('contact_email')->label('Email')->placeholder('Anonymous'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(collect(FeedbackType::cases())->mapWithKeys(fn (FeedbackType $t): array => [$t->value => $t->label()])),
                SelectFilter::make('status')
                    ->options(collect(FeedbackStatus::cases())->mapWithKeys(fn (FeedbackStatus $s): array => [$s->value => $s->label()])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
