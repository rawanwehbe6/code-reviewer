<?php

declare(strict_types=1);

require_once __DIR__ . '/AiGenerator.php';
require_once __DIR__ . '/Validator.php';

class ReviewService
{
    public static function review(string $code, ?string $filename, ?string $language): array
    {
        $file = $filename ?? 'unknown';

        $rawIssues = AiGenerator::generateIssues($code, $file, $language);

        $validated = Validator::validateIssues($rawIssues);

        return $validated;
    }
}
