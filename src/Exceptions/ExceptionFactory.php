<?php
declare(strict_types=1);

namespace TutuRu\Etcd\Exceptions;

use LinkORB\Component\Etcd\Exception as NativeException;

class ExceptionFactory
{
    const NATIVE_NOT_A_FILE = 'Not a file';
    const NATIVE_KEY_NOT_FOUND = 'Key not found';
    const NATIVE_DIR_NOT_EMPTY = 'Directory not empty';
    const NATIVE_KEY_EXISTS = 'Key already exists';

    private $messageToExceptionMapping = [
        'Not a file'          => NotAFileException::class,
        'Key not found'       => KeyNotFoundException::class,
        'Directory not empty' => DirNotEmptyException::class,
        'Key already exists'  => KeyExistsException::class,
    ];

    private $nativeExceptionToExceptionMapping = [
        NativeException\KeyNotFoundException::class => KeyNotFoundException::class,
        NativeException\KeyExistsException::class   => KeyExistsException::class,
    ];


    public function fromNativeException(NativeException\EtcdException $e): EtcdException
    {
        if ($this->isKnownMessage($e->getMessage())) {
            // в первую очередь проверяем текст - из linkorb/etcd-php типы выкидываются менее корректно, чем тексты
            $exceptionClass = $this->getClassByMessage($e->getMessage());
        } else {
            $exceptionClass = $this->nativeExceptionToExceptionMapping[get_class($e)] ?? EtcdException::class;
        }
        return new $exceptionClass($e->getMessage(), $e->getCode(), $e);
    }


    public function fromResult(array $result): EtcdException
    {
        $exceptionClass = $this->getClassByMessage($result['message'] ?? '');
        return new $exceptionClass(json_encode($result));
    }


    private function getClassByMessage($message): string
    {
        return $this->messageToExceptionMapping[$message] ?? EtcdException::class;
    }


    private function isKnownMessage($message): bool
    {
        return isset($this->messageToExceptionMapping[$message]);
    }
}
