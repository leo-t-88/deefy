<?php
declare(strict_types=1);

namespace iutnc\deefy\action;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class SigninAction extends Action {
    public function execute() : string {
        if ($this->http_method === 'GET'){
            return <<< HTML
                <div class="signform">
                    <h2>Se connecter</h2>
                    <form method="post" action="?action=signin">
                        <input type="email" id="emailu" name="emailu" placeholder="Email" required>
                        <br>
                        <input type="password" id="mdpu" name="mdpu" placeholder="Mot de passe" required>
                        <br>
                        <input type="submit" value="Se connecter">
                    </form>
                </div>
            HTML;
        } else { // POST
            session_start();
            try {
                $email = $_POST['emailu'];

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new AuthnException("Auth error : invalid email format");

                AuthnProvider::signin($email, $_POST['mdpu']);
                $_SESSION['user'] = $email;
                // Playlist => null, pour éviter un hack qui permetrait à un utilisateur de modifier une playlist d'un autre utilisateur s'il était connecté à ce compte en question avant
                $_SESSION['playlist'] = null;
                return "<p>Connexion réussie, bonjour $email !</p>";

            } catch (AuthnException $e) {
                return "<p>".$e->getMessage()."</p>";
            }
        }
    }
}