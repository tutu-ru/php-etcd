<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd;

use PHPUnit\Framework\TestCase;
use LinkORB\Component\Etcd\Client as NativeClient;
use TutuRu\Etcd\EtcdClient;
use TutuRu\Etcd\EtcdClientFactory;

abstract class BaseTest extends TestCase
{
    protected const TEST_HOST = 'localhost';
    protected const TEST_PORT = 2379;


    public function setUp()
    {
        parent::setUp();
        $this->flushData();
    }


    public function tearDown()
    {
        $this->flushData();
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


    protected function getTestHost()
    {
        return getenv('TEST_ETCD_HOST') ?: self::TEST_HOST;
    }


    protected function getTestPort()
    {
        return getenv('TEST_ETCD_PORT') ?: self::TEST_PORT;
    }


    protected function getNativeClient(): NativeClient
    {
        return new NativeClient(sprintf('http://%s:%d', self::TEST_HOST, self::TEST_PORT));
    }


    /**
     * @param  string $rootDir
     * @return EtcdClient
     */
    protected function createClient($rootDir = ''): EtcdClient
    {
        return (new EtcdClientFactory())->create(self::TEST_HOST, self::TEST_PORT, $rootDir);
    }
}
