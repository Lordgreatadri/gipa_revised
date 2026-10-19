<?php

namespace App\Modules\Assistant\Application\Tools;

use App\Modules\Assistant\Application\Contracts\AssistantTool;

abstract class AbstractTool implements AssistantTool
{
    public function matches(string $question): bool
    {
        $haystack = mb_strtolower($question);

        foreach ($this->triggers() as $trigger) {
            if (str_contains($haystack, mb_strtolower($trigger))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    abstract protected function triggers(): array;
}
