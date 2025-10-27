<?php
declare(strict_types=1);
namespace iutnc\deefy\render;

abstract class AudioTrackRenderer implements Renderer {
    protected object $track;

    public function __construct(object $track) {
        $this->track = $track;
    }

    public function render(int $selector): string {
        return $selector === self::COMPACT ? $this->renderCompact() : $this->renderLong();
    }

    abstract protected function renderCompact(): string;
    abstract protected function renderLong(): string;
}
