<?php

namespace App;

trait ApiResponseTrait
{
    protected function successResponse($data, int $httpResponseCode)
    {
        return response()->json([
            'success'    => true,
            'message'    => null,
            'data'       => $data,
            'errors'     => null,
        ], $httpResponseCode);
    }

    protected function errorResponse(string $message, int $httpResponseCode)
    {
        return response()->json([
            'success'    => false,
            'message'    => $message ?? null,
            'data'       => null,
        ], $httpResponseCode);
    }
}
