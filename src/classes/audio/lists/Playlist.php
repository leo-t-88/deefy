<?php
declare(strict_types=1);
namespace iutnc\deefy\audio\lists;

class Playlist extends AudioList {
    // Ajoute une piste (objet piste) à l'AudioList
    public function addTrack(object $track): void {
        $this->pistes[] = $track;
        $this->nbpistes++;
        $this->duree += $track->duree;
    }

    // Supprime une piste (via id dans la liste) à l'AudioList
    public function removeTrack(int $index): void {
        if (isset($this->pistes[$index])) {
            $this->duree -= $this->pistes[$index]->duree;
            array_splice($this->pistes, $index, 1);
            $this->nbpistes--;
        }
    }

    // Ajoute une/plusieurs pistes (via tableau de pistes) à l'AudioList
    public function addTracks(array $tracks): void {
        foreach ($tracks as $t) {
            if (!in_array($t, $this->pistes, true)) {
                $this->addTrack($t);
            }
        }
    }
}
