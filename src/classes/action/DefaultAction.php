<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

// Action par défaut (page d'accueil)
class DefaultAction extends Action {
    public function execute() : string {
        session_start();

        $html = "<h1>Deefy - Bienvenue</h1>";

        if (!isset($_SESSION['user'])){
            $html .= (new SigninAction())->execute();
        } else {
            $html .= (new DisplayPlaylistsAction())->execute();
        }

        return $html;
    }
}