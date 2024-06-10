<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Filesystem;

use React\Filesystem\AdapterInterface;
use React\Filesystem\Node\DirectoryInterface;
use React\Filesystem\Node\FileInterface;
use React\Filesystem\Node\NotExistInterface;

use function bin2hex;
use function random_bytes;
use function React\Async\await;

final class FilesystemTest extends AbstractFilesystemTestCase
{
    /**
     * @test
     * @dataProvider provideFilesystems
     */
    public function file(AdapterInterface $filesystem): void
    {
        $node = await($filesystem->detect(__FILE__));

        self::assertInstanceOf(FileInterface::class, $node);
    }

    /**
     * @test
     * @dataProvider provideFilesystems
     */
    public function directory(AdapterInterface $filesystem): void
    {
        $node = await($filesystem->detect(__DIR__));

        self::assertInstanceOf(DirectoryInterface::class, $node);
    }

    /**
     * @test
     * @dataProvider provideFilesystems
     */
    public function notExists(AdapterInterface $filesystem): void
    {
        $node = await($filesystem->detect(bin2hex(random_bytes(13))));

        self::assertInstanceOf(NotExistInterface::class, $node);
    }
}
