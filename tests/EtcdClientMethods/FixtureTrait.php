<?php
declare(strict_types=1);

namespace TutuRu\Tests\Etcd\EtcdClientMethods;

use TutuRu\Etcd\Exceptions\KeyNotFoundException;

trait FixtureTrait
{
    private function prepareFixture()
    {
        $client = $this->createClient();
        $dirKeys = $this->getDirKeys();
        $fileData = $this->getFileData();
        foreach ($fileData as $dirKey => $dirFiles) {
            $dirPrefix = $dirKeys[$dirKey];
            foreach ($dirFiles as $fileKey => $fileValue) {
                $client->setValue($dirPrefix . $fileKey, $fileValue);
            }
        }
    }

    private function getDirKeys()
    {
        return [
            'r'  => '/',
            'd'  => '/dir/',
            's1' => '/dir/sub1/',
            's2' => '/dir/sub2/',
        ];
    }

    private function getFileData()
    {
        return [
            // для clearFixture важен порядок ключей -от поддиреткории к директории
            's1' => ['f1' => 'vs1_1', 'f2' => 'vs1_2'],
            // для clearFixture важен порядок ключей -от поддиреткории к директории
            's2' => ['f1' => 'vs2_1', 'f2' => 'vs2_2'],
            'd'  => ['f1' => 'vd_1', 'f2' => 'vd_2'],
            'r'  => ['f1' => 'vr_1', 'f2' => 'vr_2'],
        ];
    }

    private function getExpectedFullTree()
    {
        return [
            '/dir/f1'      => 'vd_1',
            '/dir/f2'      => 'vd_2',
            '/dir/sub1/f1' => 'vs1_1',
            '/dir/sub1/f2' => 'vs1_2',
            '/dir/sub2/f1' => 'vs2_1',
            '/dir/sub2/f2' => 'vs2_2',
            '/f1'          => 'vr_1',
            '/f2'          => 'vr_2',
        ];
    }

    private function getExpectedFullTreeAsArray()
    {
        return [
            'dir' => [
                'f1'   => 'vd_1',
                'f2'   => 'vd_2',
                'sub1' =>
                    [
                        'f1' => 'vs1_1',
                        'f2' => 'vs1_2',
                    ],
                'sub2' =>
                    [
                        'f1' => 'vs2_1',
                        'f2' => 'vs2_2',
                    ],
            ],
            'f1'  => 'vr_1',
            'f2'  => 'vr_2',
        ];
    }

    private function getExpectedDirAsArray()
    {
        return [
            'f1'   => 'vd_1',
            'f2'   => 'vd_2',
            'sub1' =>
                [
                    'f1' => 'vs1_1',
                    'f2' => 'vs1_2',
                ],
            'sub2' =>
                [
                    'f1' => 'vs2_1',
                    'f2' => 'vs2_2',
                ],
        ];
    }

    private function getRootFiles()
    {
        return [
            '/f1' => 'vr_1',
            '/f2' => 'vr_2',
        ];
    }
}
