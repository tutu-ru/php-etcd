<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd;

abstract class EtcdClientMethodsTest extends BaseTest
{
    protected function assertNotEmptyGetResponse($response)
    {
        $this->assertTrue(is_array($response));
        $this->assertArrayHasKey('action', $response);
        $this->assertEquals('get', $response['action']);
        $this->assertArrayHasKey('node', $response);
        $node = $response['node'];
        $this->assertTrue(is_array($node));
    }

    protected function assertIsListNodeArray($node, $key, $value)
    {
        $this->assertArrayHasKey('key', $node);
        $this->assertArrayHasKey('value', $node);
        $this->assertEquals('/' . $key, $node['key']);
        $this->assertEquals($value, $node['value']);
    }

    protected function assertIsFullDirNodeArray($node, $key)
    {
        $this->assertArrayHasKey('key', $node);
        $this->assertArrayHasKey('dir', $node);
        $this->assertArrayHasKey('nodes', $node);
        $this->assertEquals('/' . $key, $node['key']);
        $this->assertEquals(true, $node['dir']);
        $this->assertTrue(is_array($node['nodes']));
    }

    protected function assertIsEmptyDirNodeArray($node, $key)
    {
        $this->assertArrayHasKey('key', $node);
        $this->assertArrayHasKey('dir', $node);
        $this->assertArrayNotHasKey('nodes', $node);
        $this->assertEquals('/' . $key, $node['key']);
        $this->assertEquals(true, $node['dir']);
    }
}
