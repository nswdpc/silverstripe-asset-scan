<?php

namespace NSWDPC\AssetScan\Tests;

use NSWDPC\AssetScan\Backend;
use NSWDPC\AssetScan\BackendResponse;
use NSWDPC\AssetScan\VirusFoundException;
use NSWDPC\AssetScan\ScanningFlysystemAssetStore;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Flysystem\FlysystemAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class BackendTest extends SapphireTest
{

    protected $usesDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up scanning asset store using default config
        $spec = Injector::inst()->getServiceSpec(AssetStore::class);
        $spec['class'] = ScanningFlysystemAssetStore::class;
        Injector::inst()->load($spec);

        // Set up backend
        Injector::inst()->registerService(
            new TestScanningBackend(),
            Backend::class
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testFailString()
    {
        try {
            $backend = Backend::create();
            $response = $backend->scanStream(TestClient::BLOCK_SCAN_STRING);
        } catch (\Exception $e) {
            $this->assertEquals(VirusFoundException::class, get_class($e));
        }

    }

    public function testOkString()
    {
        try {
            $backend = Backend::create();
            $response = $backend->scanStream(TestClient::OK_SCAN_STRING);
            $this->assertTrue($response->isValid(), "Response is valid");
        } catch (\Exception $e) {
            $this->assertFalse(true, "No exception should be thrown");
        }
    }

    public function testScanFile()
    {
        try {
            $path = dirname(__FILE__) . "/data/file.txt";
            $backend = Backend::create();
            $response = $backend->scanFile($path);
            $this->assertTrue($response->isValid(), "Response is valid");
        } catch (\Exception $e) {
            $this->assertFalse(true, "No exception should be thrown");
        }
    }

    public function testScanResource()
    {
        try {
            $path = dirname(__FILE__) . "/data/file.txt";
            $handle = fopen($path, 'r');
            $backend = Backend::create();
            $response = $backend->scanResource($handle);
            $this->assertTrue($response->isValid(), "Response is valid");
        } catch (\Exception $e) {
            $this->assertFalse(true, "No exception should be thrown");
        } finally {
            if(is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function testScanStream()
    {
        try {
            $path = dirname(__FILE__) . "/data/file.txt";
            $contents = file_get_contents($path);
            $backend = Backend::create();
            $response = $backend->scanStream($contents);
            $this->assertTrue($response->isValid(), "Response is valid");
        } catch (\Exception $e) {
            $this->assertFalse(true, "No exception should be thrown");
        }
    }

    public function testFileSetFromLocalFile()
    {
        try {
            $file = File::create();
            $path = dirname(__FILE__) . "/data/file.txt";
            $result = $file->setFromLocalFile(
                $path,
                "file.txt"
            );
            $this->assertEquals('file.txt', $result['Filename']);
        } catch (\Exception $e) {
            $this->assertFalse(true, "No exception should be thrown");
        } finally {
            // Clean up
            if($file) {
                $file->deleteFile();
            }
        }
    }

    public function testFileSetFromString()
    {
        try {
            $file = File::create();
            $result = $file->setFromString(
                TestClient::BLOCK_SCAN_STRING,
                "block.txt"
            );
            $this->assertEmpty($result);
        } catch (\Exception $e) {
            $this->assertEquals(VirusFoundException::class, get_class($e));
        } finally {
            // Clean up
            if($file) {
                $file->deleteFile();
            }
        }
    }

    public function testFileSetFromStream()
    {
        try {
            $handle = $file = $path = null;
            $file = File::create();
            $path = dirname(__FILE__) . "/data/file.txt";
            $handle = fopen($path, 'r');
            $result = $file->setFromStream(
                $handle,
                "stream.txt"
            );
            $this->assertEquals('stream.txt', $result['Filename']);
        } catch (\Exception $e) {
            $this->assertFalse(true, "No exception should be thrown");
        } finally {
            if(is_resource($handle)) {
                fclose($handle);
            }
            // Clean up
            if($file) {
                $file->deleteFile();
            }
        }
    }
}
