<?php
declare(strict_types=1);
namespace iutnc\deefy\auth;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\exception\AuthnException;

class AuthnProvider {
    // Fonction qui valide ou non la connection d'un utilisateur si l'email et mdp donné correspondant à une entré dans la BD
    public static function signin(string $email, string $password): void
    {
        $repo = DeefyRepository::getInstance();
        $hash = $repo->getHashUser($email);

        if (is_null($hash) || !password_verify($password, $hash)) throw new AuthnException("Auth error : invalid credentials");
    }

    // Fonction qui valide ou non l'inscription d'un utilisateur si le mdp donné x2 sont identiques, si l'email utiliser n'existe pas déjà dans la BD et si le mdp fait au moins 10 chars
    public static function register(string $email, string $password): void
    {
        $repo = DeefyRepository::getInstance();

        if ($repo->getHashUser($email) !== null) {
            throw new AuthnException("Auth error: user already exists");
        }

        if (strlen($password) < 10) {
            throw new AuthnException("Password must be at least 10 characters long");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $repo->insertUser($email, $hash, 1);
    }
}