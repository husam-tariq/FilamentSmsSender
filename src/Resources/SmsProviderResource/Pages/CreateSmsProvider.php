<?php

namespace HusamTariq\FilamentSmsSender\Resources\SmsProviderResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use HusamTariq\FilamentSmsSender\Resources\SmsProviderResource;
use Filament\Notifications\Notification;

class CreateSmsProvider extends CreateRecord
{
    protected static string $resource = SmsProviderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filamentsmssender::filamentsmssender.provider_created_title'))
            ->body(__('filamentsmssender::filamentsmssender.provider_created_body'));
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        // If this is the first provider, make it default
        if (!$record->is_default && $record::active()->count() === 1) {
            $record->update(['is_default' => true]);

            Notification::make()
                ->success()
                ->title(__('filamentsmssender::filamentsmssender.default_provider_set_title'))
                ->body(__('filamentsmssender::filamentsmssender.default_provider_set_body'))
                ->send();
        }
    }
}
