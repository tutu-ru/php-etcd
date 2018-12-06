<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Etcd\Exceptions\NotAFileException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class DeleteTest extends EtcdClientMethodsTest
{
    public function testBasicScenario()
    {
        $this->expectException(KeyNotFoundException::class);

        $key = __FUNCTION__;
        $client = $this->createClient();
        $client->setValue($key, 'value');
        $client->delete($key);
        $this->createClient()->getValue($key);
    }


    public function testFailsOnExpiredKey()
    {
        $this->expectException(KeyNotFoundException::class);

        $key = __FUNCTION__;
        $ttlInSeconds = 1;
        $client = $this->createClient();
        $client->setValue($key, 222, $ttlInSeconds);
        sleep($ttlInSeconds + 2);
        $this->createClient()->delete($key);
    }


    public function testFailsWhenTryingToDeleteAnUnexistingKey()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->createClient()->delete('key_doesnot_exist');
    }


    public function testFailsWhenTryingToDeleteDir()
    {
        $this->expectException(NotAFileException::class);

        $dirKey = __FUNCTION__;
        $client = $this->createClient();
        $client->makeDir($dirKey);
        $this->createClient()->delete($dirKey);
    }
}
