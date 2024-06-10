<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Filesystem;

use React\Filesystem\AdapterInterface;
use React\Filesystem\Node\NotExistInterface;
use React\Promise\PromiseInterface;

use function bin2hex;
use function random_bytes;
use function React\Async\await;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

final class NotExistTest extends AbstractFilesystemTestCase
{
    /**
     * @test
     * @dataProvider provideFilesystems
     */
    public function createDirectory(AdapterInterface $filesystem): void
    {
        $dirName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'reactphp-filesystem-' . bin2hex(random_bytes(13)) . DIRECTORY_SEPARATOR;
        await($filesystem->detect($dirName)->then(static function (NotExistInterface $notExist): PromiseInterface {
            return $notExist->createDirectory();
        }));

        self::assertDirectoryExists($dirName);
    }
}
