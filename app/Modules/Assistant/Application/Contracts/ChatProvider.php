<?php

namespace App\Modules\Assistant\Application\Contracts;

use App\Modules\Assistant\Application\Data\AssistantAnswer;
use App\Modules\Assistant\Application\Data\AssistantPrompt;

interface ChatProvider
{
    public function generate(AssistantPrompt $prompt): AssistantAnswer;

    public function name(): string;

    public function model(): string;
}
