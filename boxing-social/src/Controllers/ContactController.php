<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

final class ContactController
{
    public function show(Response $response): void
    {
        $success = $_SESSION['success_contact'] ?? '';
        $errors = $_SESSION['errors_contact'] ?? [];
        $old = $_SESSION['old_contact'] ?? [];

        unset($_SESSION['success_contact'], $_SESSION['errors_contact'], $_SESSION['old_contact']);

        require dirname(__DIR__, 2) . '/templates/contact.php';
    }

    public function submit(Request $request, Response $response): void
    {
        $_SESSION['errors_contact'] = ['Le formulaire contact utilise Firestore cote navigateur. Active JavaScript pour envoyer le message.'];
        $response->redirect('/contact');
    }
}
