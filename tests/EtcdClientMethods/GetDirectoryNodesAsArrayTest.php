<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class GetDirectoryNodesAsArrayTest extends EtcdClientMethodsTest
{
    use FixtureTrait;

    public function setUp()
    {
        parent::setUp();
        $this->prepareFixture();
    }


    public function tearDown()
    {
        $this->clearFixture();
        parent::tearDown();
    }


    public function testFailsOnUnexistent()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->createClient()->getDirectoryNodesAsArray(__FUNCTION__);
    }


    public function testRoot()
    {
        $res = $this->createClient()->getDirectoryNodesAsArray('/');
        $this->assertEquals($this->getExpectedFullTreeAsArray(), $res);
    }


    public function testDir()
    {
        $res = $this->createClient()->getDirectoryNodesAsArray('/dir');
        $this->assertEquals($this->getExpectedDirAsArray(), $res);
    }


    public function testSubdir()
    {
        $res = $this->createClient()->getDirectoryNodesAsArray('/dir/sub2/');
        $this->assertEquals(
            [
                'f1' => 'vs2_1',
                'f2' => 'vs2_2',
            ],
            $res
        );
    }
}
