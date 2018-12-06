<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd;

class EtcdClientTest extends BaseTest
{
    public function testRootDirsAreIndependent()
    {
        $key = __FUNCTION__;
        $dir1 = 'dir1';
        $dir2 = 'dir2';
        $client1 = $this->createClient($dir1);
        $client2 = $this->createClient($dir2);

        $client1->setValue($key, 1);
        $client2->setValue($key, 2);
        $this->assertEquals(1, $client1->getValue($key));
        $this->assertEquals(2, $client2->getValue($key));
    }
}
