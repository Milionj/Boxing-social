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
        $scope = (string) $request->input('scope', 'all');
        if (!in_array($scope, ['all', 'users', 'posts'], true)) {
            $scope = 'all';
        }

        $postTypeFilter = (string) $request->input('post_type', 'all');
        if (!in_array($postTypeFilter, ['all', 'publication', 'entrainement'], true)) {
            $postTypeFilter = 'all';
        }

        $usersPerPage = 8;
        $postsPerPage = 8;
        $usersPage = max(1, (int) $request->input('users_page', 1));
        $postsPage = max(1, (int) $request->input('posts_page', 1));

        $users = [];
        $posts = [];
        $usersCount = 0;
        $postsCount = 0;
        $usersTotalPages = 1;
        $postsTotalPages = 1;

        if ($query !== '') {
            $usersCount = $this->search->countUsers($query);
            $postsCount = $this->search->countPosts($query, $postTypeFilter);

            $usersTotalPages = max(1, (int) ceil($usersCount / $usersPerPage));
            $postsTotalPages = max(1, (int) ceil($postsCount / $postsPerPage));
            $usersPage = min($usersPage, $usersTotalPages);
            $postsPage = min($postsPage, $postsTotalPages);

            if ($scope !== 'posts') {
                $users = $this->search->searchUsers(
                    $query,
                    $usersPerPage,
                    ($usersPage - 1) * $usersPerPage
                );
            }

            if ($scope !== 'users') {
                $posts = $this->search->searchPosts(
                    $query,
                    $postTypeFilter,
                    $postsPerPage,
                    ($postsPage - 1) * $postsPerPage
                );
            }
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
