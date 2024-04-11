<?php

namespace NSWDPC\AssetScan;

/**
 * Specific exception for a scan returned a virus found result
 */
class VirusFoundException extends \Exception
{

    /**
     * Store the response of the scan
     */
    protected $response = null;

    /**
     * Set a response
     */
    public function setBackendResponse(BackendResponse $response)
    {
        $this->response = $response;
        Logger::log(
            json_encode([
                "Result" => "VirusFoundException",
                "Reason" => $this->response->getReason(),
                "URI" => $_SERVER['REQUEST_URI'] ?? null
            ]),
            "WARNING"
        );
    }

}
