<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd;

use TutuRu\Etcd\EtcdClient;
use TutuRu\Etcd\EtcdClientFactory;
use TutuRu\Etcd\Exceptions\NoEnvVarsException;

class EtcdClientFactoryTest extends BaseTest
{
    public function testCreate()
    {
        $client = (new EtcdClientFactory())->create(EtcdTestEnv::getTestHost(), EtcdTestEnv::getTestPort(), __METHOD__);
        $this->assertInstanceOf(EtcdClient::class, $client);
    }

    public function testCreateFromEnv()
    {
        putenv(EtcdClientFactory::ENV_VAR_HOST . '="127.0.0.1"');
        putenv(EtcdClientFactory::ENV_VAR_PORT . '=24001');
        $client = (new EtcdClientFactory())->createFromEnv(__METHOD__);
        $this->assertInstanceOf(EtcdClient::class, $client);
    }

    public function testCreateFromEnvWithEmptyEtcdHost()
    {
        $this->expectException(NoEnvVarsException::class);

        putenv(EtcdClientFactory::ENV_VAR_HOST . '=');
        (new EtcdClientFactory())->createFromEnv(__METHOD__);
    }

    public function testCreateFromEnvWithEmptyEtcdPort()
    {
        $this->expectException(NoEnvVarsException::class);

        putenv(EtcdClientFactory::ENV_VAR_PORT . '=');
        (new EtcdClientFactory())->createFromEnv(__METHOD__);
    }

    public function testCreateNoCache()
    {
        $client1 = (new EtcdClientFactory())->create('localhost', 2379, __METHOD__);
        $client2 = (new EtcdClientFactory())->create('localhost', 2379, __METHOD__);
        $this->assertNotSame($client1, $client2);
    }

    public function testCreateFromEnvNoCache()
    {
        putenv(EtcdClientFactory::ENV_VAR_HOST . '="127.0.0.1"');
        putenv(EtcdClientFactory::ENV_VAR_PORT . '=24001');
        $client1 = (new EtcdClientFactory())->createFromEnv(__METHOD__);
        $client2 = (new EtcdClientFactory())->createFromEnv(__METHOD__);
        $this->assertNotSame($client1, $client2);
    }
}
