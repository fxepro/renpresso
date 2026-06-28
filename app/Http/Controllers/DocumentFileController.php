<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestUpdate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentFileController extends Controller
{
    public function show(Document $document)
    {
        $document->loadMissing('documentable');

        $entity = $document->documentable;

        if (! $entity) {
            abort(404);
        }

        $allowed = match (true) {
            $entity instanceof MaintenanceRequestUpdate => $this->canViewMaintenanceRequestUpdate($entity),
            $entity instanceof MaintenanceRequest => Gate::allows('view', $entity),
            $entity instanceof Lease => Gate::allows('view', $entity),
            $entity instanceof Property => Gate::allows('view', $entity),
            default => false,
        };

        if (! $allowed) {
            abort(403);
        }

        if (! Storage::disk($document->disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->disk)->response(
            $document->path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type]
        );
    }

    private function canViewMaintenanceRequestUpdate(MaintenanceRequestUpdate $update): bool
    {
        $update->loadMissing('maintenanceRequest');

        return $update->maintenanceRequest
            && Gate::allows('view', $update->maintenanceRequest);
    }
}
