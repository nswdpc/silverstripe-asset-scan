<?php

namespace NSWDPC\AssetScan;

use SilverStripe\Assets\Flysystem\FlysystemAssetStore;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Core\Config\Config;

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
    #[\Override]
    public function setFromStream($stream, $filename, $hash = null, $variant = null, $config = [])
    {
        try {
            if (!is_resource($stream)) {
                throw new \InvalidArgumentException("The stream argument is not a valid resource");
            }

            // Scanning will throw a VirusFoundException or \Exception on failure
            $backend = Backend::create();
            $limit = Config::inst()->get($backend::class, 'bypass_over_size_bytes');
            if (is_null($limit)) {
                $response = $backend->scanResource($stream);
            } else {
                // check size
                $result = fstat($stream);
                $size = $result['size'] ?? 0;
                if ($size <= $limit) {
                    $response = $backend->scanResource($stream);
                } else {
                    Logger::log("(AssetScan) bypass scan, stream ({$size}) > limit {$limit}", "INFO");
                }
            }

            // Handle default file operation
            return parent::setFromStream($stream, $filename, $hash, $variant, $config);
        } catch (\Exception $exception) {
            // Catch the specific exception and rethrow it as a validation exception
            // for uploaders to catch
            throw ValidationException::create(
                ValidationResult::create()->addError(
                    _t('AssetScan.FILE_COULD_NOT_BE_ACCEPTED', 'The file could not be accepted'),
                    ValidationResult::TYPE_ERROR,
                    ($exception instanceof VirusFoundException ? VirusFoundException::SCAN_FAIL_VALIDATION_CODE : VirusFoundException::GENERAL_ERROR_VALIDATION_CODE)
                )
            );
        } finally {
            // Ensure pointer is closed, if it exists
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

}
