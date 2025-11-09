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
}
