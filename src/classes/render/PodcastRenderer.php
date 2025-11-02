<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

class PodcastRenderer extends AudioTrackRenderer {
    // Rendu du PodcastTrack réduit/court
    public function renderCompact(): string {
        return "<p>{$this->track->titre} by {$this->track->auteur}</p>";
    }

    // Rendu du PodcastTrack classique/long
    public function renderLong(): string {
        return "<p>{$this->track->auteur} - {$this->track->titre}</p><audio controls src='./audio/{$this->track->fichier}'></audio>";
    }
}
