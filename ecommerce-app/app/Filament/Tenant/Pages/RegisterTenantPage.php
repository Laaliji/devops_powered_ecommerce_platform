<?php

namespace App\Filament\Tenant\Pages;

use App\Classes\Core;
use App\Enums\TenantType;
use App\Models\Tenant;
use App\Models\User;
use Awcodes\PresetColorPicker\PresetColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\DB;

class RegisterTenantPage extends RegisterTenant
{

    public static function getLabel(): string
    {
        return __('app.register_tenant');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->live(debounce: 500)
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('slug', str($state)->slug());
                    })
                    ->label(__('tenants.name')),
                TextInput::make('slug')
                    ->required()
                    ->label(__('tenants.slug')),
                PresetColorPicker::make('primary_color')
                    ->colors(
                        \App\Classes\Core::Colors()
                    )
                    ->label(__('tenants.primary_color')),
                Select::make('type')
                    ->required()
                    ->options(TenantType::class)
                    ->label(__('tenants.type')),
                Select::make('admin')
                    ->required()
                    ->hidden(fn () => Core::weAreOnTenantPanel())
                    ->options(fn () => User::role(['tenant-admin'])->pluck('name', 'id'))
                    ->label(__('tenants.admin')),
            ]);
    }

    protected function handleRegistration(array $data): Tenant
    {
        unset($data['admin']);
        $tenant = Tenant::create($data);
        // $tenant->users()->attach(user());
        DB::table('tenant_user')->insert([
            'tenant_id' => $tenant->id,
            'user_id' => user()?->id,
        ]);

        return $tenant;
    }
}
