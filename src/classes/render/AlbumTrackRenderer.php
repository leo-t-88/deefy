<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

class AlbumTrackRenderer extends AudioTrackRenderer {
    protected function renderCompact(): string {
        return "<p>{$this->track->numero} - {$this->track->titre} by {$this->track->artiste}</p>";
    }

    protected function renderLong(): string {
        return "<div><h2>{$this->track->titre}</h2><p>{$this->track->artiste} - {$this->track->album} ({$this->track->annee})</p><audio controls src='./audio/{$this->track->fichier}'></audio></div>";
    }
}
