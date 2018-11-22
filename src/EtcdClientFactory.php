<?php
declare(strict_types=1);

namespace TutuRu\Etcd;

use TutuRu\Etcd\Exceptions\NoEnvVarsException;

class EtcdClientFactory
{
    public const ENV_VAR_HOST = 'ETCD_HOST';
    public const ENV_VAR_PORT = 'ETCD_PORT';


    /**
     * @param string $host
     * @param int    $port
     * @param string $rootDir
     * @return EtcdClient
     */
    public static function create(string $host, int $port, string $rootDir = ''): EtcdClient
    {
        $server = sprintf('http://%s:%d', $host, $port);
        return new EtcdClient($server, $rootDir);
    }


    /**
     * @param string $rootDir
     * @return EtcdClient
     * @throws NoEnvVarsException
     */
    public static function createFromEnv(string $rootDir = ''): EtcdClient
    {
        $host = getenv(self::ENV_VAR_HOST);
        $port = getenv(self::ENV_VAR_PORT);
        if ('' == $host || '' == $port) {
            throw new NoEnvVarsException(
                sprintf(
                    'host - %s "%s" and port %s "%d" environmental variables should be both not empty',
                    self::ENV_VAR_HOST,
                    $host,
                    self::ENV_VAR_PORT,
                    $port
                )
            );
        }
        return self::create($host, (int)$port, $rootDir);
    }
}
