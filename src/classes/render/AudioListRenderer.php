<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;

class AudioListRenderer implements Renderer {
    protected AudioList $list;

    public function __construct(AudioList $list) {
        $this->list = $list;
    }

    public function render(int $selector = 0): string {
        $html = "<h2>{$this->list->nom}</h2><ul>";
        $html .= "</ul><p>{$this->list->nbpistes} pistes, {$this->list->duree} secondes</p>";
        foreach ($this->list->pistes as $piste) {
            $html .= "<li>
                        <p>{$piste->titre}</p>
                        <audio controls src='./audio/{$piste->fichier}'></audio>
                    </li>";
        }
        return $html;
    }
}
