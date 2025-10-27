<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

class PodcastRenderer extends AudioTrackRenderer {
    protected function renderCompact(): string {
        return "<p>{$this->track->titre} by {$this->track->auteur}</p>";
    }

    protected function renderLong(): string {
        return "<div><h2>{$this->track->titre}</h2><p>{$this->track->auteur} - {$this->track->date}</p><audio controls src='./audio/{$this->track->fichier}'></audio></div>";
    }
}
