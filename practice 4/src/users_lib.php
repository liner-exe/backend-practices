<?php

function codeToMessage(int $code): string
{
    return match ($code) {
        200 => "OK",
        201 => "Created",
        400 => "Bad Request",
        404 => "Not Found",
        409 => "Conflict",
        500 => "Internal Server Error",
        default => "Unknown Error ($code)"
    };
}