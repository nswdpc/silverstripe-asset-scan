<?php

namespace NSWDPC\AssetScan;

/**
 * Backend Response
 */
class BackendResponse
{

    /**
     * Create a response based on whatever the backend in use returned
     * @param bool $isFound whether the backend detected a virus
     * @param bool $success whether the backend had a successful scan
     * @param string $reason the backend reason string, optional
     * @param string $id backend session id for the scan, optional
     */
    public function __construct(protected bool $isFound, protected bool $success, protected ?string $reason = null, protected ?string $id = null)
    {

        if($this->isFound) {
            // Create and throw exception immediately
            $exception = new VirusFoundException(_t('AssetScan.VIRUS_FOUND', 'Virus found'));
            $exception->setBackendResponse($this);
            throw $exception;
        }

        if(!$this->success) {
            $msg = "Scan failed: " . ($this->reason ?: '');
            Logger::log($msg, "ERROR");
            throw new \Exception($msg);
        }
    }

    /**
     * Whether virus was found
     */
    public function isFound() : bool
    {
        return $this->isFound;
    }

    /**
     * Response valid?
     */
    public function isValid() : bool
    {
        return $this->success;
    }

    /**
     * Response reason
     */
    public function getReason() : ?string
    {
        return $this->reason;
    }

    /**
     * Response scan id
     */
    public function getId() : ?string
    {
        return $this->id;
    }

}
