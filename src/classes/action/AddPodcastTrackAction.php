<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;

class AddPodcastTrackAction extends Action {
    public function execute() : string {
        if ($this->http_method === 'GET'){
            return <<< HTML
            <h1>Ajouter une piste</h1>
                <form method="post" action="?action=add-track" enctype="multipart/form-data">
                    <label for="nomp">Playlist :</label>
                    <input type="text" id="nomp" name="nomp" placeholder="Nom de la playlist">
                    <br>
                    <label for="titre">Titre :</label>
                    <input type="text" id="titre" name="titre" placeholder="Titre" required>
                    <br>
                    <label for="auteur">Auteur :</label>
                    <input type="text" id="auteur" name="auteur" placeholder="Nom de(s) auteur(s)" required>
                    <br>
                    <label for="userfile">Chemin du fichier :</label>
                    <input type="file" id="userfile" name="userfile" required>
                    <br>
                    <input type="submit" value="Ajouter à la playlist">
                </form>
            HTML;
        } else {
            session_start();

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
            $nomp = filter_var($_POST['nomp'], FILTER_SANITIZE_SPECIAL_CHARS);

            if ($_POST['nomp'] === "") $nomp = "Favorites";

            $info = (new \getID3)->analyze($audioRoot . '/' . $filename);
            if (isset($info['playtime_seconds'])) $duree = (int)$info['playtime_seconds'];

            if ($titre === '' || $auteur === '') {
                if ($titre === '') $titre = $info['tags']['id3v2']['titre'][0] ?? pathinfo($file['name'], PATHINFO_FILENAME);
                if ($auteur === '') $auteur = $info['tags']['id3v2']['artist'][0] ?? 'Inconnu';
            }

            $track = new PodcastTrack(0, $titre, "", $duree ?? 0, $filename, $auteur, "");

            if (!isset($_SESSION['playlist'][$nomp])) $_SESSION['playlist'][$nomp] = serialize(new Playlist($nomp, []));

            $playlist = unserialize($_SESSION['playlist'][$nomp]);
            $playlist->addTrack($track);
            $_SESSION['playlist'][$nomp] = serialize($playlist);

            return (new AudioListRenderer($playlist))->render(Renderer::LONG);

            /*
            $path = filter_input(INPUT_POST, 'patht', FILTER_SANITIZE_URL);
            if (!preg_match('/\.(mp3|ogg|wav|flac)$/i', $path)) return "Erreur : le fichier doit être un fichier .mp3, .ogg, .wav ou .flac";
            
            $nomp = filter_var($_POST['nomp'], FILTER_SANITIZE_SPECIAL_CHARS);
            // Si aucun nom alors favoris
            if ($_POST['nomp'] === "") $nomp = "Favorites";

            // Si la playlist n'existe pas, on la crée
            if (!isset($_SESSION['playlist' . $nomp])) $_SESSION['playlist' . $nomp] = serialize(new Playlist($nomp, []));

            $piste = new AudioTrack((filter_var($_POST['nomt'], FILTER_SANITIZE_SPECIAL_CHARS)), $path);
            $playlist = unserialize($_SESSION['playlist' . $nomp]);
            $playlist->addTrack($piste);
            $_SESSION['playlist' . $nomp] = serialize($playlist);
            return (new AudioListRenderer($playlist))->render(Renderer::LONG);*/
        }
    }
}