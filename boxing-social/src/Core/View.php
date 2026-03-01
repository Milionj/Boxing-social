<?php
declare(strict_types=1);

namespace App\Core;

final class View{

    /**
     * Affiche un template en lui injectant des variables.
     *
     * @param string $template Nom du fichier dans /templates (sans .php)
     * @param array  $data     Données à rendre disponibles dans le template
     */
    public static function render(string $template, array $data = []): void
    {
        $file = dirname(__DIR__, 2) . '/templates/' . $template . '.php';
        // Sécurité : si le fichier n'existe pas, on renvoie une erreur serveur
        if (!is_file($file)) {
            http_response_code(500);
            echo 'Template introuvable';
            return;
        }


        // Transforme le tableau $data en variables PHP utilisables dans le template.
        // Exemple : ['title' => 'Accueil'] -> $title = 'Accueil'
        // EXTR_SKIP évite d'écraser des variables existantes.
        extract($data, EXTR_SKIP);
        require $file;
    }
}
