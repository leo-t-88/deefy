<?php
declare(strict_types=1);

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action;

class Dispatcher {
    private string $action;

    public function __construct(string $action) {
        $this->action = $action;
    }

    // Execute un Action en fonction du paramètre query dans l'url
    public function run() : void {
        switch ($this->action) {
            case 'display-playlist':
                $html = (new action\DisplayPlaylistAction())->execute();
                break;
            case 'list-playlists':
                $html = (new action\DisplayPlaylistsAction())->execute();
                break;
            case 'add-playlist':
                $html = (new action\AddPlaylistAction())->execute();
                break;
            case 'add-track':
                $html = (new action\AddPodcastTrackAction())->execute();
                break;
            case 'signin':
                $html = (new action\SigninAction())->execute();
                break;
            case 'signup':
                $html = (new action\SignupAction())->execute();
                break;
            default:
                $html = (new action\DefaultAction())->execute();
                break;
        }
        $this->renderPage($html);
    }

    // Affiche le contenu HTML correspondant
    private function renderPage(string $html): void{
        echo <<<HTML
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Deefy</title>
            <link rel='stylesheet' href='src/css/style.css'>
        </head>
        <body>
            <div class="menu">
                <input type="checkbox" id="menu-toggle">
                <label for="menu-toggle" class="hamburger">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <path class="line1" d="M0 40h62c13 0 6 28-4 18L35 35"></path>
                        <path class="line2" d="M0 50h70"></path>
                        <path class="line3" d="M0 60h62c13 0 6-28-4-18L35 65"></path>
                    </svg>
                </label>

                <nav id="menu-bar">
                    <ul>
                        <li><a href='?action=defaut'>Accueil</a></li>
                        <li><a href='?action=list-playlists'>Mes playlists</a></li>
                        <li><a href='?action=add-playlist'>Ajouter une playlist</a></li>
                        <li><a href='https://webetu.iutnc.univ-lorraine.fr/www/e16795u/deefy/'>Lien WebEtu</a></li>
                        <li><a href='https://github.com/leo-t-88/deefy'>Code source</a></li>
                    </ul>
                    <div class="menu-buttons">
                        <a href='?action=signup'>S'inscrire</a>
                        <a href='?action=signin'>Se connecter</a>
                    </div>
                </nav>
            </div>
        $html
        </body>
        </html>
        HTML;
    }
}