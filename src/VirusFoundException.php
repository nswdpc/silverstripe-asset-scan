<?php

namespace NSWDPC\AssetScan;

/**
 * Specific exception for a scan returned a virus found result
 */
class VirusFoundException extends \Exception
{

    public const SCAN_FAIL_VALIDATION_CODE = 'VirusFoundException';

    public const GENERAL_ERROR_VALIDATION_CODE = 'VirusFoundException';

    /**
     * Store the response of the scan
     */
    protected $response;

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
