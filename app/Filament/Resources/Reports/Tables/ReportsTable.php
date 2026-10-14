<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reports\Tables;

use App\Actions\Reports\PublishReport;
use App\Actions\Reports\RejectReport;
use App\Actions\Reports\RequestInfo;
use App\Actions\Reports\RetractReport;
use App\Actions\Reports\VerifyReport;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workflow_status')->badge()->sortable(),
                TextColumn::make('category')->badge()->sortable(),
                TextColumn::make('visibility')->badge(),
                TextColumn::make('comment_text')->limit(60)->searchable(),
                TextColumn::make('facebookProfile.current_display_name')->label('Profile'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('request_info')
                    ->label('Request info')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::InVerification)
                    ->action(fn (Report $record) => app(RequestInfo::class)->handle($record, auth()->user())),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->form([
                        TextInput::make('reason')->label('Reason')->required(),
                    ])
                    ->visible(fn (Report $record): bool => in_array($record->workflow_status, [WorkflowStatus::Triaged, WorkflowStatus::InVerification], true))
                    ->action(function (array $data, Report $record): void {
                        app(RejectReport::class)->handle($record, auth()->user(), $data['reason']);
                    }),
                Action::make('verify')
                    ->label('Verify')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::InVerification)
                    ->action(fn (Report $record) => app(VerifyReport::class)->handle($record, auth()->user(), [])),
                Action::make('publish')
                    ->label('Publish')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::Verified && $record->visibility === Visibility::Hidden)
                    ->action(fn (Report $record) => app(PublishReport::class)->handle($record, auth()->user(), Visibility::Public)),
                Action::make('retract')
                    ->label('Retract')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record): bool => $record->workflow_status === WorkflowStatus::Verified && $record->visibility !== Visibility::Hidden)
                    ->action(fn (Report $record) => app(RetractReport::class)->handle($record, auth()->user())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
