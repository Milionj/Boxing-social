<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\SearchService;

final class SearchController
{
    private SearchService $search;
    private User $users;

    public function __construct()
    {
        $this->search = new SearchService();
        $this->users = new User();
    }

    private function requireAuth(Response $response): ?int
    {
        $id = $_SESSION['user']['id'] ?? null;

        if (!is_int($id)) {
            $response->redirect('/login');
            return null;
        }

        return $id;
    }

    public function index(Request $request, Response $response): void
    {
        if ($this->requireAuth($response) === null) {
            return;
        }

        $query = trim((string) $request->input('q', ''));
        $users = [];
        $posts = [];

        if ($query !== '') {
            $users = $this->search->searchUsers($query);
            $posts = $this->search->searchPosts($query);
        }

        require dirname(__DIR__, 2) . '/templates/search/index.php';
    }

    public function usernames(Request $request, Response $response): void
    {
        if ($this->requireAuth($response) === null) {
            return;
        }

        $query = trim((string) $request->input('q', ''));
        if (strlen($query) < 2) {
            $response->json(['items' => []]);
            return;
        }

        $matches = $this->users->searchByUsername($query, 8);
        $items = array_map(
            static fn(array $user): array => [
                'username' => (string) $user['username'],
                'url' => '/user?username=' . rawurlencode((string) $user['username']),
            ],
            $matches
        );

        $response->json(['items' => $items]);
    }
}
