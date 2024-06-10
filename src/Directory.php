<?php

declare(strict_types=1);

namespace ReactParallel\Filesystem;

use React\Filesystem\AdapterInterface;
use React\Filesystem\Node;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\PoolInterface;

use function count;
use function in_array;
use function React\Promise\all;
use function React\Promise\resolve;
use function rmdir;
use function scandir;

use const DIRECTORY_SEPARATOR;

final class Directory implements Node\DirectoryInterface
{
    use StatTrait;

    public function __construct(
        private readonly PoolInterface $pool,
        private AdapterInterface $filesystem,
        private string $path,
        private string $name,
    ) {
    }

    public function stat(): PromiseInterface
    {
        return $this->internalStat($this->path . $this->name);
    }

    public function ls(): PromiseInterface
    {
        return all($this->pool->run(static function (string $path, AdapterInterface $filesystem): array {
            $promises = [];
            foreach (scandir($path) as $node) {
                if (in_array($node, ['.', '..'])) {
                    continue;
                }

                $promises[] = $filesystem->detect($this->path . $this->name . DIRECTORY_SEPARATOR . $node);
            }

            return $promises;
        }, [$this->path . $this->name, $this->filesystem]));
    }

    public function unlink(): PromiseInterface
    {
        return resolve($this->pool->run(static function (string $path): bool {
            if (count(scandir($path)) > 0) {
                return false;
            }

            return rmdir($path);
        }, [$this->path . $this->name]));
    }

    public function path(): string
    {
        return $this->path;
    }

    public function name(): string
    {
        return $this->name;
    }
}
