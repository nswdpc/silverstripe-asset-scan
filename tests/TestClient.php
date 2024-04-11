<?php

namespace NSWDPC\AssetScan\Tests;

use NSWDPC\AssetScan\VirusFoundException;

/**
 * Test client handles test scans
 */
class TestClient
{

    /**
     * A string representing something that should be blocked
     * @var string
     */
    const BLOCK_SCAN_STRING = 'SCAN_BLOCK';

    /**
     * A string representing something that should not be blocked
     * @var string
     */
    const OK_SCAN_STRING = 'SCAN_OK';

    /**
     * Scan file on local path
     */
    public function scanFile(string $path)
    {
        $contents = file_get_contents($path);
        return $this->scanStream($contents);
    }

    /**
     * Scan a resource in chunks
     */
    public function scanResource($resource, int $max_chunk_size = 8192)
    {
        if(!is_resource($resource)) {
            throw new \InvalidArgumentException("Resource argument is not a resource");
        }
        $contents = '';
        while (!feof($resource)) {
            $contents .= fread($resource, $max_chunk_size);
        }
        return $this->scanStream($contents, $max_chunk_size);
    }

    public function scanStream(string $contents, int $max_chunk_size = 8192)
    {
        if($contents == self::BLOCK_SCAN_STRING) {
            return new TestClientResult(true, false, "BLOCK_SCAN_STRING signature found", "test-fail");
        } else {
            return new TestClientResult(false, true, "OK", "test-ok");
        }
    }

}
