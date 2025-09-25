<?php

if (!function_exists('api_response')) {
    function api_response($data = null, $message = 'success', $code = 200, $success = true)
    {
        $response = [
            'success' => $success,
            'code' => $code,
            'msg' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }
}
