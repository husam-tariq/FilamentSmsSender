<?php

namespace HusamTariq\FilamentSmsSender;

use Filament\Contracts\Plugin;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Tabs;
use Filament\Panel;
use HusamTariq\FilamentSmsSender\Resources\SmsProviderResource;

class FilamentSmsSenderPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filamentsmssender';
    }

    public function register(Panel $panel): void
    {



        Field::macro('translatableTab', function (bool $translatable = true, ?array $customLocales = null, ?array $localeSpecificRules = null) {
            if (!$translatable) {
                return $this;
            }
            /** @var SpatieLaravelTranslatablePlugin $plugin */
            $spatieTranslatablePlugin = filament()->getPlugin('spatie-laravel-translatable');
            $locales = $spatieTranslatablePlugin->getDefaultLocales();
            $supportedLocales = [];
            foreach ($locales as $locale) {
                $supportedLocales[$locale] = $spatieTranslatablePlugin->getLocaleLabel($locale) ?? $locale;
            }

            /**
             * @var Field $field
             * @var Field $this
             */
            $field = $this->getClone();

            $tabs = collect($customLocales ?? $supportedLocales)
                ->map(function ($label, $key) use ($field, $localeSpecificRules) {
                    $locale = is_string($key) ? $key : $label;
                    $clone = $field
                        ->getClone()
                        ->name("{$field->getName()}.{$locale}")
                        ->label($field->getLabel())
                        ->statePath("{$field->getStatePath(false)}.{$locale}");

                    if ($localeSpecificRules && isset($localeSpecificRules[$locale])) {
                        $clone->rules($localeSpecificRules[$locale]);
                    }

                    return Tabs\Tab::make($locale)
                        ->label(is_string($key) ? $label : strtoupper($locale))
                        ->schema([$clone]);
                })
                ->toArray();

            $tabsField = Tabs::make('translations')
                ->tabs($tabs);

            return $tabsField;
        });
        $panel
            ->resources([
                SmsProviderResource::class,
            ]);

    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
