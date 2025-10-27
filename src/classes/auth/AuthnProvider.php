<?php
declare(strict_types=1);
namespace iutnc\deefy\auth;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\exception\AuthnException;

class AuthnProvider {
    public static function signin(string $email, string $password): void
    {
        $repo = DeefyRepository::getInstance();
        $hash = $repo->getHashUser($email);

        if (is_null($hash) || !password_verify($password, $hash)) throw new AuthnException("Auth error : invalid credentials");
    }

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

    public static function getSignedInUser(): string
    {
        if (is_null($_SESSION['user']))throw new AuthnException("Aucun utilisateur connecté");
        return $_SESSION['user'];
    }
}