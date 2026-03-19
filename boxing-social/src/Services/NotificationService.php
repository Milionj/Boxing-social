<?php
declare(strict_types=1);

namespace App\Services;

final class NotificationService
{
    /**
     * @param array<int, array<string, mixed>> $notifications
     * @return array<int, array<string, mixed>>
     */
    public function presentMany(array $notifications, Translator $translator): array
    {
        return array_map(
            fn(array $notification): array => $this->present($notification, $translator),
            $notifications
        );
    }

    /**
     * @param array<string, mixed> $notification
     * @return array<string, mixed>
     */
    public function present(array $notification, Translator $translator): array
    {
        $displayType = $this->resolveDisplayType($notification);

        $notification['display_type'] = $translator->text('notification_type_' . $displayType);
        $notification['display_content'] = $this->resolveDisplayContent($notification, $displayType, $translator);
        $notification['target_url'] = $this->resolveTargetUrl($notification, $displayType);
        $notification['open_url'] = $this->buildOpenUrl((int) ($notification['id'] ?? 0), (string) ($notification['target_url'] ?? ''));

        return $notification;
    }

    /**
     * @param array<string, mixed> $notification
     */
    public function resolveTargetUrl(array $notification, ?string $displayType = null): string
    {
        $resolvedType = $displayType ?? $this->resolveDisplayType($notification);
        $entityId = (int) ($notification['entity_id'] ?? 0);
        $actorUsername = (string) ($notification['actor_username'] ?? '');

        if (($resolvedType === 'like' || $resolvedType === 'comment') && $entityId > 0) {
            return '/post?id=' . $entityId;
        }

        if ($resolvedType === 'message' || $resolvedType === 'training_interest') {
            return $actorUsername !== '' ? '/messages?username=' . rawurlencode($actorUsername) : '/messages';
        }

        if ($resolvedType === 'friend_request' || $resolvedType === 'friend_accept') {
            return $actorUsername !== '' ? '/user?username=' . rawurlencode($actorUsername) : '/friends';
        }

        return '/notifications';
    }

    /**
     * @param array<string, mixed> $notification
     */
    private function resolveDisplayType(array $notification): string
    {
        $type = (string) ($notification['type'] ?? '');
        $content = (string) ($notification['content'] ?? '');

        if ($type === 'message' && $this->isTrainingInterestContent($content)) {
            return 'training_interest';
        }

        return in_array($type, ['like', 'comment', 'friend_request', 'friend_accept', 'message', 'training_interest'], true)
            ? $type
            : 'generic';
    }

    /**
     * @param array<string, mixed> $notification
     */
    private function resolveDisplayContent(array $notification, string $displayType, Translator $translator): string
    {
        $storedContent = trim((string) ($notification['content'] ?? ''));

        return match ($displayType) {
            'like' => $translator->text('notification_content_like'),
            'comment' => $translator->text('notification_content_comment'),
            'friend_request' => $translator->text('notification_content_friend_request'),
            'friend_accept' => $translator->text('notification_content_friend_accept'),
            'message' => $translator->text('notification_content_message'),
            'training_interest' => $translator->text('notification_content_training_interest'),
            default => $storedContent !== '' ? $storedContent : $translator->text('notification_content_generic'),
        };
    }

    private function isTrainingInterestContent(string $content): bool
    {
        $normalized = $this->lower(trim($content));

        if ($normalized === '') {
            return false;
        }

        return (str_contains($normalized, 'intérêt') && str_contains($normalized, 'séance'))
            || (str_contains($normalized, 'interest') && (str_contains($normalized, 'session') || str_contains($normalized, 'training')));
    }

    private function lower(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
    }

    private function buildOpenUrl(int $notificationId, string $fallback): string
    {
        if ($notificationId <= 0) {
            return $fallback !== '' ? $fallback : '/notifications';
        }

        return '/notifications/open?id=' . $notificationId;
    }
}
