<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Lease;
use App\Models\LeaseTemplate;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestUpdate;
use App\Models\Property;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentsController extends Controller
{
    /** File library categories (lease PDFs use Lease templates tab, not this list). */
    private const DOC_TYPES = ['inspection', 'insurance', 'compliance', 'receipt', 'other'];

    private const LOCATION_MAP = [
        'property'    => Property::class,
        'lease'       => Lease::class,
        'maintenance' => MaintenanceRequest::class,
        'followup'    => MaintenanceRequestUpdate::class,
    ];

    public function index(Request $request)
    {
        $user = Auth::user();

        $tab = $request->query('tab', 'documents');
        if (! in_array($tab, ['leases', 'documents'], true)) {
            $tab = 'documents';
        }

        if ($tab === 'leases') {
            $templates = LeaseTemplate::query()
                ->where('landlord_id', $user->id)
                ->orderByDesc('updated_at')
                ->get();

            $templateStats = [
                'total'      => $templates->count(),
                'master'     => $templates->where('lease_type', 'master')->count(),
                'sub_lease'  => $templates->where('lease_type', 'sub_lease')->count(),
                'short_term' => $templates->where('lease_type', 'short_term')->count(),
            ];

            return view('dashboard.documents.index', [
                'tab'            => 'leases',
                'templates'      => $templates,
                'templateStats'  => $templateStats,
            ]);
        }

        $validated = $request->validate([
            'q'         => 'nullable|string|max:255',
            'type'      => 'nullable|in:all,'.implode(',', self::DOC_TYPES),
            'location'  => 'nullable|in:all,property,lease,maintenance,followup',
            'sort'      => 'nullable|in:date,name,size',
            'dir'       => 'nullable|in:asc,desc',
        ]);

        $qSearch = $validated['q'] ?? '';
        $typeFilter = $validated['type'] ?? 'all';
        $locationFilter = $validated['location'] ?? 'all';
        $sort = $validated['sort'] ?? 'date';
        $dir = ($validated['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = Document::query()
            ->where('type', '!=', 'lease')
            ->with(['uploadedBy'])
            ->with(['documentable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Lease::class => ['property'],
                    MaintenanceRequest::class => ['lease.property'],
                    MaintenanceRequestUpdate::class => ['maintenanceRequest.lease.property'],
                ]);
            }])
            ->accessibleForUser($user);

        if ($qSearch !== '') {
            $query->where('original_filename', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $qSearch).'%');
        }

        if ($typeFilter !== 'all') {
            $query->where('type', $typeFilter);
        }

        if ($locationFilter !== 'all' && isset(self::LOCATION_MAP[$locationFilter])) {
            $query->where('documentable_type', self::LOCATION_MAP[$locationFilter]);
        }

        match ($sort) {
            'name' => $query->orderBy('original_filename', $dir),
            'size' => $query->orderBy('size_bytes', $dir),
            default => $query->orderBy('created_at', $dir),
        };

        $documents = $query->paginate(30)->withQueryString();

        return view('dashboard.documents.index', [
            'tab'              => 'documents',
            'documents'        => $documents,
            'q'                => $qSearch,
            'typeFilter'       => $typeFilter,
            'locationFilter'   => $locationFilter,
            'sort'             => $sort,
            'dir'              => $dir,
            'docTypes'         => self::DOC_TYPES,
        ]);
    }
}
