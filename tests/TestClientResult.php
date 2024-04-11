<?php

namespace NSWDPC\AssetScan\Tests;

use NSWDPC\AssetScan\VirusFoundException;

/**
 * A client result class, simply stores the result of a scan by a client
 */
class TestClientResult {

    protected $isFound = false;
    protected $isOk = false;
    protected $reason = '';
    protected $id = '';

    public function __construct(bool $isFound, bool $isOk, string $reason, string $id) {
        $this->isFound = $isFound;
        $this->isOk = $isOk;
        $this->reason = $reason;
        $this->id = $id;
    }

    public function isFound() : bool {
        return $this->isFound;
    }

    public function isOk() : bool {
        return $this->isOk;
    }

    public function getReason() : string {
        return $this->reason;

    }

    public function getId() : string {
        return $this->id;
    }
}
