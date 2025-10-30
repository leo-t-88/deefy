<?php

declare(strict_types=1);

namespace iutnc\deefy\repository;

use PDO;
use PDOException;
use iutnc\deefy\audio\lists\Playlist;
use \iutnc\deefy\audio\tracks\PodcastTrack;

class DeefyRepository {
    private PDO $pdo;
    private static array $config = [];
    private static ?DeefyRepository $instance = null;

    private function __construct() {
        try {
            $this->pdo = new PDO(self::$config['dsn'], self::$config['user'], self::$config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            throw new \Exception("Erreur de connexion PDO : " . $e->getMessage());
        }
    }

    public static function getInstance(): DeefyRepository {
        if (self::$instance === null) self::$instance = new DeefyRepository(self::$config);
        return self::$instance;
    }

    public static function setConfig(string $file): void {
        $cfg = parse_ini_file($file);
        if (!$cfg) throw new \Exception("Fichier de configuration invalide : $file");
        self::$config = [
            'dsn' => "{$cfg['driver']}:host={$cfg['host']};dbname={$cfg['database']}",
            'user' => $cfg['username'],
            'pass' => $cfg['password']
        ];
    }

    // REQUETE GESTION PLAYLISTS ET TRACKS
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

    public function saveEmptyPlaylist(Playlist $playlist): Playlist {
        $stmt = $this->pdo->prepare("INSERT INTO playlist(nom) VALUES (?)");
        $stmt->execute([$playlist->nom]);
        $playlist->id = (int)$this->pdo->lastInsertId();
        return $playlist;
    }

    public function linkUserPlaylist(string $email, int $playlistID) : void{
        $userID = $this->getUser($email)['id']; //Récupère l'ID de l'utilisateur à partir de son email

        $stmt = $this->pdo->prepare("INSERT INTO user2playlist(id_user, id_pl) VALUES (?, ?)");
        $stmt->execute([$userID, $playlistID]);
    }

    public function linkPlaylistTrack(int $playlistID, int $trackID) : void{
        $stmt = $this->pdo->prepare("INSERT INTO playlist2track(id_pl, id_track) VALUES (?, ?)");
        $stmt->execute([$playlistID, $trackID]);
    }

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

    public function addTrackToPlaylist(int $playlistId, int $trackId): void {
        $stmt = $this->pdo->prepare("INSERT INTO playlist2track (id_pl, id_track) VALUES (?, ?)");
        $stmt->execute([$playlistId, $trackId]);
    }

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

    // REQUETE GESTION UTILISATEURS
    public function getHashUser(string $email): ?string {
        $stmt = $this->pdo->prepare("SELECT passwd FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['passwd'] ?? null;
    }

    public function insertUser(string $email, string $hash): void {
        $stmt = $this->pdo->prepare("INSERT INTO user (email, passwd) VALUES (:email, :hash)");
        $stmt->execute([':email' => $email, ':hash' => $hash]);
    }

    public function getUser(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT id, role FROM user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function getPlaylistOwnerId(int $playlistId): ?int {
        $stmt = $this->pdo->prepare("SELECT id_user FROM user2playlist WHERE id_pl = ?");
        $stmt->execute([$playlistId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['id_user'] ?? null;
    }


    public function getPlaylistsUser(int $user_id): array {
        $stmt = $this->pdo->prepare("SELECT id_pl FROM user2playlist WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $result;
    }

    public function getUserID($mail): ?int {
        $stmt = $this->pdo->prepare("SELECT id FROM user where email = ?");
        $stmt->execute([$mail]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['id'] ?? null;
    }
}