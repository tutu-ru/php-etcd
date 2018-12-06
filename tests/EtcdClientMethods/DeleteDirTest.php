<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\DirNotEmptyException;
use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class DeleteDirTest extends EtcdClientMethodsTest
{
    public function testBasicScenario()
    {
        $this->expectException(KeyNotFoundException::class);

        $key = __FUNCTION__;
        $client = $this->createClient();
        $client->makeDir($key, 0);
        $client->deleteDir($key, false);
        $this->createClient()->getValue($key);
    }


    /**
     * @param bool $recursive
     * @dataProvider recursiveFlagFixtures
     * @throws \TutuRu\Etcd\Exceptions\EtcdException
     */
    public function testDeletesFileKey($recursive)
    {
        $this->expectException(KeyNotFoundException::class);

        $key = __FUNCTION__;
        $this->createClient()->setValue($key, 'f');
        $this->createClient()->deleteDir($key, $recursive);
        $this->createClient()->getValue($key);
    }


    public function recursiveFlagFixtures()
    {
        return [
            [false],
            [true]
        ];
    }


    public function testFailsOnExpiredDir()
    {
        $this->expectException(KeyNotFoundException::class);

        $key = __FUNCTION__;
        $ttlInSeconds = 1;
        $this->createClient()->makeDir($key, $ttlInSeconds);
        sleep($ttlInSeconds + 1);
        $this->createClient()->delete($key);
    }


    public function testNotRecursiveFailsOnNotEmpty()
    {
        $this->expectException(DirNotEmptyException::class);

        $key1 = 'dir/key1';
        $this->createClient()->setValue($key1, 1);
        $this->createClient()->deleteDir('dir', false);
    }


    public function testRecursiveDeletesNotEmpty()
    {
        $this->expectException(KeyNotFoundException::class);

        $this->createClient()->setValue('dir/subdir/k1', 1);
        $this->createClient()->setValue('dir/k2', 2);
        $this->createClient()->deleteDir('dir', true);
        $this->createClient()->getValue('dir/subdir/k1');
    }
}
