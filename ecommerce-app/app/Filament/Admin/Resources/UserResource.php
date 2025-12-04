<?php

namespace App\Filament\Clusters\Employees\Resources;

use App\Classes\BaseResource;
use App\Classes\BranchSelect;
use App\Classes\Core;
use App\Classes\CustomChaosTables as ChaosTables;
use App\Enums\Others\UserSex;
use App\Enums\RecipientType;
use App\Filament\Actions\RegisterExpenseAction;
use App\Filament\Clusters\Employees\Employees;
use App\Filament\Clusters\Employees\Resources\UserResource\Pages;
use App\Filament\Fields\Actions\RateAction;
use App\Filament\RelationManagers\ExpensesRelationManager;
use App\Models\Role;
use App\Models\User;
use App\Rules\UniqueUserInTenantRule;
use App\Services\MPdfService;
use App\Traits\HasNotificationSelectionAction;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use LaraZeus\Chaos\Filament\ChaosResource\ChaosForms;
use Mokhosh\FilamentRating\Entries\RatingEntry;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class UserResource extends BaseResource
{
    use HasNotificationSelectionAction;


    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = -99;

    protected static ?string $navigationIcon = 'tabler-user';

    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'mobile'];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var Panel $panel */
        $panel = Filament::getCurrentPanel();
        $query = User::query()->whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', config('globals.excluded_roles_for_user'));
        });
        $panelId = $panel->getId();
        if ($panelId === 'admin') {
            return $query->withoutGlobalScopes();
        } elseif ($panelId === 'tenant') {
            $query = $query->whereHas('tenants', function ($q) {
                $q->where('tenants.id', tenant('id'));
            });

            // Apply branch filtering if a branch is selected
            return \App\Classes\Core::applyBranchScope($query);
        }

        return $query;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __(self::langFile() . '.mobile') => $record->effective_mobile,
            __(self::langFile() . '.email') => $record->email,
            __(self::langFile() . '.login_number') => $record->login_number,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function form(Form $form): Form
    {
        try {
            $roles = request()->get('roles') ?? [];
            $roles = collect($roles)->map(function ($role) {
                return Role::findByName($role)->id;
            })->toArray();
        } catch (\Throwable $th) {
            $roles = [];
        }

        return ChaosForms::make($form, [
            Section::make()
                ->columns()
                ->schema([
                    ...self::columns(),
                    CheckboxList::make('roles')
                        ->default($roles)
                        ->required()
                        ->options(function () {
                            $roles = Role::query();
                            $roles->whereNotIn('name', config('globals.excluded_roles_for_user'));
                            $notTranslatedRoles = $roles->get();
                            $translatedRoles = [];
                            foreach ($notTranslatedRoles as $role) {
                                $translatedRoles[$role->id] = __("roles.roles.{$role->name}");
                            }

                            return $translatedRoles;
                        })
                        ->formatStateUsing(function ($record) use ($roles) {
                            return $record ? $record->roles->pluck('id')->toArray() : $roles;
                        })
                        ->searchable(),
                ]),

        ]);
    }

    public static function columns($onAdminPanel = false): array
    {
        return [
            TextInput::make('name')
                ->label(__('users.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->email()
                ->label(__('users.email'))
                ->required()
                ->lazy()
                ->afterStateUpdated(function (HasForms $livewire, Field $component) {
                    // Validation will be handled automatically by Filament
                })
                ->rule(fn($record) => new UniqueUserInTenantRule('email', $record))
                ->maxLength(255),
            TextInput::make('password')
                ->password()
                ->required(fn() => $onAdminPanel)
                ->label(__('users.password'))
                ->revealable()
                ->dehydrated(function ($state, $operation) {
                    if ($operation === 'create') {
                        return true;
                    }
                    if ($state !== null) {
                        return true;
                    }

                    return false;
                })
                ->dehydrateStateUsing(function ($state, $operation) {
                    if ($operation === 'create') {
                        return Hash::make($state);
                    }

                    if ($state !== null) {
                        return Hash::make($state);
                    }

                    return false;
                })
                ->required(fn($operation) => $operation === 'create')
                ->maxLength(255),
            \App\Classes\Core::phoneInput('mobile', __('users.mobile'))
                ->displayNumberFormat(PhoneInputNumberType::RFC3966)
                ->locale(app()->getLocale())
                ->validateFor(lenient: true)
                ->strictMode(true)
                ->formatAsYouType(true)
                ->formatOnDisplay(true)
                ->nationalMode(true)
                ->required()
                ->lazy()
                ->afterStateUpdated(function (HasForms $livewire, Field $component) {
                    // Validation will be handled automatically by Filament
                })
                ->rule(fn($record) => new UniqueUserInTenantRule('mobile', $record)),

            Select::make('tenants')
                ->preload()
                ->relationship(
                    name: 'tenants',
                    titleAttribute: 'name'
                )
                ->hidden(fn() => $onAdminPanel || Core::weAreOnTenantPanel())
                ->multiple()
                // ->visible(function () {
                //     /** @var Panel $panel */
                //     $panel = Filament::getCurrentPanel();

                //     return $panel->getId() === 'admin';
                // })
                ->label(__('app.tenants'))
                ->dehydrated(false),
            TextInput::make('job_title')
                ->required()
                ->default(fn() => $onAdminPanel ? 'مدير منشأة' : null)
                ->label(__('users.job_title')),
            TextInput::make('id_number')
                // ->required()
                ->label(__('users.id_number')),
            BranchSelect::make()
                ->hidden(fn() => $onAdminPanel)
                ->required(false),
            Toggle::make('is_active')
                ->default(true)
                ->hidden(fn() => $onAdminPanel)
                ->label(__('users.is_active')),
            ToggleButtons::make('sex')
                ->inline()
                ->required()
                ->default(UserSex::MALE)
                ->options(UserSex::class)
                ->label(__('users.sex')),
        ];
    }

    public static function table(Table $table): Table
    {
        return ChaosTables::make(
            resource: static::class,
            table: $table,
            columns: [
                IconColumn::make('is_logged_in')
                    ->label(__('users.is_logged_in'))
                    ->searchable(false)
                    ->sortable(false)
                    ->boolean(),
                Core::normalizedSearchColumn('name', __('users.name'), additionalSearchFields: ['email'])
                    ->description(fn($record): HtmlString => new HtmlString($record->email))
                    ->html(),
                TextColumn::make('tenants.name')
                    ->badge()
                    ->visible(fn() => Core::weAreOnAdminPanel())
                    ->label(__('app.tenants')),
                TextColumn::make('login_number')
                    ->label(__('users.login_number'))
                    ->copyable()
                    ->copyMessage(__('users.login_number_copied'))
                    ->copyMessageDuration(1500),

                TextColumn::make('roles.name')
                    ->label(__('users.roles'))
                    ->badge()
                    ->formatStateUsing(fn($state) => __("roles.roles.{$state}"))
                    ->sortable(false)
                    ->searchable(false),
                TextColumn::make('branch.name')
                    ->label(__('app.branch'))
                    ->badge()
                    ->color('info')
                    ->placeholder(__('branches.no_branch_selected'))
                    ->searchable(false)
                    ->sortable(false)
                    ->hidden(fn() => Core::weAreOnAdminPanel()),
                PhoneColumn::make('mobile')->label(__('users.mobile'))->displayFormat(PhoneInputNumberType::INTERNATIONAL),
                TextColumn::make('sex')->label(__('users.sex'))->badge()->searchable(false),
                TextColumn::make('last_login')->label(__('users.last_login'))->dateTime()->searchable(false),
                ToggleColumn::make('is_active')->label(__('users.is_active'))->searchable(false),
            ]
        )->filters(
            [
                SelectFilter::make('roles')
                    ->label(__('app.roles'))
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => __("roles.roles.{$record->name}"))
                    ->indicateUsing(fn($state) => $state['value'] ? __('roles.roles.' . Role::find($state['value'])?->name) : null),
                SelectFilter::make('tenants')
                    ->hidden(fn() => Core::weAreOnTenantPanel())
                    ->label(__('app.tenants'))
                    ->relationship('tenants', 'name'),
                Core::branchFilter('branch')
                    ->hidden(fn() => Core::weAreOnAdminPanel()),
                // AdvancedFilter::make()
                //     ->columnSpanFull(),
            ],
            FiltersLayout::Modal
        )
            ->actions([
                Impersonate::make()
                    ->visible(app()->environment('local'))
                    ->redirectTo(function (User $record) {
                        if ($record->isTenantEmployee()) {
                            return '/tenant';
                        }
                    }),
                ActionGroup::make([
                    RegisterExpenseAction::makeTableAction(),

                    Action::make('view_ratings')
                        ->infolist(function (User $record) {
                            return [
                                RatingEntry::make('avg_rating')
                                    ->label(__('subscribers.avg_rating'))
                                    ->default(fn() => $record->avg_rating),
                            ];
                        })
                        ->size(ActionSize::ExtraLarge)
                        ->modalSubmitActionLabel(__('Yep'))
                        ->icon('tabler-eye')
                        ->label(__('app.view_ratings'))
                        ->color('indigo'),
                    RateAction::make('rate')
                        ->visible(fn($record) => Auth::id() != $record->id),
                    Action::make('qr-code')
                        ->label(__('users.his_qr_code'))
                        ->icon('tabler-qrcode')
                        ->modal()
                        ->modalHeading(fn(User $record) => __('users.employee_badge') . ' - ' . $record->name)
                        ->modalFooterActions(function (User $record) {
                            return [
                                Action::make('download_employee_badge')
                                    ->label(__('users.download_employee_badge'))
                                    ->icon('tabler-download')
                                    ->color('success')
                                    ->action(fn() => MPdfService::downloadEmployeeBadge($record)),
                            ];
                        })
                        ->color(Color::Cyan)
                        ->modalContent(function ($record) {
                            $user = $record;
                            $data = json_encode(['employee_id' => $user?->id]);
                            $renderer = new ImageRenderer(
                                new RendererStyle(400),
                                new SvgImageBackEnd
                            );
                            $writer = new Writer($renderer);
                            $svg = $writer->writeString($data);

                            $qrCode = 'data:image/svg+xml;base64,' . base64_encode($svg);

                            return view('filament.tenant.components.qr-code', [
                                'qrCodeUrl' => $qrCode,
                                'client' => $user,
                            ]);
                        }),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    DeleteAction::make()
                        ->visible(function (User $record) {
                            return $record->id != Auth::id();
                        })
                        ->action(function (User $record) {
                            $record->tenants()->detach(tenant('id'));
                        }),
                ]),

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($livewire) {
                            $records = $livewire->getSelectedTableRecords();
                            foreach ($records as $record) {
                                /** @var User $record */
                                if ($record->tenants()->count() > 1) {
                                    $record->tenants()->detach(tenant('id'));
                                } else {
                                    $record->delete();
                                }
                            }
                        }),
                    ForceDeleteBulkAction::make()->visible($table->getModel()::isUsingSoftDelete()),
                    RestoreBulkAction::make()->visible($table->getModel()::isUsingSoftDelete()),
                    BulkAction::make('deactivate')
                        ->label(__('users.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (array $data, $livewire) {
                            $selected = $livewire->selectedTableRecords;
                            foreach ($selected as $user) {
                                /** @var User $empl */
                                $empl = User::find($user);
                                if ($empl->is_active) {
                                    $empl->is_active = false;
                                    $empl->save();
                                }
                            }
                            Notification::make('done_succuss')
                                ->title(__('app.done_succuss'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('activate')
                        ->label(__('users.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->color('success')
                        ->action(function (array $data, $livewire) {
                            $selected = $livewire->selectedTableRecords;
                            foreach ($selected as $user) {
                                /** @var User $empl */
                                $empl = User::find($user);
                                if (! $empl->is_active) {
                                    $empl->is_active = true;
                                    $empl->save();
                                }
                            }
                            Notification::make('done_succuss')
                                ->title(__('app.done_succuss'))
                                ->success()
                                ->send();
                        }),
                    static::getNotificationSelectionAction(
                        RecipientType::EMPLOYEES->value,
                        null,
                        __('notifications.select_employees_for_notification')
                    ),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                // No additional relationships to load
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ExpensesRelationManager::class,
        ];
    }
}


