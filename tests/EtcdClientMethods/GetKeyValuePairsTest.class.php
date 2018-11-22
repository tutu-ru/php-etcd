<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class GetKeyValuePairsTest extends EtcdClientMethodsTest
{
    use FixtureTrait;

    public function tearDown()
    {
        $this->clearFixture();
        parent::tearDown();
    }


    public function testFailsOnNotExistingDir()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->createClient()->getKeyValuePairs(__FUNCTION__, true);
    }


    public function testReturnsAllLevelsWithRecursive()
    {
        $this->prepareFixture();
        $client = $this->createClient();
        $res = $client->getKeyValuePairs('/', true);
        $this->assertEquals($this->getExpectedFullTree(), $res);
    }


    public function testReturnsOnlyFilesWithNotRecursive()
    {
        $this->prepareFixture();
        $client = $this->createClient();
        $res = $client->getKeyValuePairs('/', false);
        $this->assertEquals($this->getRootFiles(), $res);
    }


    public function testReturnsOnlyFilesWithNotRecursiveInSubDir()
    {
        $this->prepareFixture();
        $client = $this->createClient();
        $res = $client->getKeyValuePairs('/dir/sub2/', false);
        $this->assertEquals(
            [
                '/dir/sub2/f1' => 'vs2_1',
                '/dir/sub2/f2' => 'vs2_2',

            ],
            $res
        );
    }


    public function testReturnsOnlyFilesWithRecursiveInSubDirWithoutSubDir()
    {
        $this->prepareFixture();
        $client = $this->createClient();
        $res = $client->getKeyValuePairs('/dir/sub2/', true);
        $this->assertEquals(
            [
                '/dir/sub2/f1' => 'vs2_1',
                '/dir/sub2/f2' => 'vs2_2',
            ],
            $res
        );
    }
}
