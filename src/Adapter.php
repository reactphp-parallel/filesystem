<?php

declare(strict_types=1);

namespace ReactParallel\Filesystem;

use React\Filesystem\AdapterInterface;
use React\Filesystem\ModeTypeDetector;
use React\Filesystem\Node;
use React\Filesystem\Stat;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\PoolInterface;

use function basename;
use function dirname;

use const DIRECTORY_SEPARATOR;

final class Adapter implements AdapterInterface
{
    use StatTrait;

    public function __construct(
        private readonly PoolInterface $pool,
    ) {
    }

    public function detect(string $path): PromiseInterface
    {
        return $this->internalStat($path)->then(function (Stat|null $stat) use ($path) {
            if ($stat === null) {
                return new NotExist($this->pool, $this, dirname($path) . DIRECTORY_SEPARATOR, basename($path));
            }

            switch (ModeTypeDetector::detect($stat->mode())) {
                case Node\DirectoryInterface::class:
                    return $this->directory($stat->path());

                    break;
                case Node\FileInterface::class:
                    return $this->file($stat->path());

                    break;
                default:
                    return new Node\Unknown($stat->path(), $stat->path());

                    break;
            }
        });
    }

    public function directory(string $path): Node\DirectoryInterface
    {
        return new Directory($this->pool, $this, dirname($path) . DIRECTORY_SEPARATOR, basename($path));
    }

    public function file(string $path): Node\FileInterface
    {
        return new File($this->pool, dirname($path) . DIRECTORY_SEPARATOR, basename($path));
    }
}
