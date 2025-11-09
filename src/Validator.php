<?php

declare(strict_types=1);

require_once __DIR__ . '/Config.php';

class Validator
{
    public static function validateIssues(array $issues): array
    {
        $validated = [];

        foreach ($issues as $i => $issue) {
            if (!is_array($issue)) {
                error_log("Skipping issue #{$i}: not an array");
                continue;
            }

            $normalized = self::validateSingleIssue($issue);
            if ($normalized !== null) {
                $validated[] = $normalized;
            }
        }

        return $validated;
    }

    private static function validateSingleIssue(array $issue): ?array
    {
        $requiredFields = ['severity', 'file', 'issue', 'suggestion'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $issue)) {
                error_log("Invalid issue: missing field '{$field}'");
                return Config::STRICT_SCHEMA_MODE ? null : self::fillMissingFields($issue);
            }
        }

        $severity = strtolower(trim((string) $issue['severity']));
        if (!in_array($severity, Config::ALLOWED_SEVERITIES, true)) {
            error_log("Invalid severity '{$severity}', normalizing to 'low'");
            $severity = 'low';
        }

        return [
            'severity'   => $severity,
            'file'       => trim((string) $issue['file']),
            'issue'      => trim((string) $issue['issue']),
            'suggestion' => trim((string) $issue['suggestion']),
        ];
    }
}
