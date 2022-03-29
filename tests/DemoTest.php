<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Szwtdl\Framework\Uploads;
use Szwtdl\Framework\UploadsInterface;

class DemoTest extends TestCase
{

    public function testUser()
    {
        $this->assertTrue(true);
    }

    public function testFile(): array
    {
        $this->assertTrue(true);
        return [
            'name' => '2012-04-20 21.13.42.jpg',
            'tmp_name' => 'C:\wamp\tmp\php8D20.tmp',
            'type' => 'image/jpeg',
            'size' => 1472190,
            'error' => 0
        ];
    }

    /**
     * @depends testFile
     */
    public function testUpload(array $data)
    {
        $upload = new Uploads();
        $this->assertInstanceOf(UploadsInterface::class, $upload);
        $this->assertEquals(['name' => '2012-04-20 21.13.42.jpg',
            'tmp_name' => 'C:\wamp\tmp\php8D20.tmp',
            'type' => 'image/jpeg',
            'size' => 1472190,
            'error' => 0], $data);
    }

}