<?php
declare(strict_types=1);
namespace iutnc\deefy\audio\tracks;

class PodcastTrack extends AudioTrack {
    protected string $auteur, $date;

    public function __construct(int $id, string $titre, string $genre, int $duree, string $fichier, string $auteur, string $date) {
        parent::__construct($id, $titre, $genre, $duree, $fichier);
        $this->auteur = $auteur;
        $this->date = $date;
    }
}
