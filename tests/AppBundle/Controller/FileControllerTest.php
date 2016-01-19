<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Test FileController post and serve method
 */
class FileControllerTest extends WebTestCase
{
    public function __construct($name = null, array $data = [], $dataName = null)
    {
        exec('php ./bin/console server:start > /dev/null &', $output);

        parent::__construct($name, $data, $dataName);
    }

    public function __destruct()
    {
        exec('php bin/console server:stop > /dev/null &', $output);
    }

    /**
     * Test post method : the response code SHOULD be 200 OK and the returned url SHOULD be valid
     */
    public function testPost()
    {
        // create sample file
        $sampleFilePath = __DIR__ . '/../../../var/cache/sample.txt';
        $fileSystem = new Filesystem();
        $fileSystem->dumpFile($sampleFilePath, 'this is a sample file');
        // create uploaded file for the request
        $sampleFile = new UploadedFile($sampleFilePath, 'sample.txt');

        // test application (@see config_dev.yml)
        $parameters = [
            'file_post' => [
                'application' => 'app_test',
                'password' => 'password_test',
            ]
        ];
        $files = [
            'file_post' => [
                'file' => $sampleFile
            ]
        ];
        $client = static::createClient();
        // post request
        $client->request(
            'POST',
            '/post',
            $parameters,
            $files
        );
        // response code MUST be 200
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // file MUST exist on server, and returned url must be valid
        $headers = get_headers($client->getResponse()->getContent());
        $this->assertContains('200 OK', $headers[0]);

        // remove sample file
        $fileSystem->remove($sampleFile);
    }

    /**
     * Test server method : the response code SHOULD be 200 OK and an instance of File SHOULD be present in the response
     */
    public function testServe()
    {
        // get request
        $client = static::createClient();
        $client->request(
            'GET',
            '/app_test/sample.txt'
        );

        /** @var BinaryFileResponse $response */
        $response = $client->getResponse();

        var_dump($response);

        // response code MUST be 200
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // file MUST be an uploaded file
        $this->assertInstanceOf(File::class, $response->getFile());
    }
}
