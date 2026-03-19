<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\SportsDataService;
use Throwable;

final class SportsController
{
    private SportsDataService $sportsData;

    public function __construct()
    {
        $this->sportsData = new SportsDataService();
    }

    private function requireAuth(Response $response, ?Request $request = null): ?int
    {
        $id = $_SESSION['user']['id'] ?? null;

        if (!is_int($id)) {
            if ($request?->expectsJson()) {
                $response->json([
                    'ok' => false,
                    'message' => 'Connexion requise.',
                ], 401);
                return null;
            }

            $response->redirect('/login');
            return null;
        }

        return $id;
    }

    public function mmaSchedule(Request $request, Response $response): void
    {
        if ($this->requireAuth($response, $request) === null) {
            return;
        }

        $season = (int) $request->input('season', date('Y'));
        if ($season < 2000 || $season > 2100) {
            $season = (int) date('Y');
        }

        if (!$this->sportsData->isConfigured()) {
            $response->json([
                'ok' => true,
                'configured' => false,
                'season' => $season,
                'events' => [],
            ]);
            return;
        }

        try {
            $schedule = $this->sportsData->fetchMmaSchedule($season);

            $response->json([
                'ok' => true,
                'configured' => true,
                'requestedSeason' => $season,
                'season' => (int) ($schedule['season'] ?? $season),
                'events' => $schedule['events'] ?? [],
                'fetchedAt' => date(DATE_ATOM),
            ]);
        } catch (Throwable $e) {
            if (($_ENV['APP_DEBUG'] ?? '0') === '1') {
                error_log($e->getMessage());
            }

            $response->json([
                'ok' => false,
                'configured' => true,
                'season' => $season,
                'events' => [],
                'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Sports data unavailable.',
            ], 503);
        }
    }

    public function mmaEvent(Request $request, Response $response): void
    {
        if ($this->requireAuth($response, $request) === null) {
            return;
        }

        $eventId = trim((string) $request->input('event_id', ''));
        if ($eventId === '') {
            $response->json([
                'ok' => false,
                'message' => 'Event id missing.',
            ], 422);
            return;
        }

        if (!$this->sportsData->isConfigured()) {
            $response->json([
                'ok' => true,
                'configured' => false,
                'event' => null,
            ]);
            return;
        }

        try {
            $event = $this->sportsData->fetchMmaEvent($eventId);

            $response->json([
                'ok' => true,
                'configured' => true,
                'event' => $event,
                'fetchedAt' => date(DATE_ATOM),
            ]);
        } catch (Throwable $e) {
            if (($_ENV['APP_DEBUG'] ?? '0') === '1') {
                error_log($e->getMessage());
            }

            $response->json([
                'ok' => false,
                'configured' => true,
                'event' => null,
                'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Sports data unavailable.',
            ], 503);
        }
    }
}
