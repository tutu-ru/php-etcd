<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd;

class EtcdTestEnv
{
    private static $testHost = 'localhost';
    private static $testPort = 2379;

    public static function getTestHost()
    {
        return getenv('TEST_ETCD_HOST') ?: self::$testHost;
    }

    public static function getTestPort()
    {
        return getenv('TEST_ETCD_PORT') ?: self::$testPort;
    }

    public static function getTestServer(): string
    {
        return sprintf('http://%s:%d', self::getTestHost(), self::getTestPort());
    }

    public static function setTestEnvForConnection()
    {
        putenv(\TutuRu\Etcd\EtcdClientFactory::ENV_VAR_HOST . '=' . self::getTestHost());
        putenv(\TutuRu\Etcd\EtcdClientFactory::ENV_VAR_PORT . '=' . self::getTestPort());
    }
}
