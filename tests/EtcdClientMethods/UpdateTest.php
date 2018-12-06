<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Etcd\Exceptions\NotAFileException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class UpdateTest extends EtcdClientMethodsTest
{
    public function testUpdatesValue()
    {
        $client = $this->createClient();
        $key = __FUNCTION__;
        $client->setValue($key, 420);
        $client->updateValue($key, 421);
        $this->assertEquals(421, $client->getValue($key));
    }


    public function testFailsOnUnexisting()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->createClient()->updateValue(__FUNCTION__, 100);
    }


    public function testModifiesTtl()
    {
        $this->expectException(KeyNotFoundException::class);

        $client = $this->createClient();
        $key = __FUNCTION__;
        $longTtlInSeconds = 10000;
        $shortTtlInSeconds = 1;
        $client->setValue($key, 10, $longTtlInSeconds);
        $client->updateValue($key, 10, $shortTtlInSeconds);
        sleep($shortTtlInSeconds + 1);
        $this->createClient()->getValue($key);
    }


    public function testFailsOnDirectory()
    {
        $this->expectException(NotAFileException::class);

        $client = $this->createClient();
        $dirKey = __FUNCTION__;
        $client->makeDir($dirKey);
        $client->updateValue($dirKey, 'new');
    }
}
