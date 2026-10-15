<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reports\Schemas;

use App\Enums\HasLabel;
use App\Enums\Platform;
use App\Enums\ReportCategory;
use App\Enums\SubmittedVia;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('submitted_profile_url')
                ->label('Submitted profile URL')
                ->url()
                ->maxLength(2048),
            TextInput::make('submitted_comment_url')
                ->label('Submitted comment URL')
                ->url()
                ->maxLength(2048),
            Textarea::make('comment_text')
                ->label('Verbatim comment')
                ->rows(4),
            Textarea::make('comment_text_translation')
                ->label('Translation (optional)')
                ->rows(3),
            Select::make('category')
                ->label('Category')
                ->options(self::enumOptions(ReportCategory::class)),
            Select::make('severity')
                ->label('Severity')
                ->options([
                    1 => '1 - Low',
                    2 => '2 - Medium',
                    3 => '3 - High',
                ])
                ->nullable(),
            Select::make('workflow_status')
                ->label('Workflow status')
                ->options(self::enumOptions(WorkflowStatus::class))
                ->disabled(),
            Select::make('visibility')
                ->label('Visibility')
                ->options(self::enumOptions(Visibility::class)),
            Toggle::make('is_hidden_from_homepage')
                ->label('Hide from Homepage')
                ->helperText('When enabled, this report will not be displayed in the homepage feed.')
                ->default(false),
            Select::make('platform')
                ->label('Platform')
                ->options(self::enumOptions(Platform::class)),
            Select::make('submitted_via')
                ->label('Submitted via')
                ->options(self::enumOptions(SubmittedVia::class)),
            DateTimePicker::make('comment_posted_at')
                ->label('Comment posted at'),
        ]);
    }

    /**
     * @param  class-string<HasLabel & BackedEnum>  $enum
     * @return array<string, string>
     */
    private static function enumOptions(string $enum): array
    {
        return collect($enum::cases())
            ->mapWithKeys(fn (HasLabel&BackedEnum $case): array => [$case->value => $case->label()])
            ->all();
    }
}
