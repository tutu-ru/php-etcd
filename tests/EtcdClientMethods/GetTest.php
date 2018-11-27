<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class GetTest extends EtcdClientMethodsTest
{
    public function testFailsOnUnknownKey()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->createClient()->getValue('some/very/unknown/toanyaone/key');
    }


    public function testRespectsRootDir()
    {
        $client = $this->createClient();
        $client->setValue('inner/dir/some.complex.key', 140);

        $client = $this->createClient('inner/dir/');
        $this->assertEquals(140, $client->getValue('some.complex.key'));

        $client = $this->createClient('inner/');
        $this->assertEquals(140, $client->getValue('dir/some.complex.key'));
    }


    /**
     * @param string $keySuffix
     * @param mixed  $value
     * @dataProvider getSimpleValues
     * @throws \TutuRu\Etcd\Exceptions\EtcdException
     */
    public function testReturnsScalarValues($keySuffix, $value)
    {
        $key = 'simple.key.' . $keySuffix;
        $this->createClient()->setValue($key, $value);
        $this->assertEquals($value, $this->createClient()->getValue($key));
    }


    public function getSimpleValues()
    {
        return [
            ['integer', 42],
            ['string', 'test'],
            ['float', -12.5],
            ['boolean', false],
            ['true', true],
            ['null', null],
            ['longstring', str_repeat('-', 10000000)]
        ];
    }


    public function testNotExistingKey()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->createClient()->getValue(__FUNCTION__);
    }


    public function testReturnsNullOnDir()
    {
        $dirKey = __FUNCTION__;
        $this->createClient()->makeDir($dirKey);
        $result = $this->createClient()->getValue($dirKey);
        $this->assertNull($result);
    }
}
