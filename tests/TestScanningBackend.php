<?php

namespace NSWDPC\AssetScan\Tests;

use NSWDPC\AssetScan\Backend;
use NSWDPC\AssetScan\BackendResponse;

/**
 * test backend to speak with the test client
 */
class TestScanningBackend extends Backend
{

    /**
     * Create test client
     */
    public function createClient() {
        if($this->client) {
            return $this->client;
        }
        $this->client = new TestClient();
    }

    /**
     * Create and return a response based on the specific result object
     * @param object $result
     */
    protected function createResponse(object $result) : BackendResponse {
        return new BackendResponse($result->isFound(), $result->isOk(), $result->getReason(), $result->getId());
    }

    /**
     * Scan a file based on a path
     */
    public function scanFile(string $path) : BackendResponse {
        return $this->createResponse( $this->client->scanFile($path) );
    }

    /**
     * Scan a resource pointer
     */
    public function scanResource($resource) : BackendResponse{
        if(!is_resource($resource)) {
            throw new \InvalidArgumentException("Resource value passed is not a resource");
        }
        return $this->createResponse( $this->client->scanResource($resource, $this->getMaxChunkSize() ) );
    }

    /**
     * Scan a string
     */
    public function scanStream(string $contents) : BackendResponse {
        return $this->createResponse( $this->client->scanStream($contents, $this->getMaxChunkSize() ) );
    }

    // Not implemented for this test
    public function multiScanFile(string $path) : BackendResponse {
        throw new \Exception("multiScanFile not implemented");
    }

    public function contScan(string $path) : BackendResponse {
        throw new \Exception("contScan not implemented");
    }

}
