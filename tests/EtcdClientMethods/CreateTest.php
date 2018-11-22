<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyExistsException;
use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class CreateTest extends EtcdClientMethodsTest
{
    public function testCreates()
    {
        $key = __FUNCTION__;
        $value = 42;
        $client = $this->createClient();
        $client->createValue($key, $value);
        $this->assertEquals($value, $client->getValue($key));
    }


    public function testCreatesInDir()
    {
        $key = __FUNCTION__ . '/' . __FUNCTION__;
        $value = 42;
        $client = $this->createClient();
        $client->createValue($key, $value);
        $this->assertEquals($value, $client->getValue($key));
    }


    public function testCreatesInExistingDir()
    {
        $dir = __FUNCTION__;
        $key = __FUNCTION__ . '/' . __FUNCTION__;
        $value = 42;
        $client = $this->createClient();
        $client->makeDir($dir);
        $client->createValue($key, $value);
        $this->assertEquals($value, $client->getValue($key));
    }


    public function testFailsOnExistingKey()
    {
        $this->expectException(KeyExistsException::class);

        $key = __FUNCTION__;
        $client = $this->createClient();
        $client->createValue($key, 1);
        $this->createClient()->createValue($key, 420);
    }


    public function testThrowsExceptionOnDirKey()
    {
        $this->expectException(KeyExistsException::class);

        $dirKey = __FUNCTION__;
        $this->createClient()->makeDir($dirKey);
        $this->createClient()->createValue($dirKey, 'scalar');
    }


    public function testKeyIsAliveForPeriodSmallerThanTtl()
    {
        $value = 48; // value doesn't matter
        $ttlInSeconds = 3;
        $client = $this->createClient();
        $key = __FUNCTION__;
        $client->createValue($key, $value, $ttlInSeconds);
        sleep(1);  // smaller, than $ttlInSeconds
        $this->assertEquals($value, $client->getValue($key));
    }


    public function testKeyIsExpiredAfterTtl()
    {
        $this->expectException(KeyNotFoundException::class);

        $value = 234; // value doesn't matter
        $ttlInSeconds = 1;
        $key = __FUNCTION__;
        $this->createClient()->createValue($key, $value, $ttlInSeconds);
        sleep($ttlInSeconds + 2);
        $this->createClient()->getValue($key);
    }


    public function testTrailingSlashInKeyIsTrimmed()
    {
        $client = $this->createClient();
        $key = 'test/';
        $value = 'test value';
        $client->createValue($key, $value);
        $this->assertEquals($value, $client->getValue($key));
        $this->assertEquals($value, $client->getValue(substr($key, 0, -1)));
    }
}
