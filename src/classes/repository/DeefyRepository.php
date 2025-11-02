<?php

declare(strict_types=1);

namespace iutnc\deefy\repository;

use PDO;
use PDOException;
use iutnc\deefy\audio\lists\Playlist;
use \iutnc\deefy\audio\tracks\PodcastTrack;

// Classe qui contient et gère toutes les connections, requêtes et insertions dans la base de données
class DeefyRepository {
    // Attributs
    private PDO $pdo;
    private static array $config = [];
    private static ?DeefyRepository $instance = null;

    // Constructeur
    private function __construct() {
        try {
            $this->pdo = new PDO(self::$config['dsn'], self::$config['user'], self::$config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            throw new \Exception("Erreur de connexion PDO : " . $e->getMessage());
        }
    }

    // Récupère l'instance du DeefyRepository
    public static function getInstance(): DeefyRepository {
        if (self::$instance === null) self::$instance = new DeefyRepository(self::$config);
        return self::$instance;
    }

    // Charge la configuration depuis un fichier pour la connection à la BD
    public static function setConfig(string $file): void {
        $cfg = parse_ini_file($file);
        if (!$cfg) throw new \Exception("Fichier de configuration invalide : $file");
        self::$config = [
            'dsn' => "{$cfg['driver']}:host={$cfg['host']};dbname={$cfg['database']}",
            'user' => $cfg['username'],
            'pass' => $cfg['password']
        ];
    }

    /*
    REQUETE GESTION PLAYLISTS ET TRACKS
    */

    // Retourne un tableau de toutes les playlistes existantes dans la BD
    public function findAllPlaylists(): array {
        $stmt = $this->pdo->query("SELECT * FROM playlist");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $playlists = [];
        foreach ($rows as $row) {
            $pl = new Playlist($row['nom']);
            $pl->id = (int)$row['id'];
            $playlists[] = $pl;
        }
        return $playlists;
    }

    // Sauvegarde dans la BD la playlists passé en paramètre à retourne l'objet Playlists mis à jour avec sont id dans la BD d'ajouté 
    public function saveEmptyPlaylist(Playlist $playlist): Playlist {
        $stmt = $this->pdo->prepare("INSERT INTO playlist(nom) VALUES (?)");
        $stmt->execute([$playlist->nom]);
        $playlist->id = (int)$this->pdo->lastInsertId();
        return $playlist;
    }

    // Ajoute un lien dans la BD entre un utilisateur et une playliste
    public function linkUserPlaylist(string $email, int $playlistID) : void{
        $userID = $this->getUser($email)['id']; //Récupère l'ID de l'utilisateur à partir de son email

        $stmt = $this->pdo->prepare("INSERT INTO user2playlist(id_user, id_pl) VALUES (?, ?)");
        $stmt->execute([$userID, $playlistID]);
    }

    // Ajoute un lien dans la BD entre une playliste et une piste
    public function linkPlaylistTrack(int $playlistID, int $trackID) : void{
        $stmt = $this->pdo->prepare("SELECT MAX(no_piste_dans_liste) AS lastTrackNumber FROM playlist2track WHERE id_pl = ?");
        $stmt->execute([$playlistID]);
        $lastTrackNumber = $stmt->fetchColumn();

        $nextTrackNumber = $lastTrackNumber !== false ? $lastTrackNumber + 1 : 1;

        $stmt = $this->pdo->prepare("INSERT INTO playlist2track(id_pl, id_track, no_piste_dans_liste) VALUES (?, ?, ?)");
        $stmt->execute([$playlistID, $trackID, $nextTrackNumber]);
    }

    // Sauvegarde dans la BD la PodcastTrack passé en paramètre à retourne l'objet mis à jour avec sont id dans la BD d'ajouté 
    public function savePodcastTrack(PodcastTrack $track): PodcastTrack {
        $stmt = $this->pdo->prepare("INSERT INTO track (titre, genre, duree, filename, type, auteur_podcast, date_podcast) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $track->titre,
            $track->genre,
            $track->duree,
            $track->fichier,
            'podcast',
            $track->auteur,
            $track->date
        ]);

        $track->id = (int)$this->pdo->lastInsertId();
        return $track;
    }

    // Retourne l'objet Playlist associer à l'ID passer en paramètre si elle existe dans la BD sinon null
    public function findPlaylistById(int $id): ?Playlist {
        $stmt = $this->pdo->prepare("SELECT * FROM playlist WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return null; // non trouvée

        $pl = new Playlist($result['nom']);
        $pl->id = (int)$result['id'];

        $stmt = $this->pdo->prepare("SELECT t.*
                                     FROM track t
                                     INNER JOIN playlist2track p2t ON t.id = p2t.id_track
                                     WHERE p2t.id_pl = ?");
        $stmt->execute([$id]);
        $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $track;
        foreach ($tracks as $t) {
            if ($t['type'] === 'podcast') {
                $track = new \iutnc\deefy\audio\tracks\PodcastTrack((int)$t['id'], $t['titre'], $t['genre'], (int)$t['duree'], $t['filename'], $t['auteur_podcast'], $t['date_podcast']);
            } else if ($t['type'] === 'album') {
                $track = new \iutnc\deefy\audio\tracks\AlbumTrack((int)$t['id'], $t['titre'], $t['genre'], (int)$t['duree'], $t['filename'], $t['artiste_album'], $t['titre_album'], (int)$t['annee_album'], (int)$t['numero_album']);
            } else {
                continue; // type inconnu
            }
            $track->id = (int)$t['id'];
            $pl->addTrack($track);
        }

        return $pl;
    }

    /*
     REQUETE GESTION UTILISATEURS
    */

    // Retourne le hash du mdp de l'utilisateur stocké dans la BD
    public function getHashUser(string $email): ?string {
        $stmt = $this->pdo->prepare("SELECT passwd FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['passwd'] ?? null;
    }

    // Insère un utilisateur (email + hash du mdp) dans la BD
    public function insertUser(string $email, string $hash): void {
        $stmt = $this->pdo->prepare("INSERT INTO user (email, passwd) VALUES (:email, :hash)");
        $stmt->execute([':email' => $email, ':hash' => $hash]);
    }

    // Retourne toutes les informations stocké dans la BD de l'utilisateur s'il existe sinon null
    public function getUser(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT id, role FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    // Retourne l'id de l'utilisateur auquel appartient une playliste donnée (via ID) si elle existe dans la BD sinon null
    public function getPlaylistOwnerId(int $playlistId): ?int {
        $stmt = $this->pdo->prepare("SELECT id_user FROM user2playlist WHERE id_pl = ?");
        $stmt->execute([$playlistId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['id_user'] ?? null;
    }
}