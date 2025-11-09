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
}
