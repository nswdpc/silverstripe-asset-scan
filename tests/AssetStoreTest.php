<?php

namespace NSWDPC\AssetScan\Tests;

use League\Flysystem\Filesystem;
use NSWDPC\AssetScan\Backend;
use NSWDPC\AssetScan\ScanningFlysystemAssetStore;
use NSWDPC\AssetScan\VirusFoundException;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Flysystem\FlysystemAssetStore;
use SilverStripe\Assets\Flysystem\PublicAssetAdapter;
use SilverStripe\Assets\Flysystem\ProtectedAssetAdapter;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Dev\SapphireTest;

class AssetStoreTest extends SapphireTest
{
    protected $usesDatabase = false;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        // Set up scanning asset store using default config
        $spec = Injector::inst()->getServiceSpec(AssetStore::class);
        $spec['class'] = ScanningFlysystemAssetStore::class;
        Injector::inst()->load($spec);

        // Set up backend
        Injector::inst()->registerService(
            TestScanningBackend::create(),
            Backend::class
        );
    }

    /**
     * Check validation result for AssetStoreTest
     */
    protected function checkValidationResult(\Exception $exception): void
    {
        $this->assertInstanceof(ValidationException::class, $exception);
        $result = $exception->getResult();
        $this->assertInstanceof(ValidationResult::class, $result);
        $messages = $result->getMessages();
        $this->assertArrayHasKey(VirusFoundException::SCAN_FAIL_VALIDATION_CODE, $messages);
    }

    public function testAssetStoreConfiguration(): void
    {
        $assetStore = Injector::inst()->get(AssetStore::class);
        $this->assertInstanceof(FlysystemAssetStore::class, $assetStore);
        $this->assertInstanceof(ScanningFlysystemAssetStore::class, $assetStore);

        $publicFs = $assetStore->getPublicFilesystem();
        $this->assertInstanceof(Filesystem::class, $publicFs);
        $publicAdapter = $publicFs->getAdapter();
        $this->assertInstanceof(PublicAssetAdapter::class, $publicAdapter);

        $protectedFs = $assetStore->getProtectedFilesystem();
        $this->assertInstanceof(Filesystem::class, $protectedFs);
        $protectedAdapter = $protectedFs->getAdapter();
        $this->assertInstanceof(ProtectedAssetAdapter::class, $protectedAdapter);
    }

    /**
     * Scan all, as limit is set
     */
    public function testScanSizeNull(): void
    {
        Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', null);
        $file = File::create();
        $path = __DIR__ . "/data/file.txt";
        $result = $file->setFromLocalFile(
            $path,
            "file.txt"
        );
        $this->assertEquals($result['Filename'], "file.txt");
    }

    /**
     * No scan file, size is over limit of 0 (scan all)
     */
    public function testScanSizeZero(): void
    {
        Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', 0);
        $file = File::create();
        $path = __DIR__ . "/data/file.txt";
        $result = $file->setFromLocalFile(
            $path,
            "file.txt"
        );
        $this->assertEquals($result['Filename'], "file.txt");
    }

    /**
     * No scan file, if size over limit
     */
    public function testScanSizeOverLimit(): void
    {
        $file = File::create();
        $path = __DIR__ . "/data/file.txt";
        $size = filesize($path);
        $limit = ($size - 1);
        Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', $limit);
        $result = $file->setFromLocalFile(
            $path,
            "file.txt"
        );
        $this->assertEquals($result['Filename'], "file.txt");
    }

    /**
     * Scan file, under limit
     */
    public function testScanSizeUnderLimit(): void
    {
        $file = File::create();
        $path = __DIR__ . "/data/file.txt";
        $size = filesize($path);
        $limit = ($size + 1);
        Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', $limit);
        $result = $file->setFromLocalFile(
            $path,
            "file.txt"
        );
        $this->assertEquals($result['Filename'], "file.txt");
    }


    /**
     * Scan a blocked file, under limit. Should scan and fail
     */
    public function testBlockScanSizeUnderLimit(): void
    {
        try {
            $file = File::create();
            $contents = TestClient::BLOCK_SCAN_STRING;
            $size = strlen($contents);
            $limit = ($size + 1);
            Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', $limit);
            $result = $file->setFromString(
                $contents,
                "block.txt"
            );
            $this->assertEmpty($result);
        } catch (\Exception $exception) {
            $this->checkValidationResult($exception);
        } finally {
            // Clean up
            /** @phpstan-ignore variable.undefined */
            if ($file) {
                $file->deleteFile();
            }
        }
    }


    /**
     * Scan a blocked file, over limit. Should not fail
     */
    public function testBlockScanSizeOverLimit(): void
    {
        try {
            $file = File::create();
            $contents = TestClient::BLOCK_SCAN_STRING;
            $size = strlen($contents);
            $limit = ($size - 1);
            Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', $limit);
            $result = $file->setFromString(
                $contents,
                "block.txt"
            );
            $this->assertEquals($result['Filename'], "block.txt");
        } catch (\Exception) {
            /** @phpstan-ignore method.alreadyNarrowedType */
            $this->assertFalse(true, "scan should not fail");
        } finally {
            // Clean up
            /** @phpstan-ignore variable.undefined */
            if ($file) {
                $file->deleteFile();
            }
        }
    }

    /**
     * Scan a blocked file, over limit set of 0. Should not fail
     */
    public function testBlockScanSizeZero(): void
    {
        try {
            $file = File::create();
            $contents = TestClient::BLOCK_SCAN_STRING;
            $size = strlen($contents);
            Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', 0);
            $result = $file->setFromString(
                $contents,
                "block.txt"
            );
            $this->assertEquals($result['Filename'], "block.txt");
        } catch (\Exception) {
            /** @phpstan-ignore method.alreadyNarrowedType */
            $this->assertFalse(true, "scan should not fail");
        } finally {
            // Clean up
            /** @phpstan-ignore variable.undefined */
            if ($file) {
                $file->deleteFile();
            }
        }
    }

    /**
     * Scan a blocked file, over limit set of null. Should fail.
     */
    public function testBlockScanSizeNull(): void
    {
        try {
            $file = File::create();
            $contents = TestClient::BLOCK_SCAN_STRING;
            $size = strlen($contents);
            Config::modify()->set(TestScanningBackend::class, 'bypass_over_size_bytes', null);
            $result = $file->setFromString(
                $contents,
                "block.txt"
            );
            $this->assertEmpty($result);
        } catch (\Exception $exception) {
            $this->checkValidationResult($exception);
        } finally {
            // Clean up
            /** @phpstan-ignore variable.undefined */
            if ($file) {
                $file->deleteFile();
            }
        }
    }
}
