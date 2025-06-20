<?php

namespace HusamTariq\FilamentSmsSender\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use HusamTariq\FilamentSmsSender\Models\SmsProvider;
use HusamTariq\FilamentSmsSender\Resources\SmsProviderResource\Pages;
use HusamTariq\FilamentSmsSender\Services\SmsService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SmsProviderResource extends Resource
{
    protected static ?string $model = SmsProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filamentsmssender::filamentsmssender.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filamentsmssender::filamentsmssender.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filamentsmssender::filamentsmssender.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filamentsmssender::filamentsmssender.plural_model_label');
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filamentsmssender::filamentsmssender.provider_information'))
                    ->description(__('filamentsmssender::filamentsmssender.provider_information_description'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('filamentsmssender::filamentsmssender.provider_name'))
                                    ->placeholder(__('filamentsmssender::filamentsmssender.provider_name_placeholder'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('filamentsmssender::filamentsmssender.is_active'))
                                    ->default(true)
                                    ->helperText(__('filamentsmssender::filamentsmssender.is_active_help')),
                            ]),

                        Forms\Components\Toggle::make('is_default')
                            ->label(__('filamentsmssender::filamentsmssender.is_default'))
                            ->helperText(__('filamentsmssender::filamentsmssender.is_default_help'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $set('is_active', true);
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('filamentsmssender::filamentsmssender.api_configuration'))
                    ->description(__('filamentsmssender::filamentsmssender.api_configuration_description'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('request_method')
                                    ->label(__('filamentsmssender::filamentsmssender.http_method'))
                                    ->options([
                                        'GET' => 'GET',
                                        'POST' => 'POST',
                                    ])
                                    ->default('POST')
                                    ->required(),

                                Forms\Components\TextInput::make('api_endpoint_url')
                                    ->label(__('filamentsmssender::filamentsmssender.api_endpoint_url'))
                                    ->placeholder(__('filamentsmssender::filamentsmssender.api_endpoint_placeholder'))
                                    ->url()
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Repeater::make('request_parameters')
                            ->label(__('filamentsmssender::filamentsmssender.request_parameters'))
                            // ->description(__('filamentsmssender::filamentsmssender.request_parameters_description'))
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('key')
                                            ->label(__('filamentsmssender::filamentsmssender.parameter_name'))
                                            ->placeholder(__('filamentsmssender::filamentsmssender.parameter_name_placeholder'))
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('value')
                                            ->label(__('filamentsmssender::filamentsmssender.parameter_value'))
                                            ->placeholder(__('filamentsmssender::filamentsmssender.parameter_value_placeholder'))
                                            ->required()
                                            ->maxLength(1000),
                                    ]),
                            ])
                            ->itemLabel(fn(array $state): ?string => $state['key'] ?? null)
                            ->addActionLabel(__('filamentsmssender::filamentsmssender.add_parameter'))
                            ->defaultItems(0)
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('headers')
                            ->label(__('filamentsmssender::filamentsmssender.http_headers'))
                            //   ->description(__('filamentsmssender::filamentsmssender.http_headers_description'))
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('key')
                                            ->label(__('filamentsmssender::filamentsmssender.header_name'))
                                            ->placeholder(__('filamentsmssender::filamentsmssender.header_name_placeholder'))
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('value')
                                            ->label(__('filamentsmssender::filamentsmssender.header_value'))
                                            ->placeholder(__('filamentsmssender::filamentsmssender.header_value_placeholder'))
                                            ->required()
                                            ->maxLength(1000),
                                    ]),
                            ])
                            ->itemLabel(fn(array $state): ?string => $state['key'] ?? null)
                            ->addActionLabel(__('filamentsmssender::filamentsmssender.add_header'))
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make(__('filamentsmssender::filamentsmssender.otp_configuration'))
                    ->description(__('filamentsmssender::filamentsmssender.otp_configuration_description'))
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('otp_length')
                                    ->label(__('filamentsmssender::filamentsmssender.otp_length'))
                                    ->numeric()
                                    ->minValue(4)
                                    ->maxValue(8)
                                    ->default(6)
                                    ->required(),

                                Forms\Components\TextInput::make('otp_expiry_minutes')
                                    ->label(__('filamentsmssender::filamentsmssender.otp_expiry_minutes'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(60)
                                    ->default(10)
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('otp_template')
                            ->label(__('filamentsmssender::filamentsmssender.otp_template'))
                            ->placeholder(__('filamentsmssender::filamentsmssender.otp_template_placeholder'))
                            ->default(__('filamentsmssender::filamentsmssender.otp_template_default'))
                            ->required()
                            ->rows(3)->translatableTab()
                            ->columnSpanFull()
                        // ->helperText(__('filamentsmssender::filamentsmssender.otp_template_help'))
                        ,
                    ]),

                Forms\Components\Section::make(__('filamentsmssender::filamentsmssender.success_configuration'))
                    ->description(__('filamentsmssender::filamentsmssender.success_configuration_description'))
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('success_code')
                                    ->label(__('filamentsmssender::filamentsmssender.success_code'))
                                    ->numeric()
                                    ->default(200)
                                    ->required(),

                                Forms\Components\TextInput::make('success_body')
                                    ->label(__('filamentsmssender::filamentsmssender.success_body'))
                                    ->default('sent successfully')
                                    ->required(),

                                Forms\Components\Select::make('success_conditional_body')
                                    ->label(__('filamentsmssender::filamentsmssender.success_conditional_body'))
                                    ->options([
                                        '=' => __('filamentsmssender::filamentsmssender.success_conditional_equal'),
                                        '>' => __('filamentsmssender::filamentsmssender.success_conditional_greater'),
                                        '<' => __('filamentsmssender::filamentsmssender.success_conditional_less'),
                                        'like' => __('filamentsmssender::filamentsmssender.success_conditional_like'),
                                    ])
                                    ->default('like')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filamentsmssender::filamentsmssender.table_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('api_endpoint_url')
                    ->label(__('filamentsmssender::filamentsmssender.table_endpoint'))
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        return $column->getState();
                    }),

                Tables\Columns\TextColumn::make('request_method')
                    ->label(__('filamentsmssender::filamentsmssender.table_method'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'GET' => 'success',
                        'POST' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('filamentsmssender::filamentsmssender.table_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filamentsmssender::filamentsmssender.table_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filamentsmssender::filamentsmssender.table_created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('filamentsmssender::filamentsmssender.filter_default_provider')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filamentsmssender::filamentsmssender.filter_active')),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label(__('filamentsmssender::filamentsmssender.action_test'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('test_recipient')
                            ->label(__('filamentsmssender::filamentsmssender.test_phone_number'))
                            ->placeholder(__('filamentsmssender::filamentsmssender.test_phone_placeholder'))
                            ->tel()
                            ->required(),

                        Forms\Components\TextInput::make('test_message')
                            ->label(__('filamentsmssender::filamentsmssender.test_message'))
                            ->placeholder(__('filamentsmssender::filamentsmssender.test_message_placeholder'))
                            ->default(__('filamentsmssender::filamentsmssender.test_message_default'))
                            ->required(),
                    ])
                    ->action(function (SmsProvider $record, array $data) {
                        $smsService = app(SmsService::class);
                        $result = $smsService->send(
                            $data['test_recipient'],
                            $data['test_message'],
                            $record->name
                        );
                        $code = $result['code'] ?? '-';
                        $body = $result['body'] ?? '';
                        if ($result['success']) {
                            Notification::make()
                                ->title(__('filamentsmssender::filamentsmssender.test_sms_sent_title') . " (HTTP $code)")
                                ->body(__('filamentsmssender::filamentsmssender.test_sms_sent_body') . "\n\n" . __('filamentsmssender::filamentsmssender.http_response_body') . ":\n" . $body)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('filamentsmssender::filamentsmssender.test_failed_title') . ($code ? " (HTTP $code)" : ''))
                                ->body(__('filamentsmssender::filamentsmssender.test_failed_body') . "\n\n" . __('filamentsmssender::filamentsmssender.http_response_body') . ":\n" . $body)
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('makeDefault')
                    ->label(__('filamentsmssender::filamentsmssender.action_make_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(SmsProvider $record): bool => !$record->is_default && $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading(__('filamentsmssender::filamentsmssender.make_default_heading'))
                    ->modalDescription(fn(SmsProvider $record) => __('filamentsmssender::filamentsmssender.make_default_description', ['name' => $record->name]))
                    ->action(function (SmsProvider $record) {
                        $record->makeDefault();

                        Notification::make()
                            ->title(__('filamentsmssender::filamentsmssender.default_provider_updated_title'))
                            ->body(__('filamentsmssender::filamentsmssender.default_provider_updated_body', ['name' => $record->name]))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (SmsProvider $record) {
                        if (!$record->canBeDeleted()) {
                            Notification::make()
                                ->title(__('filamentsmssender::filamentsmssender.cannot_delete_provider_title'))
                                ->body(__('filamentsmssender::filamentsmssender.cannot_delete_provider_body'))
                                ->danger()
                                ->send();

                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $activeCount = SmsProvider::active()->count();
                            $recordsToDelete = $records->where('is_active', true)->count();

                            if ($activeCount - $recordsToDelete < 1) {
                                Notification::make()
                                    ->title(__('filamentsmssender::filamentsmssender.cannot_delete_all_active_title'))
                                    ->body(__('filamentsmssender::filamentsmssender.cannot_delete_all_active_body'))
                                    ->danger()
                                    ->send();

                                return false;
                            }
                        }),
                ]),
            ])
            ->defaultSort('is_default', 'desc')
            ->poll('30s');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['otpCodes']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsProviders::route('/'),
            'create' => Pages\CreateSmsProvider::route('/create'),
            'edit' => Pages\EditSmsProvider::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::active()->count();

        return match (true) {
            $count === 0 => 'danger',
            $count === 1 => 'warning',
            default => 'success',
        };
    }
}
