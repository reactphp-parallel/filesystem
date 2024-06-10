<?php

declare(strict_types=1);

namespace ReactParallel\Filesystem;

use React\Filesystem\AdapterInterface;
use React\Filesystem\Node;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\PoolInterface;

use function mkdir;
use function React\Promise\resolve;

final class NotExist implements Node\NotExistInterface
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

    public function createDirectory(): PromiseInterface
    {
        $this->pool->run(static function (string $path, string $name): void {
            @mkdir($path . $name, 0777, true);
        }, [$this->path, $this->name]);

        return resolve(new Directory($this->pool, $this->filesystem, $this->path, $this->name));
    }

    public function createFile(): PromiseInterface
    {
        $file = new File($this->pool, $this->path, $this->name);

        return $this->filesystem->detect($this->path)->then(static function (Node\NodeInterface $node): PromiseInterface {
            if ($node instanceof Node\NotExistInterface) {
                return $node->createDirectory();
            }

            return resolve($node);
        })->then(static function () use ($file): PromiseInterface {
            return $file->putContents('');
        })->then(static function () use ($file): Node\FileInterface {
            return $file;
        });
    }

    public function unlink(): PromiseInterface
    {
        // Essentially a No-OP since it doesn't exist anyway
        return resolve(true);
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
