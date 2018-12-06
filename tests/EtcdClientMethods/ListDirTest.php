<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyNotFoundException;
use TutuRu\Tests\Etcd\EtcdClientMethodsTest;

class ListDirTest extends EtcdClientMethodsTest
{
    public function testFailsOnNotExistingDir()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->createClient()->listDir(__FUNCTION__, true);
    }


    public function testReturnsSimpleStructureOnFile()
    {
        $fileKey = __FUNCTION__;
        $value = 'value';
        $this->createClient()->setValue($fileKey, $value);
        $result = $this->createClient()->listDir($fileKey, true);
        $this->assertNotEmptyGetResponse($result);
        $node = $result['node'];
        $this->assertIsListNodeArray($node, $fileKey, $value);
    }


    public function testGetEmptyDirList()
    {
        $dirKey = __FUNCTION__;
        $this->createClient()->makeDir($dirKey);
        $result = $this->createClient()->listDir($dirKey, true);
        $this->assertNotEmptyGetResponse($result);
        $this->assertIsEmptyDirNodeArray($result['node'], $dirKey);
    }


    public function testReturnsOnlyFirstLevelWithNoRecursive()
    {
        $dirKey = __FUNCTION__;
        $subDirKey = $dirKey . '/subdir';
        $value = 'value';
        $client = $this->createClient();
        $client->setValue($subDirKey . '/f', $value);
        $result = $this->createClient()->listDir($dirKey, false); // false - no recursion
        $this->assertNotEmptyGetResponse($result);
        $node = $result['node'];
        $this->assertIsFullDirNodeArray($node, $dirKey);
        $subDirNode = reset($node['nodes']);
        $this->assertIsEmptyDirNodeArray($subDirNode, $subDirKey); // subdir is not expanded
    }


    public function testReturnsAllLevelsWithRecursive()
    {
        $dirKey = __FUNCTION__;
        $subDirKey = $dirKey . '/subdir';
        $value = 'value';
        $client = $this->createClient();
        $client->setValue($subDirKey . '/f', $value);
        $result = $this->createClient()->listDir($dirKey, true); // true recursion
        $this->assertNotEmptyGetResponse($result);
        $node = $result['node'];
        $this->assertIsFullDirNodeArray($node, $dirKey);
        $subDirNode = $node['nodes'][0];
        $this->assertIsFullDirNodeArray($subDirNode, $subDirKey); // subdir is expanded
    }
}
