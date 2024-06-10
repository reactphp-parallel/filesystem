<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Filesystem;

use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use React\Filesystem\AdapterInterface;
use ReactParallel\Factory;
use ReactParallel\Filesystem\Adapter;

abstract class AbstractFilesystemTestCase extends TestCase
{
    /** @return iterable<array<AdapterInterface, LoopInterface>> */
    final public function provideFilesystems(): iterable
    {
        yield 'parallel' => [
            new Adapter(
                (new Factory())->lowLevelPool(),
            ),
        ];
    }
}
