<?php
declare(strict_types=1);

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action;

class Dispatcher {
    private string $action;

    public function __construct(string $action) {
        $this->action = $action;
    }

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
            <ul>
                <li><a href='?action=defaut'>Accueil</a></li>
                <li><a href='?action=list-playlists'>Mes playlists</a></li>
                <li><a href='?action=add-playlist'>Ajouter une playlist</a></li>
                <li><a href='?action=signin'>Se connecter</a></li>
                <li><a href='?action=signup'>S'inscrire</a></li>
            </ul>
            $html
        </body>
        </html>
        HTML;
    }
}