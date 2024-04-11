<?php

namespace NSWDPC\AssetScan;

use SilverStripe\Assets\Flysystem\FlysystemAssetStore;

/**
 * A FlysystemAssetStore that scans a stream and throws exceptions if the scanning backend
 * finds content that is blocked
 * @author James
 */
class ScanningFlysystemAssetStore extends FlysystemAssetStore
{

    /**
     * Pass file to scanner prior to Flysystem handling
     * @inheritdoc
     * @throws VirusFoundException|\Exception|\InvalidArgumentException
     */
    public function setFromStream($stream, $filename, $hash = null, $variant = null, $config = [])
    {
        try {
            if(!is_resource($stream)) {
                throw new \InvalidArgumentException("The stream argument is not a valid resource");
            }
            // Scanning will throw a VirusFoundException or \Exception on failure
            $response = Backend::create()->scanResource($stream);
            // Handle default file operation
            return parent::setFromStream($stream, $filename, $hash, $variant, $config);
        } catch (\Exception $e) {
            // Rethrow to ensure uploads fail
            throw $e;
        } finally {
            // Ensure pointer is closed, if it exists
            if(is_resource($stream)) {
                fclose($stream);
            }
        }
    }

}
