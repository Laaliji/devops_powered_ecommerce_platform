<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RecipientType: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case EMPLOYEES = 'employees';
    case CLIENTS = 'clients';

    public function getLabel(): string
    {
        return match ($this) {
            self::EMPLOYEES => __('notifications.form.employees'),
            self::CLIENTS => __('notifications.form.clients'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EMPLOYEES => 'tabler-users',
            self::CLIENTS => 'tabler-user-circle',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EMPLOYEES => 'success',
            self::CLIENTS => 'warning',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::EMPLOYEES => __('notifications.form.employees_description'),
            self::CLIENTS => __('notifications.form.clients_description'),
        };
    }
}
