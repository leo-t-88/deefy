<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;
use iutnc\deefy\repository\DeefyRepository;

// Action qui permet de gérer un formulaire de créeation d'une playlist
class AddPlaylistAction extends Action {
    public function execute() : string {
        if ($this->http_method === 'GET'){
        //if ($_SERVER['REQUEST_METHOD'] === 'GET'){
            return <<< HTML
            <h1>Ajouter une playlist</h1>
                <form method="post" action="?action=add-playlist">
                    <label for="nomp">Nom de la playlist :</label>
                    <input type="text" id="nomp" name="nomp" placeholder="Nom" required>
                    <br>
                    <input type="submit" value="Ajouter">
                </form>
            HTML;
        } else {
            session_start();

            if (!isset($_SESSION['user'])) return "<p>Vous devez être connecté pour crée une playlist.</p>";

            $repo = DeefyRepository::getInstance();

            $playlist = $repo->saveEmptyPlaylist(
                new Playlist((filter_var($_POST['nomp'], FILTER_SANITIZE_SPECIAL_CHARS)), [])
            );
            $repo->linkUserPlaylist($_SESSION['user'], $playlist->id);

            $_SESSION['playlist'] = serialize($playlist);
            return (new AudioListRenderer($playlist))->render(Renderer::LONG);
        }
    }
}