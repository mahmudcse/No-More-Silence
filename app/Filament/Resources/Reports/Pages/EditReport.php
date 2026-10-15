<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Pages\ReviewQueue;
use App\Filament\Resources\Reports\ReportResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('hide_from_homepage')
                ->label('Hide from Homepage')
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Hide report from homepage?')
                ->modalDescription('This report will be hidden from the homepage feed. You can publish or restore it to the homepage at any time.')
                ->visible(fn (): bool => ! $this->record->is_hidden_from_homepage)
                ->action(function (): void {
                    $this->record->update(['is_hidden_from_homepage' => true]);
                    Notification::make()
                        ->title('Report hidden from homepage')
                        ->warning()
                        ->send();
                }),
            Action::make('publish_to_homepage')
                ->label('Publish to Homepage')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Publish report to homepage?')
                ->modalDescription('This report will become visible in the homepage feed.')
                ->visible(fn (): bool => (bool) $this->record->is_hidden_from_homepage)
                ->action(function (): void {
                    $this->record->update(['is_hidden_from_homepage' => false]);
                    Notification::make()
                        ->title('Report published to homepage')
                        ->success()
                        ->send();
                }),
            Action::make('review')
                ->label('Open in Review Queue')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->url(fn (): string => ReviewQueue::getUrl(['report' => $this->record->id])),
            DeleteAction::make(),
        ];
    }
}
