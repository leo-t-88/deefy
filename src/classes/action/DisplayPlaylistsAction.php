<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;

class DisplayPlaylistsAction extends Action {
    public function execute() : string {
        session_start();
        
        $allPlaylists = "";
        foreach ($_SESSION as $key => $value) {
            $playlist = unserialize($value);
            $allPlaylists .= (new AudioListRenderer($playlist))->render();
        }
        return $allPlaylists;
    }
}