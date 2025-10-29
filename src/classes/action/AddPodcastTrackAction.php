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
        if ($this->http_method === 'GET'){
            return <<< HTML
                <h3>Ajouter une piste</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="text" name="titre" placeholder="Titre" required>
                    <input type="text" name="auteur" placeholder="Auteur(e)(s)" required>
                    <input type="file" name="userfile" accept=".mp3" required>
                    <br>
                    <input type="submit" value="Ajouter à la playlist">
                </form>
            HTML;
        } else {
            session_start();

            if (!isset($_SESSION['user'])) return "<p>Vous devez être connecté pour ajouter une piste.</p>";

            if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] !== UPLOAD_ERR_OK) return 'Erreur upload du fichier.';

            $file = $_FILES['userfile'];
            $type = $file['type'];

            if (strtolower(substr($file['name'], -4)) !== '.mp3' || $type !== 'audio/mpeg') return 'Seuls les fichiers MP3 sont acceptés.';

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

            if (!isset($_SESSION['playlist'])) return "<p>Aucune playlist courrante. Veuillez d'abord sélectionner ou créer une playlist.</p>";

            $track = $repo->savePodcastTrack(new PodcastTrack(0, $titre, "", $duree ?? 0, $filename, $auteur, ""));
            $repo->linkPlaylistTrack($_SESSION['playlist']->id, $track->id);

            $playlist = $repo->findPlaylistById($_SESSION['playlist']->id);
            $_SESSION['playlist'] = serialize($playlist);

            return "<p>Piste ajoutée avec succès !</p>";
        }
    }
}