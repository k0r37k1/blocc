<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\ResultStores\ResultStore;

class SystemHealth extends Page
{
    protected string $view = 'filament.pages.system-health';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'System Health';

    protected static ?string $title = 'System Health';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 10;

    public function getViewData(): array
    {
        $latestResults = app(ResultStore::class)->latestResults();

        return [
            'checkResults' => $latestResults?->storedCheckResults ?? collect(),
            'lastRanAt' => $latestResults?->storedCheckResults->first()?->ended_at,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runChecks')
                ->label(__('Run checks now'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    Artisan::call(RunHealthChecksCommand::class);

                    Notification::make()
                        ->title(__('Health checks completed'))
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
