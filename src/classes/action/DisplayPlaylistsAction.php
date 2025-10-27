<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;

class DisplayPlaylistsAction extends Action {
    public function execute() : string {
        session_start();
        
        $allPlaylists = "";

        if (!isset($_SESSION['playlist']) || !is_array($_SESSION['playlist'])) return "<p>Aucune playlist trouvée.</p>";

        foreach ($_SESSION['playlist'] as $key => $value) {
            $playlist = unserialize($value);
            $allPlaylists .= (new AudioListRenderer($playlist))->render();
        }
        return $allPlaylists;
    }
}