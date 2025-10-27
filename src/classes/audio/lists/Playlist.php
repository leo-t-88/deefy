<?php
declare(strict_types=1);
namespace iutnc\deefy\audio\lists;

class Playlist extends AudioList {
    public function addTrack(object $track): void {
        $this->pistes[] = $track;
        $this->nbpistes++;
        $this->duree += $track->duree;
    }

    public function removeTrack(int $index): void {
        if (isset($this->pistes[$index])) {
            $this->duree -= $this->pistes[$index]->duree;
            array_splice($this->pistes, $index, 1);
            $this->nbpistes--;
        }
    }

    public function addTracks(array $tracks): void {
        foreach ($tracks as $t) {
            if (!in_array($t, $this->pistes, true)) {
                $this->addTrack($t);
            }
        }
    }
}
