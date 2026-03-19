<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class FirebaseContactService
{
    public function send(array $payload): void
    {
        $databaseUrl = rtrim((string) ($_ENV['FIREBASE_DATABASE_URL'] ?? ''), '/');
        $databaseSecret = (string) ($_ENV['FIREBASE_DATABASE_SECRET'] ?? '');

        if ($databaseUrl === '') {
            throw new RuntimeException('Configuration Firebase manquante: FIREBASE_DATABASE_URL');
        }

        // Le write passe par le backend pour éviter d'exposer une capacité
        // d'écriture directe au navigateur et pour laisser le rate limiting
        // décider qui peut réellement soumettre le formulaire.
        $url = $databaseUrl . '/contact_messages.json';
        if ($databaseSecret !== '') {
            $url .= '?auth=' . rawurlencode($databaseSecret);
        }

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            throw new RuntimeException('Impossible de serialiser le message de contact.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('L’extension cURL est requise pour envoyer le message vers Firebase.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FAILONERROR => false,
        ]);

        $result = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false || $curlError !== '') {
            throw new RuntimeException('Échec de connexion à Firebase : ' . $curlError);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException('Firebase a retourné le code HTTP ' . $statusCode . '.');
        }
    }
}
