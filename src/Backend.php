<?php

namespace NSWDPC\AssetScan;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

/**
 * Backend abstract
 */
abstract class Backend
{

    use Configurable;
    use Injectable;

    /**
     * @config
     * @var int
     */
    private static $max_chunk_size = 1048576;

    /**
     * @config
     * @var int
     */
    private static $socket_timeout = 30;

    /**
     * @var int
     */
    const DEFAULT_MAX_CHUNK_SIZE = 1048576;

    /**
     * @var int
     */
    const DEFAULT_SOCKET_TIMEOUT = 30;

    /**
     * @var mixed
     */
    protected $client;

    /**
     */
    public function __construct() {
        $this->createClient();
    }

    /**
     * Return configured max chunk size or default value
     */
    public function getSocketTimeout() : int {
        $socketTimeout = self::config()->get('socket_timeout');
        if(!is_int($socketTimeout) || $socketTimeout < 0) {
            $socketTimeout = self::DEFAULT_SOCKET_TIMEOUT;
        }
        return $socketTimeout;
    }

    /**
     * Return configured max chunk size or default value
     */
    public function getMaxChunkSize() : int {
        $maxChunkSize = self::config()->get('max_chunk_size');
        if(!is_int($maxChunkSize) || $maxChunkSize < 0) {
            $maxChunkSize = self::DEFAULT_MAX_CHUNK_SIZE;
        }
        return $maxChunkSize;
    }

    /**
     * Creating a client
     */
    abstract public function createClient();

    /**
     * Allow access to the client
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * Return a response based on a result object
     * @param object $result response result of a scan
     */
    abstract protected function createResponse(object $result) : BackendResponse;

    // Utility methods
    abstract public function scanFile(string $path) : BackendResponse;
    abstract public function multiScanFile(string $path) : BackendResponse;
    abstract public function contScan(string $path) : BackendResponse;
    abstract public function scanResource($resource) : BackendResponse;
    abstract public function scanStream(string $contents) : BackendResponse;

}
