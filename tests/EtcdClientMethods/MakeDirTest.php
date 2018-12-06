<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyExistsException;
use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class MakeDirTest extends EtcdClientMethodsTest
{
    public function testTtlParamWorks()
    {
        $this->expectException(KeyNotFoundException::class);

        $ttlInSeconds = 1;
        $dirKey = __FUNCTION__;
        $this->createClient()->makeDir($dirKey, $ttlInSeconds);
        sleep($ttlInSeconds + 1);
        $this->createClient()->getValue($dirKey);
    }


    public function testChangingTtlViaMakeDirIsProhibbited()
    {
        $this->expectException(KeyExistsException::class);

        $dirKey = __FUNCTION__;
        $shortTtlInSeconds = 1;
        $longTtlInSeconds = 10000;
        // creating
        $this->createClient()->makeDir($dirKey, $longTtlInSeconds);
        // changing ttl
        $this->createClient()->makeDir($dirKey, $shortTtlInSeconds);
    }


    public function testMakeDirThrowsExceptionForExistingDir()
    {
        $this->expectException(KeyExistsException::class);

        $this->createClient()->makeDir(__FUNCTION__);
        $this->createClient()->makeDir(__FUNCTION__);
    }


    public function testMakeDirThrowsExceptionForExistingKey()
    {
        $this->expectException(KeyExistsException::class);

        $this->createClient()->setValue(__FUNCTION__, 'value');
        $this->createClient()->makeDir(__FUNCTION__);
    }


    public function testMakeDirThrowsExceptionWhenDirWasCreatedViaHierarhcySet()
    {
        $this->expectException(KeyExistsException::class);

        $this->createClient()->setValue('dir/key', 'value');
        $this->createClient()->makeDir('dir');
    }
}
