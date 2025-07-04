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
    #[\Override]
    public function createClient()
    {
        if($this->client) {
            return $this->client;
        }

        $this->client = new TestClient();
        return null;
    }

    /**
     * Create and return a response based on the specific result object
     */
    #[\Override]
    protected function createResponse(object $result) : BackendResponse
    {
        return new BackendResponse($result->isFound(), $result->isOk(), $result->getReason(), $result->getId());
    }

    /**
     * Scan a file based on a path
     */
    #[\Override]
    public function scanFile(string $path) : BackendResponse
    {
        return $this->createResponse($this->client->scanFile($path));
    }

    /**
     * Scan a resource pointer
     */
    #[\Override]
    public function scanResource($resource) : BackendResponse
    {
        if(!is_resource($resource)) {
            throw new \InvalidArgumentException("Resource value passed is not a resource");
        }

        return $this->createResponse($this->client->scanResource($resource, $this->getMaxChunkSize()));
    }

    /**
     * Scan a string
     */
    #[\Override]
    public function scanStream(string $contents) : BackendResponse
    {
        return $this->createResponse($this->client->scanStream($contents, $this->getMaxChunkSize()));
    }

    // Not implemented for this test
    #[\Override]
    public function multiScanFile(string $path) : BackendResponse
    {
        throw new \Exception("multiScanFile not implemented");
    }

    #[\Override]
    public function contScan(string $path) : BackendResponse
    {
        throw new \Exception("contScan not implemented");
    }

}
