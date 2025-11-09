<?php

declare(strict_types=1);

class AiGenerator
{
    public static function generateIssues(string $code, string $file, ?string $language): array
    {
        $apiKey = getenv('OPENAI_API_KEY');

        if (!$apiKey) {
            return self::fallbackIssues($code, $file, $language);
        }

        try {
            $issues = self::callOpenAiAndParse($apiKey, $code, $file, $language);

            if (!is_array($issues) || empty($issues)) {
                return self::fallbackIssues($code, $file, $language);
            }

            return $issues;
        } catch (\Throwable $e) {
            return self::fallbackIssues($code, $file, $language);
        }
    }

    private static function fallbackIssues(string $code, string $file, ?string $language): array
    {
        $lowerCode = strtolower($code);

        $looksLikeDataSave =
            str_contains($lowerCode, 'save_to_db') ||
            str_contains($lowerCode, 'insert')    ||
            str_contains($lowerCode, 'update')    ||
            str_contains($lowerCode, 'request')   ||
            str_contains($lowerCode, 'input(');

        $mentionsValidation = str_contains($lowerCode, 'validat'); // "validate", "validation", etc.

        if ($looksLikeDataSave && !$mentionsValidation) {
            return [[
                'severity'   => 'high',
                'file'       => $file,
                'issue'      => 'No input validation',
                'suggestion' => 'Validate payload before saving',
            ]];
        }

        return [];
    }
}
