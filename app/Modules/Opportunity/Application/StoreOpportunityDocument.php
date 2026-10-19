<?php

namespace App\Modules\Opportunity\Application;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Enums\DocumentCategory;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use App\Modules\Opportunity\Domain\Models\OpportunityDocument;
use App\Modules\Opportunity\Domain\OpportunityAuditActions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class StoreOpportunityDocument
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly DistrictScope $scope,
    ) {}

    public function store(User $actor, Opportunity $opportunity, UploadedFile $file, DocumentCategory $category): OpportunityDocument
    {
        $mayAttach = $actor->can(AccessPermissions::OPPORTUNITIES_UPDATE)
            || ($actor->can(AccessPermissions::OPPORTUNITIES_CREATE) && $opportunity->workflow_state->isEditable());

        if (! $mayAttach || ! $this->scope->canSee($actor, $opportunity)) {
            throw new AuthorizationException;
        }

        if (! $opportunity->workflow_state->isEditable() && ! $actor->can(AccessPermissions::OPPORTUNITIES_UPDATE)) {
            throw ValidationException::withMessages([
                'document' => 'Supporting documents can be added while the opportunity is still a draft.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'];

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'document' => 'Upload a PDF, Word, Excel or image file.',
            ]);
        }

        $storedName = (string) Str::uuid().'.'.$extension;
        $path = $file->storeAs('opportunities/'.$opportunity->uuid, $storedName, 'local');

        if ($path === false) {
            throw ValidationException::withMessages([
                'document' => 'The file could not be stored.',
            ]);
        }

        $document = OpportunityDocument::query()->create([
            'opportunity_id' => $opportunity->id,
            'category' => $category,
            'original_name' => Str::limit($file->getClientOriginalName(), 180, ''),
            'storage_path' => $path,
            'mime_type' => (string) $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
            'checksum' => hash_file('sha256', $file->getRealPath()) ?: '',
            'uploaded_by' => $actor->id,
        ]);

        $this->audit->record(
            AuditEntry::for(OpportunityAuditActions::DOCUMENT_STORED, $opportunity)
                ->causedBy($actor)
                ->withMetadata([
                    'reference' => $opportunity->reference,
                    'category' => $category->value,
                    'size' => $document->size,
                ])
        );

        return $document;
    }
}
