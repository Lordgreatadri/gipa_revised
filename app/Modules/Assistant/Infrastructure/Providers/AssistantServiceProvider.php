<?php

namespace App\Modules\Assistant\Infrastructure\Providers;

use App\Modules\Assistant\Application\AssistantService;
use App\Modules\Assistant\Application\Contracts\ChatProvider;
use App\Modules\Assistant\Application\Contracts\EmbeddingProvider;
use App\Modules\Assistant\Application\Guardrails;
use App\Modules\Assistant\Application\KnowledgeRetriever;
use App\Modules\Assistant\Application\Providers\OfflineChatProvider;
use App\Modules\Assistant\Application\Providers\OfflineEmbeddingProvider;
use App\Modules\Assistant\Application\Providers\OpenAiChatProvider;
use App\Modules\Assistant\Application\Tools\ActiveOpportunityTool;
use App\Modules\Assistant\Application\Tools\CatalogueTool;
use App\Modules\Assistant\Application\Tools\ContactChannelsTool;
use App\Modules\Assistant\Application\Tools\OfficeHoursTool;
use App\Modules\Assistant\Application\Tools\PublicJourneyTool;
use App\Modules\Assistant\Application\Tools\UnavailableCapabilityTool;
use App\Modules\Shared\Infrastructure\RateLimits\ThrottleSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AssistantServiceProvider extends ServiceProvider
{
    /** @var list<class-string> */
    private const TOOLS = [
        PublicJourneyTool::class,
        UnavailableCapabilityTool::class,
        OfficeHoursTool::class,
        ContactChannelsTool::class,
        CatalogueTool::class,
        ActiveOpportunityTool::class,
    ];

    public function register(): void
    {
        $this->app->bind(EmbeddingProvider::class, OfflineEmbeddingProvider::class);

        $this->app->bind(ChatProvider::class, function ($app): ChatProvider {
            $driver = (string) config('assistant.driver', 'offline');
            $key = (string) config('assistant.providers.openai.api_key');

            if ($driver === 'openai' && $key !== '') {
                return $app->make(OpenAiChatProvider::class);
            }

            return $app->make(OfflineChatProvider::class);
        });

        $this->app->tag(self::TOOLS, 'assistant.tools');

        $this->app->bind(AssistantService::class, function ($app): AssistantService {
            return new AssistantService(
                $app->make(ChatProvider::class),
                $app->make(KnowledgeRetriever::class),
                $app->make(Guardrails::class),
                $app->tagged('assistant.tools'),
            );
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(dirname(__DIR__, 2).'/Presentation/Views', 'assistant');

        RateLimiter::for('assistant', fn (Request $request) => ThrottleSpec::limit(
            (string) config('assistant.rate_limit', '15,1'),
            ThrottleSpec::bucketFor($request),
        ));
    }
}
