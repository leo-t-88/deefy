<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthnException;

class AddPodcastTrackAction extends Action {
    public function execute() : string {
        session_start();

        if ($this->http_method === 'GET'){
            $playlists = DeefyRepository::getInstance()->findAllPlaylists();

            $options = '';
            foreach ($playlists as $p) {
                try {
                    Authz::checkPlaylistOwner($p->id);
                    $label = htmlspecialchars($p->nom, ENT_QUOTES);
                    $val = $p->id;
                    $options .= "<option value=\"$val\">$label</option>";
                } catch (AuthnException $e) {}
            }

            return <<< HTML
            <h1>Ajouter une piste</h1>
                <form method="post" action="?action=add-track" enctype="multipart/form-data">
                    <label for="idp">Playlist :</label>
                    <select id="idp" name="idp" required>
                        $options
                    </select>
                    <br>
                    <label for="titre">Titre :</label>
                    <input type="text" id="titre" name="titre" placeholder="Titre" required>
                    <br>
                    <label for="auteur">Auteur :</label>
                    <input type="text" id="auteur" name="auteur" placeholder="Nom de(s) auteur(s)" required>
                    <br>
                    <label for="userfile">Chemin du fichier :</label>
                    <input type="file" id="userfile" name="userfile" accept=".mp3" required>
                    <br>
                    <input type="submit" value="Ajouter à la playlist">
                </form>
            HTML;
        } else {
            if (!isset($_SESSION['user'])) return "<p>Vous devez être connecté pour crée une playlist.</p>";

            if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] !== UPLOAD_ERR_OK) return 'Erreur upload du fichier.' . $this->execute();

            $file = $_FILES['userfile'];
            $type = $file['type'];

            if (strtolower(substr($file['name'], -4)) !== '.mp3' || $type !== 'audio/mpeg') return 'Seuls les fichiers MP3 sont acceptés.' . $this->execute();

            // répertoire cible
            $audioRoot = dirname(__DIR__, 3) . '/audio';
            if (!is_dir($audioRoot)) mkdir($audioRoot, 0777, true);

            $filename = uniqid('track_', true) . '.mp3';

            if (!move_uploaded_file($file['tmp_name'], $audioRoot . '/' . $filename)) return 'Impossible de sauvegarder le fichier.';

            // Lecture éventuelle des métadonnées
            $titre = filter_var($_POST['titre'], FILTER_SANITIZE_SPECIAL_CHARS);
            $auteur = filter_var($_POST['auteur'], FILTER_SANITIZE_SPECIAL_CHARS);

            $info = (new \getID3)->analyze($audioRoot . '/' . $filename);
            if (isset($info['playtime_seconds'])) $duree = (int)$info['playtime_seconds'];

            if ($titre === '' || $auteur === '') {
                if ($titre === '') $titre = $info['tags']['id3v2']['titre'][0] ?? pathinfo($file['name'], PATHINFO_FILENAME);
                if ($auteur === '') $auteur = $info['tags']['id3v2']['artist'][0] ?? 'Inconnu';
            }

            $repo = DeefyRepository::getInstance();

            $track = $repo->savePodcastTrack(new PodcastTrack(0, $titre, "", $duree ?? 0, $filename, $auteur, ""));
            $repo->linkPlaylistTrack((int)$_POST['idp'], $track->id);

            $playlist = $repo->findPlaylistById((int)$_POST['idp']);
            $playlist->addTrack($track);
            $_SESSION['playlist'] = serialize($playlist);

            return (new AudioListRenderer($playlist))->render(Renderer::LONG);
        }
    }
}