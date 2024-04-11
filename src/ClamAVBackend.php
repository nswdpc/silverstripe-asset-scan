<?php

namespace NSWDPC\AssetScan;

use Xenolope\Quahog\Client;
use Xenolope\Quahog\Result;

/**
 * ClamAV backend for scanning a file and returning a response
 */
class ClamAVBackend extends Backend
{

    /**
     * @config
     * @var string
     */
    private static $address = "";

    /**
     * Create the client for the backend
     */
    public function createClient() {
        if($this->client) {
            return $this->client;
        }
        $address = self::config()->get('address');
        if(!$address) {
            throw new \RuntimeException( _t("AssetScan.NO_ADDRESS_PROVIDED", "No unix socket or TCP socket address provided for ClamAV scanner") );
        }
        $socket = (new \Socket\Raw\Factory())->createClient( $address );
        $this->client = new \Xenolope\Quahog\Client(
            $socket,
            $this->getSocketTimeout(),
            PHP_NORMAL_READ
        );
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
     * Scan a file based on a path
     */
    public function multiScanFile(string $path) : BackendResponse {
        return $this->createResponse( $this->client->multiscanFile($path) );
    }

    /**
     * Scan a file based on a path
     */
    public function contScan(string $path) : BackendResponse {
        return $this->createResponse( $this->client->contScan($path) );
    }

    /**
     * Scan a resource pointer
     */
    public function scanResource($resource) : BackendResponse{
        if(!is_resource($resource)) {
            throw new \InvalidArgumentException("Resource value passed is not a resource");
        }
        return $this->createResponse( $this->client->scanResourceStream($resource, $this->getMaxChunkSize() ) );
    }

    /**
     * Scan a string
     */
    public function scanStream(string $contents) : BackendResponse {
        return $this->createResponse( $this->client->scanStream($contents, $this->getMaxChunkSize() ) );
    }

}
