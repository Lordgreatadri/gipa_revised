<?php

namespace App\Modules\Assistant\Presentation\Http\Controllers;

use App\Modules\Assistant\Application\DocumentSlug;
use App\Modules\Assistant\Application\KnowledgeIndexer;
use App\Modules\Assistant\Domain\Models\AssistantDocument;
use App\Modules\Assistant\Presentation\Http\Requests\SaveAssistantDocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssistantKnowledgeController
{
    public function index(): View
    {
        return view('assistant::knowledge.index', [
            'documents' => AssistantDocument::query()->latest('updated_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('assistant::knowledge.form', ['document' => new AssistantDocument]);
    }

    public function store(SaveAssistantDocumentRequest $request, KnowledgeIndexer $indexer): RedirectResponse
    {
        $document = new AssistantDocument;
        $this->fill($document, $request);
        $document->save();
        $indexer->reindex($document);

        return redirect()->route('admin.assistant.knowledge.index')->with('status', 'The assistant document was saved and indexed.');
    }

    public function edit(AssistantDocument $document): View
    {
        return view('assistant::knowledge.form', ['document' => $document]);
    }

    public function update(SaveAssistantDocumentRequest $request, AssistantDocument $document, KnowledgeIndexer $indexer): RedirectResponse
    {
        $this->fill($document, $request);
        $document->save();
        $indexer->reindex($document);

        return redirect()->route('admin.assistant.knowledge.index')->with('status', 'The assistant document was updated and indexed.');
    }

    public function destroy(AssistantDocument $document): RedirectResponse
    {
        $document->delete();

        return redirect()->route('admin.assistant.knowledge.index')->with('status', 'The assistant document was removed from answers.');
    }

    public function reindex(KnowledgeIndexer $indexer): RedirectResponse
    {
        $indexer->reindexAll();

        return redirect()->route('admin.assistant.knowledge.index')->with('status', 'Published assistant documents were indexed again.');
    }

    private function fill(AssistantDocument $document, SaveAssistantDocumentRequest $request): void
    {
        $title = (string) $request->string('title');

        $document->forceFill([
            'title' => $title,
            'slug' => $document->exists && filled($document->slug)
                ? $document->slug
                : DocumentSlug::fromTitle($title, $document->exists ? $document->id : null),
            'category' => (string) $request->string('category'),
            'audience' => (string) $request->string('audience'),
            'summary' => $request->input('summary'),
            'body' => (string) $request->string('body'),
            'is_published' => $request->boolean('is_published'),
            'source_type' => $document->exists ? $document->source_type : 'manual',
            'updated_by' => $request->user()?->id,
        ]);
    }
}
