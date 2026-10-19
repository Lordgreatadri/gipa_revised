<?php

namespace Database\Seeders;

use App\Modules\Assistant\Application\KnowledgeIndexer;
use App\Modules\Assistant\Application\PlatformKnowledge;
use App\Modules\Assistant\Domain\Models\AssistantDocument;
use Illuminate\Database\Seeder;

class AssistantKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PlatformKnowledge::documents() as $document) {
            $record = AssistantDocument::query()->firstOrNew(['slug' => $document['slug']]);
            $record->forceFill([
                'title' => $document['title'],
                'category' => $document['category'],
                'audience' => $document['audience'],
                'summary' => $document['summary'],
                'body' => $document['body'],
                'source_type' => 'seed',
                'is_published' => true,
            ])->save();
        }

        app(KnowledgeIndexer::class)->reindexAll();
    }
}
