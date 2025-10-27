<?php
declare(strict_types=1);

namespace iutnc\deefy\action;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class SigninAction extends Action {
    public function execute() : string {
        if ($this->http_method === 'GET'){
            return <<< HTML
                <h2>Se connecter</h2>
                <form method="post" action="?action=signin">
                    <input type="email" id="emailu" name="emailu" placeholder="Email" required>
                    <br>
                    <input type="password" id="mdpu" name="mdpu" placeholder="Mot de passe" required>
                    <br>
                    <input type="submit" value="Se connecter">
                </form>
            HTML;
        } else { // POST
            session_start();
            try {
                $email = $_POST['emailu'];

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new AuthnException("Auth error : invalid email format");

                AuthnProvider::signin($email, $_POST['mdpu']);
                $_SESSION['user'] = $email;
                return "<p>Connexion réussie, bonjour $email !</p>";

            } catch (AuthnException $e) {
                return "<p>".$e->getMessage()."</p>";
            }
        }
    }
}