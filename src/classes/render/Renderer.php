<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

// Interface pour les rendus HTML d'objets
interface Renderer {
    const COMPACT = 1;
    const LONG = 2;
    public function render(int $selector): string;
}
