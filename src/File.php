<?php

declare(strict_types=1);

namespace ReactParallel\Filesystem;

use React\Filesystem\Node\FileInterface;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\PoolInterface;

use function file_get_contents;
use function file_put_contents;
use function React\Promise\resolve;
use function stat;
use function unlink;

use const FILE_APPEND;

final class File implements FileInterface
{
    use StatTrait;

    public function __construct(
        private readonly PoolInterface $pool,
        private string $path,
        private string $name,
    ) {
    }

    public function stat(): PromiseInterface
    {
        return $this->internalStat($this->path . $this->name);
    }

    public function getContents(int $offset = 0, int|null $maxlen = null): PromiseInterface
    {
        return resolve($this->pool->run(static function (string $path, int $offset, int|null $maxlen): string {
            return file_get_contents($path, false, null, $offset, $maxlen ?? (int) stat($path)['size']);
        }, [$this->path . $this->name, $offset, $maxlen]));
    }

    public function putContents(string $contents, int $flags = 0): PromiseInterface
    {
        // Making sure we only pass in one flag for security reasons
        if (($flags & FILE_APPEND) === FILE_APPEND) {
            $flags = FILE_APPEND;
        } else {
            $flags = 0;
        }

        return resolve($this->pool->run(static function (string $path, string $contents, int $flags): bool|int {
            return file_put_contents($path, $contents, $flags);
        }, [$this->path . $this->name, $contents, $flags]));
    }

    public function unlink(): PromiseInterface
    {
        return resolve($this->pool->run(static function (string $path): bool {
            return unlink($path);
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
