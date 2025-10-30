<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;

class DisplayPlaylistsAction extends Action {
    public function execute() : string {
        session_start();
        
        $allPlaylists = "";

        try{
            $mail = AuthnProvider::getSignedInUser();
            $id = DeefyRepository::getInstance()->getUserID($mail);

            $playlists = DeefyRepository::getInstance()->getPlaylistsUser($id);
            
            if (!$playlists) return "<p>Aucune playlist</p>";


            foreach($playlists as $pid){
                $playlist = DeefyRepository::getInstance()->findPlaylistById((int)$pid);
                if ($playlist) {
                    $allPlaylists .= (new AudioListRenderer($playlist))->render();
                }
            }
        } catch (\Exception $e) {
            return "<p>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        return $allPlaylists;
        
    }
}