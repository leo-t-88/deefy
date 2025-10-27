<?php
declare(strict_types=1);

namespace loader;

class Psr4ClassLoader {
    protected string $prefix, $baseDir;

    public function __construct(string $prefix, string $baseDir) {
        $this->prefix = rtrim($prefix, '\\') . '\\';
        $this->baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function loadClass(string $className): void {
        if (strpos($className, $this->prefix) !== 0) return;
        $relative = substr($className, strlen($this->prefix));
        $file = $this->baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_file($file)) require_once $file;
    }

    public function register(): void {
        spl_autoload_register([$this, 'loadClass']);
    }
}
