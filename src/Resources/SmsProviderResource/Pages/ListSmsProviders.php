<?php

namespace HusamTariq\FilamentSmsSender\Resources\SmsProviderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use HusamTariq\FilamentSmsSender\Resources\SmsProviderResource;
use HusamTariq\FilamentSmsSender\Models\SmsProvider;
use Filament\Notifications\Notification;

class ListSmsProviders extends ListRecords
{
    protected static string $resource = SmsProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('filamentsmssender::filamentsmssender.add_provider')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }

    public function getTitle(): string
    {
        return __('filamentsmssender::filamentsmssender.page_title');
    }

    public function getSubheading(): ?string
    {
        $defaultProvider = SmsProvider::getDefault();
        $activeCount = SmsProvider::active()->count();

        if (!$defaultProvider) {
            return __('filamentsmssender::filamentsmssender.no_default_provider', ['count' => $activeCount]);
        }

        return __('filamentsmssender::filamentsmssender.default_provider_status', [
            'name' => $defaultProvider->name,
            'count' => $activeCount
        ]);
    }
}
