<?php

namespace App\Traits;

use App\Enums\RecipientType;
use App\Filament\Tenant\Resources\ClientAnnouncementResource;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

trait HasNotificationSelectionAction
{
    public static function getNotificationSelectionAction(
        string $recipientType,
        ?string $courseId = null,
        ?string $actionLabel = null
    ): BulkAction {
        return BulkAction::make('select_for_notification')
            ->label($actionLabel ?? __('notifications.select_for_notification'))
            ->icon('tabler-mail')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading(__('notifications.select_for_notification_modal_title'))
            ->modalDescription(__('notifications.select_for_notification_modal_description'))
            ->action(function (Collection $records, $livewire) use ($recipientType, $courseId) {
                $ids = self::getUsersIds($records);

                $params = [
                    'recipient_types' => [$recipientType],
                ];

                if ($recipientType === RecipientType::CLIENTS->value) {
                    $params['specific_clients'] = json_encode($ids);
                } elseif ($recipientType === RecipientType::EMPLOYEES->value) {
                    $params['specific_employees'] = json_encode($ids);
                }

                if ($courseId) {
                    $params['course_id'] = $courseId;
                }
            })
            ->visible(fn() => user()->can('send_message_to_all_clients'));
    }

    public static function getUsersIds(Collection $records): array
    {
        return $records->pluck('id')->toArray();
    }
}
