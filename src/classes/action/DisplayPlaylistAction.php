<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\Authz;

class DisplayPlaylistAction extends Action {
    public function execute() : string {
        session_start();

        $id = $_GET['id'] ?? null;
        if (!$id || !is_numeric($id)) return "<p>Playlist non spécifiée ou invalide.</p>";
        $id = (int)$id;

        try {
            Authz::checkPlaylistOwner($id);

            $playlist = DeefyRepository::getInstance()->findPlaylistById($id);

            if (!$playlist) return "<p>Playlist introuvable.</p>";

            return (new AudioListRenderer($playlist))->render();
            
        } catch (\Exception $e) {
            return "<p>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
