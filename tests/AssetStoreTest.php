<?php

namespace NSWDPC\AssetScan\Tests;

use League\Flysystem\Filesystem;
use NSWDPC\AssetScan\ScanningFlysystemAssetStore;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Assets\Flysystem\FlysystemAssetStore;
use SilverStripe\Assets\Flysystem\PublicAssetAdapter;
use SilverStripe\Assets\Flysystem\ProtectedAssetAdapter;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class AssetStoreTest extends SapphireTest
{

    protected $usesDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();
        // Set up scanning asset store using default config
        $spec = Injector::inst()->getServiceSpec(AssetStore::class);
        $spec['class'] = ScanningFlysystemAssetStore::class;
        Injector::inst()->load($spec);
    }

    public function testAssetStoreConfiguration()
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
}
