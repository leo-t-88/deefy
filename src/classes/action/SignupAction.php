<?php
declare(strict_types=1);

namespace iutnc\deefy\action;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

// Action d'inscription demande l'email et le mot de passe x2 si inscription OK alors email stocké en Session
class SignupAction extends Action {
    public function execute() : string {
        if ($this->http_method === 'GET'){
            return <<< HTML
                <div class="signform">
                    <h2>Crée un compte</h2>
                    <form method="post" action="?action=signup">
                        <input type="email" id="emailu" name="emailu" placeholder="Email" required>
                        <br>
                        <input type="password" id="mdpu" name="mdpu" placeholder="Mot de passe" required>
                        <br>
                        <input type="password" id="mdp2u" name="mdp2u" placeholder="Retaper le Mot de passe" required>
                        <br>
                        <input type="submit" value="S'inscrire">
                    </form>
                    <br>
                    <a href="?action=signin">Déjà inscrit ?</a>
                </div>
            HTML;
        } else { // POST
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            try {
                $email = $_POST['emailu'];

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new AuthnException("Auth error : invalid email format");
                }

                if ($_POST['mdpu'] !== $_POST['mdp2u']){
                    throw new AuthnException("Les mots de passe ne correspondent pas");
                }

                AuthnProvider::register($email, $_POST['mdpu']);
                $_SESSION['user'] = $email;
                // Playlist => null, pour éviter un hack qui permetrait à un utilisateur de modifier une playlist d'un autre utilisateur s'il était connecté à ce compte en question avant
                $_SESSION['playlist'] = null;

                return "<p>Compte créé et connecté ! Bienvenue $email</p>";
            } catch (AuthnException $e) {
                return "<p>".$e->getMessage()."</p>";
            }
        }
    }
}