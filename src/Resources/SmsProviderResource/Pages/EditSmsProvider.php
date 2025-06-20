<?php

namespace HusamTariq\FilamentSmsSender\Resources\SmsProviderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use HusamTariq\FilamentSmsSender\Resources\SmsProviderResource;
use HusamTariq\FilamentSmsSender\Services\SmsService;
use Filament\Notifications\Notification;

class EditSmsProvider extends EditRecord
{
    protected static string $resource = SmsProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test')
                ->label(__('filamentsmssender::filamentsmssender.action_test_provider'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\TextInput::make('test_recipient')
                        ->label(__('filamentsmssender::filamentsmssender.test_phone_number'))
                        ->placeholder(__('filamentsmssender::filamentsmssender.test_phone_placeholder'))
                        ->tel()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('test_message')
                        ->label(__('filamentsmssender::filamentsmssender.test_message'))
                        ->placeholder(__('filamentsmssender::filamentsmssender.test_message_placeholder'))
                        ->default(__('filamentsmssender::filamentsmssender.test_message_default'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $smsService = app(SmsService::class);
                    $success = $smsService->sendWithProvider(
                        $data['test_recipient'],
                        $data['test_message'],
                        $this->record
                    );

                    if ($success['success']) {
                        Notification::make()
                            ->title(__('filamentsmssender::filamentsmssender.test_sms_sent_title'))
                            ->body(__('filamentsmssender::filamentsmssender.test_sms_sent_body'))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title(__('filamentsmssender::filamentsmssender.test_failed_title'))
                            ->body(__('filamentsmssender::filamentsmssender.test_failed_body'))
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('makeDefault')
                ->label(__('filamentsmssender::filamentsmssender.action_make_default'))
                ->icon('heroicon-o-star')
                ->color('warning')
                ->visible(fn(): bool => !$this->record->is_default && $this->record->is_active)
                ->requiresConfirmation()
                ->modalHeading(__('filamentsmssender::filamentsmssender.make_default_heading'))
                ->modalDescription(fn() => __('filamentsmssender::filamentsmssender.make_default_description', ['name' => $this->record->name]))
                ->action(function () {
                    $this->record->makeDefault();

                    Notification::make()
                        ->title(__('filamentsmssender::filamentsmssender.default_provider_updated_title'))
                        ->body(__('filamentsmssender::filamentsmssender.default_provider_updated_body', ['name' => $this->record->name]))
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->before(function () {
                    if (!$this->record->canBeDeleted()) {
                        Notification::make()
                            ->title(__('filamentsmssender::filamentsmssender.cannot_delete_provider_title'))
                            ->body(__('filamentsmssender::filamentsmssender.cannot_delete_provider_body'))
                            ->danger()
                            ->send();

                        return false;
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filamentsmssender::filamentsmssender.provider_updated_title'))
            ->body(__('filamentsmssender::filamentsmssender.provider_updated_body'));
    }
}
