<?php

declare(strict_types=1);

namespace App\Filament\Resources\Feedback\Schemas;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Type')
                    ->options(collect(FeedbackType::cases())->mapWithKeys(fn (FeedbackType $t): array => [$t->value => $t->label()]))
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(collect(FeedbackStatus::cases())->mapWithKeys(fn (FeedbackStatus $s): array => [$s->value => $s->label()]))
                    ->required(),
                TextInput::make('title')
                    ->label('Title')
                    ->disabled()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Description')
                    ->disabled()
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('page_url')
                    ->label('Page URL')
                    ->disabled(),
                TextInput::make('contact_email')
                    ->label('Contact Email')
                    ->disabled(),
                Textarea::make('admin_notes')
                    ->label('Admin Notes / Internal Action')
                    ->placeholder('Notes on bug investigation, planned milestone, or resolution...')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
