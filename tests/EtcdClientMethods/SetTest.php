<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\EtcdException;
use TutuRu\Etcd\Exceptions\InvalidValueException;
use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Etcd\Exceptions\NotAFileException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class SetTest extends EtcdClientMethodsTest
{
    public function testCanBeRepeatedTwice()
    {
        $client = $this->createClient();
        $client->setValue('repeatedKey', 420);
        $client->setValue('repeatedKey', 421);
        $this->assertEquals(421, $client->getValue('repeatedKey'));
    }


    public function testThrowsExceptionOnDirKey()
    {
        $this->expectException(NotAFileException::class);

        $dirKey = __FUNCTION__;
        $this->createClient()->makeDir($dirKey);
        $this->createClient()->setValue($dirKey, 'scalar');
    }


    public function testKeyIsAliveForPeriodSmallerThanTtl()
    {
        $value = 341; // value doesn't matter;
        $ttlInSeconds = 3;
        $client = $this->createClient();
        $key = __FUNCTION__;
        $client->setValue($key, $value, $ttlInSeconds);
        sleep(1);  // smaller, than $ttlInSeconds
        $this->assertEquals($value, $client->getValue($key));
    }


    public function testKeyIsExpiredAfterTtl()
    {
        $this->expectException(KeyNotFoundException::class);

        $value = 512; // value doesn't matter;
        $ttlInSeconds = 1;
        $key = __FUNCTION__;
        $this->createClient()->setValue($key, $value, $ttlInSeconds);
        sleep($ttlInSeconds + 2);
        $this->createClient()->getValue($key);
    }


    public function testTrailingSlashInKeyIsTrimmed()
    {
        $client = $this->createClient();
        $key = 'test/';
        $value = 'test value';
        $client->setValue($key, $value);
        $this->assertEquals($value, $client->getValue($key));
        $this->assertEquals($value, $client->getValue(substr($key, 0, -1)));
    }


    public function testTtlCannotBeFloat()
    {
        $this->expectException(EtcdException::class);
        $value = 483; // value doesn't matter
        $ttlInSeconds = 0.6;
        $this->createClient()->setValue(__FUNCTION__, $value, $ttlInSeconds);
    }


    public function testTtlForDirMattersForFile()
    {
        $this->expectException(KeyNotFoundException::class);

        $client = $this->createClient();
        $dirTtl = 1;
        $fileTtl = 1000;
        $dirKey = __FUNCTION__;
        $fileKey = $dirKey . '/file';
        $client->makeDir($dirKey, $dirTtl);
        $client->setValue($fileKey, 'file', $fileTtl);
        sleep($dirTtl + 1);
        $this->createClient()->getValue($fileKey);
    }


    public function testSettingNotScalarValueIsProhibited()
    {
        $this->expectException(InvalidValueException::class);
        $this->createClient()->setValue(__FUNCTION__, [1, 2, 3]);
    }
}
