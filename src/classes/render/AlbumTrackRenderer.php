<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

class AlbumTrackRenderer extends AudioTrackRenderer {
    // Rendu de l'AlbumTrack réduit/court
    public function renderCompact(): string {
        return "<p>{$this->track->numero} - {$this->track->titre} by {$this->track->artiste}</p>";
    }

    // Rendu de l'AlbumTrack classique/long
    public function renderLong(): string {
        return "<p>{$this->track->artiste} - {$this->track->titre} de {$this->track->album} ({$this->track->annee})</p><audio controls src='./audio/{$this->track->fichier}'></audio>";
    }
}
