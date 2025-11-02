<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\audio\tracks\AlbumTrack;

class AudioListRenderer implements Renderer {
    protected AudioList $list;

    public function __construct(AudioList $list) {
        $this->list = $list;
    }

    // Rendu de l'AudioList (utilise les renderes long de PodcastTrack et AlbumTrack)
    public function render(int $selector = 0): string {
        $rendu = "<h2>{$this->list->nom}</h2>\n\t<p>{$this->list->nbpistes} pistes, {$this->list->duree} secondes</p>\n\t<ul>\n";

        $trackRender = '';
        foreach ($this->list->pistes as $p) {
            if ($p instanceof PodcastTrack) {
                $trackRender = (new PodcastRenderer($p))->renderLong();
            } else if ($p instanceof AlbumTrack) {
                $trackRender = (new AlbumTrackRenderer($p))->renderLong();
            }

            $rendu .= "\t\t<li>{$trackRender}</li>\n";
        }

        return $rendu . "\t</ul>\n";

    }
}
