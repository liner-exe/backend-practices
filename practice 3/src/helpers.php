<?php

function sendResponse(int $code, ?string $message = null, mixed $data = null): void
{
    http_response_code($code);

    $response = [
        "code" => $code
    ];

    if ($message !== null) {
        $response["message"] = $message;
    }

    if ($data !== null) {
        $response["data"] = $data;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}