<?php

namespace App\Modules\Assistant\Application;

use App\Modules\Assistant\Application\Support\Text;

/**
 * The offline driver never follows instructions inside a question. These
 * checks still flag manipulation so a pure attack is deflected and recorded.
 */
final class Guardrails
{
    private const INJECTION_PATTERNS = [
        '/ignore\s+(all\s+)?(previous|prior|above)\s+(instructions|prompts?)/i',
        '/disregard\s+(the\s+)?(system|previous|above)/i',
        '/you\s+are\s+now\s+/i',
        '/reveal\s+(your\s+)?(system\s+)?(prompt|instructions)/i',
        '/(what|show)\s+(is|me)\s+your\s+(system\s+)?prompt/i',
        '/act\s+as\s+(a\s+)?(dan|jailbreak)/i',
        '/pretend\s+(to\s+be|you\s+are)/i',
    ];

    public function sanitize(string $input): string
    {
        $clean = Text::clean($input);
        $limit = (int) config('assistant.guardrails.max_question_length', 1000);

        return mb_substr($clean, 0, $limit);
    }

    public function detectInjection(string $input): bool
    {
        foreach (self::INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $input) === 1) {
                return true;
            }
        }

        return false;
    }
}
