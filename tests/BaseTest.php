<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd;

use PHPUnit\Framework\TestCase;
use LinkORB\Component\Etcd\Client as NativeClient;
use TutuRu\Etcd\EtcdClient;
use TutuRu\Etcd\EtcdClientFactory;

abstract class BaseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->flushData();
    }


    public function tearDown()
    {
        parent::tearDown();
    }


    private function flushData()
    {
        $client = $this->getNativeClient();
        $res = $client->ls();
        foreach ($res as $path) {
            try {
                $client->rmdir($path, true);
            } catch (\Exception $e) {
                $client->rm($path);
            }
        }
    }


    protected function getNativeClient(): NativeClient
    {
        return new NativeClient(EtcdTestEnv::getTestServer());
    }


    /**
     * @param  string $rootDir
     * @return EtcdClient
     */
    protected function createClient($rootDir = ''): EtcdClient
    {
        return EtcdClientFactory::create(EtcdTestEnv::getTestHost(), EtcdTestEnv::getTestPort(), $rootDir);
    }
}
