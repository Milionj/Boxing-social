<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class SportsDataService
{
    private const MMA_BASE_URL = 'https://v1.mma.api-sports.io/';
    private const MMA_FIGHTS_ENDPOINT = 'fights';
    private const FREE_PLAN_FALLBACK_SEASON = 2024;

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    public function fetchMmaSchedule(int $season): array
    {
        try {
            $payload = $this->requestJson([
                'season' => (string) $season,
            ]);

            $seasonItems = $this->unwrapResponse($payload);
            if ($seasonItems !== []) {
                return [
                    'season' => $season,
                    'events' => $this->normalizeFightList($seasonItems, 'asc'),
                ];
            }
        } catch (RuntimeException $e) {
            if (!$this->isSeasonPlanRestriction($e) || $season === self::FREE_PLAN_FALLBACK_SEASON) {
                throw $e;
            }
        }

        $fallbackPayload = $this->requestJson([
            'season' => (string) self::FREE_PLAN_FALLBACK_SEASON,
        ]);

        return [
            'season' => self::FREE_PLAN_FALLBACK_SEASON,
            'events' => $this->normalizeFightList($this->unwrapResponse($fallbackPayload), 'desc'),
        ];
    }

    public function fetchMmaEvent(string $eventId): array
    {
        $eventId = trim($eventId);
        if ($eventId === '') {
            throw new RuntimeException('Fight id missing.');
        }

        $payload = $this->requestJson([
            'id' => $eventId,
        ]);

        $items = $this->unwrapResponse($payload);
        $item = $items[0] ?? null;
        if (!is_array($item)) {
            throw new RuntimeException('Sports API returned an empty fight payload.');
        }

        return $this->normalizeFight($item);
    }

    private function requestJson(array $query): array
    {
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            throw new RuntimeException('SPORTS_DATA_API_KEY missing.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('The cURL extension is required to query the MMA API.');
        }

        $url = self::MMA_BASE_URL . self::MMA_FIGHTS_ENDPOINT;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'x-apisports-key: ' . $apiKey,
            ],
        ]);

        $result = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false || $curlError !== '') {
            throw new RuntimeException('Unable to reach the MMA API: ' . $curlError);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException('MMA API returned HTTP ' . $statusCode . '.');
        }

        $payload = json_decode($result, true);
        if (!is_array($payload)) {
            throw new RuntimeException('MMA API returned an invalid JSON payload.');
        }

        $this->assertNoApiError($payload);

        return $payload;
    }

    private function assertNoApiError(array $payload): void
    {
        $errors = $payload['errors'] ?? null;
        if (!is_array($errors) || $errors === []) {
            return;
        }

        $messages = [];
        foreach ($errors as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $message = trim((string) $value);
            if ($message !== '') {
                $messages[] = $message;
            }
        }

        $errorText = implode(' | ', $messages);
        if ($errorText === '') {
            throw new RuntimeException('L’API MMA a renvoyé une erreur.');
        }

        if (stripos($errorText, 'application key') !== false || stripos($errorText, 'token') !== false) {
            throw new RuntimeException('La clé API MMA est invalide ou non reconnue par API-SPORTS.');
        }

        throw new RuntimeException('L’API MMA a renvoyé une erreur : ' . $errorText);
    }

    private function unwrapResponse(array $payload): array
    {
        $response = $payload['response'] ?? null;
        if (is_array($response)) {
            return array_values(array_filter($response, static fn($item): bool => is_array($item)));
        }

        if (array_is_list($payload)) {
            return array_values(array_filter($payload, static fn($item): bool => is_array($item)));
        }

        return [];
    }

    private function normalizeFightList(array $items, string $direction = 'asc'): array
    {
        $fights = [];
        $seen = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $fight = $this->normalizeFight($item);
            $fightId = (string) ($fight['id'] ?? '');
            if ($fightId !== '' && isset($seen[$fightId])) {
                continue;
            }

            if ($fightId !== '') {
                $seen[$fightId] = true;
            }

            $fights[] = $fight;
        }

        usort($fights, static function (array $left, array $right) use ($direction): int {
            $leftTs = (int) ($left['timestamp'] ?? 0);
            $rightTs = (int) ($right['timestamp'] ?? 0);

            if ($leftTs > 0 && $rightTs > 0) {
                return $direction === 'desc'
                    ? $rightTs <=> $leftTs
                    : $leftTs <=> $rightTs;
            }

            return $direction === 'desc'
                ? strcmp((string) ($right['title'] ?? ''), (string) ($left['title'] ?? ''))
                : strcmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
        });

        return array_slice($fights, 0, 24);
    }

    private function normalizeFight(array $item): array
    {
        $fighterOne = $this->valueAsString($item, [
            ['fighters', 'home', 'name'],
            ['fighters', 'red', 'name'],
            ['fighters', 'first', 'name'],
            ['fighters', 'fighter1', 'name'],
            ['fighter_1'],
        ]);
        $fighterTwo = $this->valueAsString($item, [
            ['fighters', 'away', 'name'],
            ['fighters', 'blue', 'name'],
            ['fighters', 'second', 'name'],
            ['fighters', 'fighter2', 'name'],
            ['fighter_2'],
        ]);

        $title = trim($fighterOne . ($fighterOne !== '' || $fighterTwo !== '' ? ' vs ' : '') . $fighterTwo);
        if ($title === '' || $title === 'vs') {
            $title = $this->valueAsString($item, [
                ['title'],
                ['name'],
                ['slug'],
            ]);
        }
        if ($title === '') {
            $title = 'MMA fight';
        }

        $timestamp = $this->valueAsInt($item, [
            ['timestamp'],
            ['time', 'timestamp'],
        ]);

        $dateRaw = $this->valueAsString($item, [
            ['date'],
            ['time'],
            ['datetime'],
        ]);

        $status = $this->valueAsString($item, [
            ['status', 'long'],
            ['status', 'short'],
            ['status'],
        ]);

        $promotion = $this->valueAsString($item, [
            ['league', 'name'],
            ['category', 'name'],
            ['category'],
            ['slug'],
        ]);

        $venue = implode(', ', array_filter([
            $this->valueAsString($item, [['venue', 'name']]),
            $this->valueAsString($item, [['venue', 'city']]),
            $this->valueAsString($item, [['country', 'name']]),
        ]));

        $details = implode(' • ', array_filter([
            $this->valueAsString($item, [['card']]),
            $this->valueAsString($item, [['weight', 'category']]),
            $this->valueAsString($item, [['rounds']]),
            $this->valueAsString($item, [['referee']]),
        ]));

        return [
            'id' => $this->valueAsString($item, [['id']]) ?: sha1($title . '|' . $dateRaw),
            'title' => $title,
            'dateRaw' => $dateRaw,
            'dateLabel' => $this->formatDateLabel($dateRaw, $timestamp),
            'timestamp' => $timestamp,
            'status' => $status,
            'venue' => $venue,
            'headline' => $promotion,
            'promotion' => $promotion,
            'details' => $details,
            'fights' => [],
        ];
    }

    private function valueAsString(array $item, array $paths): string
    {
        foreach ($paths as $path) {
            $value = $this->valueAtPath($item, $path);
            if (is_scalar($value)) {
                $value = trim((string) $value);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    private function valueAsInt(array $item, array $paths): int
    {
        foreach ($paths as $path) {
            $value = $this->valueAtPath($item, $path);
            if (is_int($value)) {
                return $value;
            }

            if (is_string($value) && ctype_digit($value)) {
                return (int) $value;
            }
        }

        return 0;
    }

    private function valueAtPath(array $item, array $path): mixed
    {
        $current = $item;

        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    private function formatDateLabel(string $dateRaw, int $timestamp): string
    {
        if ($timestamp > 0) {
            return date('d/m/Y H:i', $timestamp);
        }

        if ($dateRaw === '') {
            return '';
        }

        $parsed = strtotime($dateRaw);
        if ($parsed === false) {
            return $dateRaw;
        }

        return date('d/m/Y H:i', $parsed);
    }

    private function apiKey(): string
    {
        return trim((string) ($_ENV['SPORTS_DATA_API_KEY'] ?? ''));
    }

    private function isSeasonPlanRestriction(RuntimeException $e): bool
    {
        $message = $e->getMessage();

        return stripos($message, 'Free plans do not have access to this season') !== false;
    }
}
