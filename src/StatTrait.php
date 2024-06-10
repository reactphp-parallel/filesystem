<?php

declare(strict_types=1);

namespace ReactParallel\Filesystem;

use React\Filesystem\Stat;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\PoolInterface;

use function file_exists;
use function React\Promise\resolve;
use function stat;

/** @property PoolInterface $pool */
trait StatTrait
{
    protected function internalStat(string $path): PromiseInterface
    {
        return resolve($this->pool->run(static function (string $path): Stat|null {
            if (! file_exists($path)) {
                return null;
            }

            return new Stat($path, stat($path));
        }, [$path]));
    }
}
