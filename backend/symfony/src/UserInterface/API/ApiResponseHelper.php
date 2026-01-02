<?php

declare(strict_types=1);

namespace App\UserInterface\API;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseHelper
{
    /**
     * Zwraca ujednolicony format odpowiedzi sukcesu
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        $response = [];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return new JsonResponse($response, $statusCode);
    }

    /**
     * Zwraca ujednolicony format odpowiedzi błędu
     */
    public static function error(
        string $message,
        array $errors = [],
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        $response = [
            'error' => true,
            'message' => $message,
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return new JsonResponse($response, $statusCode);
    }

    /**
     * Zwraca ujednolicony format odpowiedzi błędu walidacji
     */
    public static function validationError(
        string $message,
        array $fieldErrors = []
    ): JsonResponse {
        return self::error($message, $fieldErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

