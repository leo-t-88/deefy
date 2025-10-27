<?php
declare(strict_types=1);
namespace iutnc\deefy\audio\tracks;

class AlbumTrack extends AudioTrack {
    protected string $album, $artiste;
    protected int $numero, $annee;

    public function __construct(int $id, string $titre, string $genre, int $duree, string $fichier, string $artiste, string $album, int $annee, int $numero) {
        parent::__construct($id, $titre, $genre, $duree, $fichier);
        $this->artiste = $artiste;
        $this->album = $album;
        $this->annee = $annee;
        $this->numero = $numero;
    }

    public function __toString(): string {
        return json_encode(get_object_vars($this));
    }
}
