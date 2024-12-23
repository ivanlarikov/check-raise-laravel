<?php

namespace App\Traits\Response;

trait ResponseTrait
{
    /**
     * @param bool $successful
     * @param string|null $message
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return \Illuminate\Http\JsonResponse
     */
    private function jsonResponse(
        bool $successful = false,
        $payload = null,
        int $status = 200,
        array $headers = [],
        int $options = 0
    ): \Illuminate\Http\JsonResponse
    {
        if( is_string($payload) ){
            $response = [
                'status' => $successful,
                'message' => $payload
            ];
        }else{
            $payload['status'] = $successful;
            $response = $payload;
        }

        return response()->json(
            $response,
            $status,
            $headers,
            $options
        );
    }

    /**
     * @param string|null $message
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonResponseSuccess(
        $payload = null,
        int $status = 200,
        array $headers = [],
        int $options = 0
    ): \Illuminate\Http\JsonResponse
    {
        return $this->jsonResponse(
            true,
            $payload,
            $status,
            $headers,
            $options
        );
    }

    /**
     * @param string|null $message
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonResponseFail(
        $payload = null,
        int $status = 200,
        array $headers = [],
        int $options = 0
    ): \Illuminate\Http\JsonResponse
    {
        return $this->jsonResponse(
            false,
            $payload,
            $status,
            $headers,
            $options
        );
    }
}
