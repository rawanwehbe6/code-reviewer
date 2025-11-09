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
    private static function callOpenAiAndParse(
        string $apiKey,
        string $code,
        string $file,
        ?string $language
    ): array {
        $model = 'gpt-4.1-mini';

        $prompt = self::buildPrompt($code, $file, $language);

        $payload = [
            'model' => $model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a strict code review assistant. '
                        . 'Reply ONLY with JSON that matches the requested schema.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: ' . 'Bearer ' . $apiKey,
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('OpenAI request failed: ' . $err);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('OpenAI HTTP status ' . $status);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid OpenAI JSON response');
        }

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        if (!is_string($content)) {
            throw new \RuntimeException('Missing content in OpenAI reply');
        }

        $json = json_decode($content, true);
        if (!is_array($json)) {
            throw new \RuntimeException('OpenAI content is not valid JSON');
        }

        $issues = $json['issues'] ?? null;
        if (!is_array($issues)) {
            throw new \RuntimeException('Missing "issues" array in OpenAI JSON');
        }

        return $issues;
    }
    private static function buildPrompt(string $code, string $file, ?string $language): string
    {
        $langPart = $language ? "Language: {$language}\n" : '';

        return <<<PROMPT
You are reviewing a single source file.

File name: {$file}
{$langPart}
Code:
```text
{$code}
PROMPT;
    }
}
