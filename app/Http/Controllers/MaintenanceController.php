<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRequestUpdate;
use App\Models\MaintenanceTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    private const CATEGORIES = ['plumbing', 'electrical', 'heating', 'structural', 'appliance', 'other'];

    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isLandlord() || $user->isMaintenance() || $user->isTenant(), 403);

        if ($user->isLandlord()) {
            $requests = MaintenanceRequest::whereHas('lease.property', fn ($q) => $q->where('landlord_id', $user->id))
                ->with(['lease.property', 'raisedBy', 'assignee', 'maintenanceTeam'])
                ->orderByDesc('created_at')
                ->get();
            $engagedTeams = $user->engagedMaintenanceTeams()->orderBy('name')->get();
            $viewMode = 'landlord';

            return view('dashboard.maintenance.index', compact('requests', 'engagedTeams', 'viewMode'));
        }

        if ($user->isTenant()) {
            $requests = MaintenanceRequest::whereHas('lease', fn ($q) => $q->where('tenant_id', $user->id))
                ->with(['lease.property', 'raisedBy', 'assignee', 'maintenanceTeam'])
                ->orderByDesc('created_at')
                ->get();
            $viewMode = 'tenant';
            $canCreate = $user->can('create', MaintenanceRequest::class);

            return view('dashboard.maintenance.index', compact('requests', 'viewMode', 'canCreate'));
        }

        $team = $user->ownedMaintenanceTeam;
        $linkedCount = $team ? $team->engagedLandlords()->count() : 0;

        $requests = $team
            ? $team->assignedMaintenanceRequestsQuery()
                ->with(['lease.property', 'raisedBy', 'maintenanceTeam'])
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $viewMode = 'staff';

        return view('dashboard.maintenance.index', compact('requests', 'linkedCount', 'viewMode'));
    }

    public function create()
    {
        $this->authorize('create', MaintenanceRequest::class);
        $user = Auth::user();

        $lease = null;
        $leases = collect();

        if ($user->isTenant()) {
            $lease = $user->primaryActiveLease()?->load('property');
            abort_unless($lease, 403, 'You need an active lease to submit a request.');
        } else {
            $leases = Lease::query()
                ->where('status', 'active')
                ->whereHas('property', fn ($q) => $q->where('landlord_id', $user->id))
                ->with('property', 'tenant')
                ->orderByDesc('activated_at')
                ->get();
            abort_unless($leases->isNotEmpty(), 403, 'No active leases to attach a request to.');
        }

        $categories = self::CATEGORIES;

        return view('dashboard.maintenance.create', compact('lease', 'leases', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', MaintenanceRequest::class);
        $user = Auth::user();

        $lease = $this->resolveLeaseForCreate($user, $request);
        abort_unless($lease, 403);

        $validated = $request->validate([
            'category'    => ['required', Rule::in(self::CATEGORIES)],
            'title'       => 'required|string|max:200',
            'description' => 'required|string|max:10000',
            'photos'      => 'nullable|array|max:12',
            'photos.*'    => 'file|image|max:10240',
        ]);

        $mr = MaintenanceRequest::create([
            'lease_id'    => $lease->id,
            'raised_by'   => $user->id,
            'category'    => $validated['category'],
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'status'      => 'submitted',
        ]);

        $this->storePhotos($request, $mr, 'photos');

        return redirect()
            ->route('maintenance.show', $mr)
            ->with('success', 'Maintenance request submitted.');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('view', $maintenanceRequest);

        $maintenanceRequest->load([
            'lease.property',
            'lease.tenant',
            'raisedBy',
            'assignee',
            'maintenanceTeam',
            'documents',
            'followUps.author',
            'followUps.documents',
        ]);

        $canFollowUp = Auth::user()->can('followUp', $maintenanceRequest);
        $canEdit = Auth::user()->can('updateDetails', $maintenanceRequest);
        $canDelete = Auth::user()->can('delete', $maintenanceRequest);

        return view('dashboard.maintenance.show', compact(
            'maintenanceRequest', 'canFollowUp', 'canEdit', 'canDelete'
        ));
    }

    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('updateDetails', $maintenanceRequest);
        $maintenanceRequest->load(['lease.property', 'documents']);

        $categories = self::CATEGORIES;

        return view('dashboard.maintenance.edit', compact('maintenanceRequest', 'categories'));
    }

    public function updateDetails(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('updateDetails', $maintenanceRequest);

        $validated = $request->validate([
            'category'         => ['required', Rule::in(self::CATEGORIES)],
            'title'            => 'required|string|max:200',
            'description'      => 'required|string|max:10000',
            'photos'           => 'nullable|array|max:12',
            'photos.*'         => 'file|image|max:10240',
            'remove_photos'    => 'nullable|array',
            'remove_photos.*'  => 'uuid',
        ]);

        $maintenanceRequest->update([
            'category'    => $validated['category'],
            'title'       => $validated['title'],
            'description' => $validated['description'],
        ]);

        $this->removePhotos($maintenanceRequest, $request->input('remove_photos', []));
        $this->storePhotos($request, $maintenanceRequest, 'photos');

        return redirect()
            ->route('maintenance.show', $maintenanceRequest)
            ->with('success', 'Request updated.');
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('delete', $maintenanceRequest);

        foreach ($maintenanceRequest->documents as $doc) {
            Storage::disk($doc->disk)->delete($doc->path);
            $doc->delete();
        }

        $maintenanceRequest->delete();

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Request deleted.');
    }

    public function storeFollowUp(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('followUp', $maintenanceRequest);

        $validated = $request->validate([
            'body'    => 'nullable|string|max:10000',
            'photos'  => 'nullable|array|max:12',
            'photos.*'=> 'file|image|max:10240',
        ]);

        if (empty(trim($validated['body'] ?? '')) && ! $request->hasFile('photos')) {
            return back()->withErrors(['body' => 'Add a message and/or at least one photo.'])->withInput();
        }

        $update = MaintenanceRequestUpdate::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'user_id'                => Auth::id(),
            'body'                   => isset($validated['body']) ? trim($validated['body']) : null,
        ]);

        $this->storePhotos($request, $update, 'photos');

        return redirect()->route('maintenance.show', $maintenanceRequest)
            ->with('success', 'Update added.');
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('update', $maintenanceRequest);
        $request->validate(['status' => 'required|in:submitted,acknowledged,in_progress,resolved']);
        $maintenanceRequest->update([
            'status'          => $request->status,
            'acknowledged_at' => $maintenanceRequest->acknowledged_at ?? now(),
            'resolved_at'     => $request->status === 'resolved' ? now() : null,
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function assign(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('assign', $maintenanceRequest);
        $request->validate(['maintenance_team_id' => 'nullable|uuid|exists:maintenance_teams,id']);

        $teamId = $request->input('maintenance_team_id');
        $assigneeId = null;

        if ($teamId) {
            $team = MaintenanceTeam::query()->whereKey($teamId)->firstOrFail();
            abort_unless(
                Auth::user()->engagedMaintenanceTeams()->whereKey($teamId)->exists(),
                422,
                'That team is not on your roster.'
            );
            $property = $maintenanceRequest->lease->property;
            abort_unless(
                $team->coversCityCountry($property->city, $property->country_code),
                422,
                'That team does not operate in '.$property->city.', '.$property->country_code.'.'
            );
            $assigneeId = $team->owner_id;
        }

        $maintenanceRequest->update([
            'maintenance_team_id' => $teamId,
            'assignee_id'         => $assigneeId,
            'assigned_at'         => $teamId ? now() : null,
        ]);

        return back()->with(
            'success',
            $teamId
                ? 'Team assigned — they will see this request on their maintenance queue.'
                : 'Assignment cleared.'
        );
    }

    private function resolveLeaseForCreate($user, Request $request): ?Lease
    {
        if ($user->isTenant()) {
            return $user->primaryActiveLease();
        }

        $request->validate(['lease_id' => 'required|uuid|exists:leases,id']);
        $lease = Lease::with('property')->find($request->input('lease_id'));
        abort_unless($lease && $lease->property->landlord_id === $user->id, 403);
        abort_unless($lease->status === 'active', 422, 'Lease must be active.');

        return $lease;
    }

    private function storePhotos(Request $request, MaintenanceRequest|MaintenanceRequestUpdate $entity, string $inputKey): void
    {
        $disk = config('filesystems.default', 'local');
        $dir = $entity instanceof MaintenanceRequest
            ? 'maintenance-requests/'.$entity->id
            : 'maintenance-requests/'.$entity->maintenance_request_id.'/updates/'.$entity->id;

        foreach ($request->file($inputKey, []) as $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store($dir, $disk);
            Document::create([
                'documentable_type' => $entity::class,
                'documentable_id'   => $entity->id,
                'uploaded_by'       => Auth::id(),
                'type'              => 'other',
                'disk'              => $disk,
                'path'              => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getClientMimeType() ?? 'application/octet-stream',
                'size_bytes'        => $file->getSize() ?: 0,
            ]);
        }
    }

    /** @param  array<int, string>  $documentIds */
    private function removePhotos(MaintenanceRequest $maintenanceRequest, array $documentIds): void
    {
        if ($documentIds === []) {
            return;
        }

        $docs = $maintenanceRequest->documents()
            ->whereIn('id', $documentIds)
            ->get();

        foreach ($docs as $doc) {
            Storage::disk($doc->disk)->delete($doc->path);
            $doc->delete();
        }
    }
}
