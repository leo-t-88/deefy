<?php
declare(strict_types=1);
namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

class AudioTrack {
    protected string $titre, $fichier, $genre;
    protected int $duree, $id;

    public function __construct(int $id, string $titre, string $genre, int $duree, string $fichier) {
        $this->id = $id;
        $this->titre = $titre;
        $this->genre = "???";
        $this->duree = $duree;
        $this->fichier = $fichier;
    }

    public function __get(string $prop): mixed {
        if (!property_exists($this, $prop)) throw new InvalidPropertyNameException("invalid property : $prop");
        return $this->$prop;
    }

    public function __set(string $prop, mixed $val): void {
        if ($prop === 'duree' && $val < 0) throw new InvalidPropertyValueException("Durée négative : $val");
        if (!property_exists($this, $prop)) throw new InvalidPropertyNameException("invalid property : $prop");
        $this->$prop = $val;
    }
}
