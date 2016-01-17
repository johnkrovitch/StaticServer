<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Assets\AssetsManager;
use PHPUnit_Framework_TestCase;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetsManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test add method: it SHOULD NOT throw exception on file adding, it SHOULD return a valid slug and the file
     * SHOULD be uploaded on the right directory
     */
    public function testAdd()
    {
        // create sample file
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile('/tmp/static-server/sample.txt', 'sample string content');

        $assetsManager = new AssetsManager('/tmp/static-server/data');
        $slug = $assetsManager->add('app_test', new UploadedFile(
            '/tmp/static-server/sample.txt',
            'sample.txt',
            null,
            null,
            null,
            true
        ));
        $this->assertContains('sample.txt', $slug);
        $this->assertStringEndsWith('.txt', $slug);
        $this->assertFileExists('/tmp/static-server/data/app_test/' . $slug);

        // remove sample file
        $fileSystem->remove('/tmp/static-server');
    }

    public function testUpdate()
    {
        // create sample file
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile('/tmp/static-server/sample.txt', 'sample string content');

        $assetsManager = new AssetsManager('/tmp/static-server/data');
        $slug = $assetsManager->add('app_test', new UploadedFile(
            '/tmp/static-server/sample.txt',
            'sample.txt',
            null,
            null,
            null,
            true
        ));

        // create an other sample file
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile('/tmp/static-server/another-sample.txt', 'sample string content');

        $newSlug = $assetsManager->update('app_test', new SplFileInfo('/tmp/static-server/another-sample.txt'), $slug);

        // update SHOULD return ok
        $this->assertEquals($slug, $newSlug);
        // file MUST exists on the server
        $this->assertFileExists('/tmp/static-server/data/app_test/' . $slug);
    }

    /**
     * Test slug method: it SHOULD start with the original file name and it SHOULD contains the original file extension
     */
    public function testSlug()
    {
        $assetsManager = new AssetsManager('/tmp/static-server/data');
        $slug = $assetsManager->slug(new SplFileInfo('/tmp/static-server/sample-slug.txt'));

        // slug SHOULD start with the 10 first characters of the original name
        $this->assertEquals('sample-slu', substr($slug, 0, 10));
        // slug SHOULD have the correct extension
        $this->assertEquals('txt', substr($slug, -3));
    }

    /**
     * Test exists method: it SHOULD return true if a file exists, false if not
     */
    public function testExists()
    {
        // create sample file
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile('/tmp/static-server/sample-exists.txt', 'sample string content');

        $assetsManager = new AssetsManager('/tmp/static-server/data');
        $slug = $assetsManager->add('app_test', new UploadedFile(
            '/tmp/static-server/sample-exists.txt',
            'sample.txt',
            null,
            null,
            null,
            true
        ));

        // exists SHOULD return true
        $this->assertTrue($assetsManager->exists('app_test', $slug));

        // remove sample file
        $fileSystem->remove('/tmp/static-server');
    }

    public function testRemove()
    {
        // create sample file
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile('/tmp/static-server/sample-exists.txt', 'sample string content');

        $assetsManager = new AssetsManager('/tmp/static-server/data');
        $slug = $assetsManager->add('app_test', new UploadedFile(
            '/tmp/static-server/sample-exists.txt',
            'sample.txt',
            null,
            null,
            null,
            true
        ));
        $assetsManager->remove('app_test', $slug);

        // file MUST not exists anymore
        $this->assertFalse($assetsManager->exists('app_test', $slug));
    }
}
