<?php
declare(strict_types=1);
namespace iutnc\deefy\auth;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\exception\AuthnException;

class Authz {
    public static function checkRole(int $expectedRole): void {
        if (!isset($_SESSION['user'])) throw new AuthnException("Utilisateur non authentifié");

        $role = (DeefyRepository::getInstance()->getUser($_SESSION['user']))['role'];

        if (is_null($role) || (int)$role < $expectedRole) {
            throw new AuthnException("Accès refusé : Permission insuffisante");
        }
    }

    public static function checkPlaylistOwner(int $playlistId): void
    {
        if (!isset($_SESSION['user'])) throw new AuthnException("Utilisateur non authentifié.");

        $repo = DeefyRepository::getInstance();

        $user = $repo->getUser($_SESSION['user']);
        if (!$user) throw new AuthnException("Utilisateur introuvable.");

        if ((int)$user['role'] === 100) return; // ADMIN a accès à tout

        $ownerId = $repo->getPlaylistOwnerId($playlistId);
        if ($ownerId === null) throw new AuthnException("Playlist introuvable.");

        if ($ownerId !== (int)$user['id']) throw new AuthnException("Accès refusé : vous n'êtes pas le propriétaire de cette playlist.");
    }
}