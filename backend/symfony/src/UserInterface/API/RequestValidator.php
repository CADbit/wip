<?php

declare(strict_types=1);

namespace App\UserInterface\API;

use Symfony\Component\HttpFoundation\Request;

class RequestValidator
{
    /**
     * @param array<string, mixed> $data
     * @param list<string> $requiredFields
     * @return array<string, string>|null Returns validation errors or null if valid
     */
    public static function validateRequiredFields(array $data, array $requiredFields): ?array
    {
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                $missingFields[$field] = "Pole '$field' jest wymagane";
            }
        }

        return ! empty($missingFields) ? $missingFields : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function parseJsonRequest(Request $request): ?array
    {
        $data = json_decode($request->getContent(), true);

        return is_array($data) ? $data : null;
    }
}
