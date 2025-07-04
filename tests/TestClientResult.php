<?php

namespace NSWDPC\AssetScan\Tests;

/**
 * A client result class, simply stores the result of a scan by a client
 */
class TestClientResult
{
    public function __construct(protected bool $isFound, protected bool $isOk, protected string $reason, protected string $id)
    {
    }

    public function isFound(): bool
    {
        return $this->isFound;
    }

    public function isOk(): bool
    {
        return $this->isOk;
    }

    public function getReason(): string
    {
        return $this->reason;

    }

    public function getId(): string
    {
        return $this->id;
    }
}
