<?php

namespace App\Modules\Opportunity\Domain;

final class OpportunityAuditActions
{
    public const CREATED = 'opportunity.created';

    public const UPDATED = 'opportunity.updated';

    public const TRANSITIONED = 'opportunity.transitioned';

    public const REASSIGNED = 'opportunity.reassigned';

    public const DOCUMENT_STORED = 'opportunity.document_stored';
}
