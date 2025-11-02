<?php
declare(strict_types=1);
namespace iutnc\deefy\audio\lists;

// Clase de base de toutes les listes audios
class AudioList {
    protected string $nom;
    protected array $pistes;
    protected int $nbpistes, $duree;

    public function __construct(string $nom, array $pistes = []) {
        $this->nom = $nom;
        $this->pistes = $pistes;
        $this->nbpistes = count($pistes);
        $this->duree = array_reduce($pistes, fn($acc, $p) => $acc + $p->duree, 0);
    }

    // GETTER MAGIQUE
    public function __get(string $prop): mixed {
        if (!property_exists($this, $prop)) {
            throw new \Exception("invalid property : $prop");
        }
        return $this->$prop;
    }
}
