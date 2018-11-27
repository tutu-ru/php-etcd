<?php
declare(strict_types=1);

namespace TutuRu\Etcd;

use LinkORB\Component\Etcd;
use GuzzleHttp\Client as GuzzleClient;
use TutuRu\Etcd\Exceptions\EmptyResponseException;
use TutuRu\Etcd\Exceptions\ExceptionFactory;
use TutuRu\Etcd\Exceptions\InvalidValueException;

class EtcdClient
{
    public const FLAG_WAIT = 'wait';
    public const FLAG_RECURSIVE = 'recursive';
    public const FLAG_WAIT_INDEX = 'waitIndex';

    /** @var Etcd\Client */
    private $nativeClient;

    /** @var ExceptionFactory */
    private $exceptionConverter;

    /** @var string */
    private $rootDir;

    const PATH_SEP = '/';
    const PREV_EXIST_KEY = 'prevExist';

    public function __construct(string $server, $rootDir = '')
    {
        $guzzleClient = new GuzzleClient(
            [
                'headers'     => ['Connection' => 'close'],
                'base_uri'    => $server,
                'http_errors' => false,
            ]
        );
        $this->nativeClient = Etcd\Client::constructWithGuzzleClient($guzzleClient, $server, 'v2');
        if (!empty($rootDir)) {
            $this->setRootDir((string)$rootDir);
        }
        $this->exceptionConverter = new Exceptions\ExceptionFactory();
    }


