<?php

namespace App\Filament\Resources\Posts\Concerns;

use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Throwable;

trait AutosavesPostRecord
{
    public ?string $lastAutosavedAt = null;

    public function mountAutosavesPostRecord(): void
    {
        /** @var Post $record */
        $record = $this->record;

        $this->lastAutosavedAt = $record->updated_at?->toIso8601String();
    }

    public function autosave(): void
    {
        if (! $this->formHasUnsavedChanges()) {
            return;
        }

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
            $this->lastAutosavedAt = now()->toIso8601String();
        } catch (ValidationException) {
            return;
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function getLastAutosavedLabel(): ?string
    {
        if (blank($this->lastAutosavedAt)) {
            return null;
        }

        return Carbon::parse($this->lastAutosavedAt)->locale(app()->getLocale())->isoFormat('LLL');
    }

    protected function formHasUnsavedChanges(): bool
    {
        $currentHash = md5((string) str(json_encode($this->data, JSON_UNESCAPED_UNICODE))->replace('\\', ''));

        return $currentHash !== $this->savedDataHash;
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        parent::save($shouldRedirect, $shouldSendSavedNotification);

        $this->lastAutosavedAt = now()->toIso8601String();
    }
}
