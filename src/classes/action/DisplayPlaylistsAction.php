<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\Authz;
 use iutnc\deefy\exception\AuthnException;

class DisplayPlaylistsAction extends Action {
    public function execute() : string {
        session_start();

        if (!isset($_SESSION['user'])) return "<p>Vous n'êtes pas connecté.</p>";
        
        $playlists = DeefyRepository::getInstance()->findAllPlaylists();

        $playlistUser = "\t<ul>\n";
        foreach ($playlists as $p) {
            try {
                Authz::checkPlaylistOwner($p->id);
                $nom = htmlspecialchars($p->nom, ENT_QUOTES);
                $playlistUser .= "\t\t<li><a href='?action=display-playlist&id={$p->id}'>$nom</a></li>\n";
            } catch (AuthnException $e) {}
        }

        return $playlistUser . "\t</ul>";
    }
}