    /**
     * @param  string $key
     * @return string
     * @throws Exceptions\EtcdException
     */
    public function getValue($key)
    {
        try {
            $node = $this->nativeClient->getNode($key, []);
            $this->checkResultForErrors($node);
            if (empty($node)) {
                throw new EmptyResponseException();
            }
            return $node['value'];
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    /**
     * при создании ключа в иерерархии dir1/dir2/dir3/key будут созданы все промежуточные директории
     * если ключ уже создан - значение будет перезаписано
     *
     * @param string     $key
     * @param string|int $value
     * @param null|int   $ttlInSeconds
     *
     * @return \stdClass
     * @throws Exceptions\EtcdException
     */
    public function setValue($key, $value, $ttlInSeconds = null)
    {
        try {
            $value = $this->checkAndPrepareValue($value);
            $result = $this->nativeClient->set($key, $value, $ttlInSeconds, []);
            $this->checkResultForErrors($result);
            return $result;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }

    /**
     * при создании ключа в иерерархии dir1/dir2/dir3/key будут созданы все промежуточные директории
     * если ключ уже есть, то будет выкинуто исключение
     *
     * @param string   $key
     * @param string   $value
     * @param null|int $ttlInSeconds
     *
     * @return \stdClass
     * @throws Exceptions\EtcdException
     */
    public function createValue($key, $value, $ttlInSeconds = null)
    {
        try {
            $value = $this->checkAndPrepareValue($value);
            $result = $this->nativeClient->set($key, $value, $ttlInSeconds, [self::PREV_EXIST_KEY => 0]);
            $this->checkResultForErrors($result);
            return $result;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    /**
     * Если ключа нет, то будет выкинуто исключение
     *
     * @param string $key
     * @param string $value
     * @param null/ште   $ttlInSeconds
     *
     * @return array
     * @throws Exceptions\EtcdException
     */
    public function updateValue($key, $value, $ttlInSeconds = null)
    {
        try {
            $value = $this->checkAndPrepareValue($value);
            $result = $this->nativeClient->update($key, $value, $ttlInSeconds, []);
            $this->checkResultForErrors($result);
            return $result;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    /**
     * @param string $key
     *
     * @return array|\stdClass
     * @throws Exceptions\EtcdException
     */
    public function delete($key)
    {
        try {
            $result = $this->nativeClient->rm($key);
            $this->checkResultForErrors($result);
            return $result;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    /**
     * Создает директорию, если ее нет. Если есть - кидает исключение
     *
     * @param string   $dir
     * @param null|int $ttlInSeconds
     *
     * @return array
     * @throws Exceptions\EtcdException
     */
    public function makeDir($dir, $ttlInSeconds = null)
    {
        try {
            $result = $this->nativeClient->mkdir($dir, $ttlInSeconds);
            $this->checkResultForErrors($result);
            return $result;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    /**
     * @param string $dir
     * @param bool   $recursive
     *
     * @return mixed
     * @throws Exceptions\EtcdException
     */
    public function deleteDir($dir, $recursive)
    {
        try {
            $result = $this->nativeClient->rmdir($dir, $recursive);
            $this->checkResultForErrors($result);
            return $result;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    /**
     * @param string $dir
     * @param bool   $recursive
     *
     * @return mixed
     * @throws Exceptions\EtcdException
     */
    public function listDir($dir, $recursive)
    {
        try {
            $body = $this->nativeClient->listDir($dir, $recursive);
            $this->checkResultForErrors($body);
            if (empty($body)) {
                throw new EmptyResponseException();
            }
            return $body;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    /**
     * возвращает одномерный массив, где ключами являются полные пути до нод
     * например
     * [
     *     '/file' => 'value',
     *     '/dir/another' => 10
     * ]
     *
     * @param string $dir
     * @param bool   $recursive
     *
     * @return array
     * @throws Exceptions\EtcdException
     */
    public function getKeyValuePairs($dir, $recursive)
    {
        $rawData = $this->listDir($dir, $recursive);
        $result = [];
        $iterator = new \RecursiveArrayIterator($rawData);
        $this->traverse($result, $iterator);
        ksort($result);
        return $result;
    }


    /**
     * Метод блокирует поток выполнения кода до получения изменений.
     *
     * @todo реализовать передачу таймаута
     *
     * Ожидание изменений для определенного ключа конфига.
     * Можно задавать как ключ со значением, так и директорию.
     * Отлавливаются все изменения, включая удаление и переименование.
     *
     * Если ключ отсутствует, то метод будет ожидать его появления.
     *
     * Возвращаемое значение -- новое значение ключа (или null при удалении).
     * Для директорий всегда возвращается null.
     *
     * @param string   $keyOrDir
     * @param bool     $recursive
     * @param int|null $fromRevision
     *
     * @return array
     * @throws Exceptions\EtcdException
     */
    public function waitForChange($keyOrDir, $recursive = false, $fromRevision = null)
    {
        try {
            $flags = [self::FLAG_WAIT => 'true'];
            if ($recursive) {
                $flags[self::FLAG_RECURSIVE] = 'true';
            }
            if ($fromRevision) {
                $flags[self::FLAG_WAIT_INDEX] = $fromRevision;
            }

            $node = $this->nativeClient->getNode($keyOrDir, $flags);
            $this->checkResultForErrors($node);
            if (empty($node)) {
                throw new EmptyResponseException();
            }
            return $node;
        } catch (Etcd\Exception\EtcdException $e) {
            throw $this->exceptionConverter->fromNativeException($e);
        }
    }


    private function traverse(&$values, \RecursiveArrayIterator $iterator)
    {
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                $this->traverse($values, $iterator->getChildren());
            } else {
                $currentLevel = $iterator->getArrayCopy();
                if (array_key_exists('key', $currentLevel) && array_key_exists('value', $currentLevel)) {
                    $values[$currentLevel['key']] = $currentLevel['value'];
                    return;
                }
            }
            $iterator->next();
        }
    }


    /**
     * возвращает данные из директории в виде многомерного массива
     * например
     * [
     *     'file' => 'value,
     *     'dir' => [
     *         'another' => '10'
     *     ]
     * ]
     * при запросе префиск директорй из ключей убирается
     *
     * @param $dir
     *
     * @return array
     * @throws Exceptions\EtcdException
     */
    public function getDirectoryNodesAsArray($dir)
    {
        $nodes = $this->getKeyValuePairs($dir, true);
        $dir = trim($dir, self::PATH_SEP) . self::PATH_SEP;
        if ($dir !== self::PATH_SEP) {
            $dir = self::PATH_SEP . $dir;
        }
        if (!is_null($this->rootDir)) {
            $dir = self::PATH_SEP . trim($this->rootDir, self::PATH_SEP) . $dir;
        }
        $result = [];
        foreach ($nodes as $k => $v) {
            $realKey = substr($k, strlen($dir));
            $parts = explode(self::PATH_SEP, $realKey);
            $this->addToDepth($result, $parts, $v);
        }
        return $result;
    }


    private function addToDepth(&$array, $path, $value)
    {
        if (1 === count($path)) {
            $array[current($path)] = $value;
        } else {
            $current = array_shift($path);
            if (!array_key_exists($current, $array)) {
                $array[$current] = [];
            }
            $this->addToDepth($array[$current], $path, $value);
        }
    }


    /**
     * @NOTE работает локально на приложении, не проверяет существование на сервере etcd
     *       не является частью REST АПИ etcd, внутреннее свойство библиотеки linkorb/php-etcd
     * @NOTE protected - только для тестов, вообще private
     * @param string $dir
     *
     * @return Etcd\Client
     *
     */
    protected function setRootDir($dir)
    {
        $this->rootDir = $dir;
        return $this->nativeClient->setRoot($dir);
    }


    /**
     * @param mixed $value
     *
     * @return mixed
     * @throws InvalidValueException
     */
    private function checkAndPrepareValue($value)
    {
        // наше решение. все, что не скаляр, где-то внутри linkorb/etcd-php приводится к пустой строке
        // при необходимости можно сделать сериализацию
        if (!is_scalar($value) && !is_null($value)) {
            throw new InvalidValueException('value must be a scalar');
        }

        /**
         * Когда мы делаем set в либе etcd-php, мы посылаем PUT запрос с параметром form_params.
         * И boolean значения там конвертятся в integer. Т.е. true -> 1, false -> 0.
         * В старой версии etcd-php использовался Guzzle 3.x,
         * который вел себя немного по другому: true -> 1, false -> ''.
         * Мы вбили этот костыль, чтобы было как раньше, т.к. хз где это может вылезти.
         */
        if (false === $value) {
            $value = '';
        }

        return $value;
    }


    /**
     * наше решение. linkorb/php-etcd не обрабатывает ошибки из ответа
     * @param array $result
     * @throws Exceptions\EtcdException
     */
    private function checkResultForErrors($result)
    {
        if (array_key_exists('errorCode', $result) || array_key_exists('message', $result)) {
            throw $this->exceptionConverter->fromResult($result);
        }
    }
}
