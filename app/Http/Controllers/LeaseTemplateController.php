<?php

namespace App\Http\Controllers;

use App\Models\LeaseTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaseTemplateController extends Controller
{
    public function create()
    {
        return view('dashboard.documents.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'lease_type'  => 'required|in:'.implode(',', array_keys(LeaseTemplate::LEASE_TYPES)),
            'description' => 'nullable|string|max:2000',
            'body'        => 'nullable|string|max:500000',
            'file'        => 'nullable|file|max:20480|mimes:pdf,doc,docx,txt',
        ]);

        $data = [
            'landlord_id' => Auth::id(),
            'name'        => $validated['name'],
            'lease_type'  => $validated['lease_type'],
            'description' => $validated['description'] ?? null,
            'body'        => $validated['body'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $data = array_merge($data, $this->storeUploadedFile($request->file('file')));
        }

        LeaseTemplate::create($data);

        return redirect()
            ->route('documents.index', ['tab' => 'leases'])
            ->with('success', 'Lease template created.');
    }

    public function edit(LeaseTemplate $leaseTemplate)
    {
        $this->authorizeLandlord($leaseTemplate);

        return view('dashboard.documents.templates.edit', compact('leaseTemplate'));
    }

    public function update(Request $request, LeaseTemplate $leaseTemplate)
    {
        $this->authorizeLandlord($leaseTemplate);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'lease_type'  => 'required|in:'.implode(',', array_keys(LeaseTemplate::LEASE_TYPES)),
            'description' => 'nullable|string|max:2000',
            'body'        => 'nullable|string|max:500000',
            'file'        => 'nullable|file|max:20480|mimes:pdf,doc,docx,txt',
            'remove_file' => 'sometimes|boolean',
        ]);

        $leaseTemplate->fill([
            'name'        => $validated['name'],
            'lease_type'  => $validated['lease_type'],
            'description' => $validated['description'] ?? null,
            'body'        => $validated['body'] ?? null,
        ]);

        if ($request->boolean('remove_file')) {
            $this->deleteStoredFile($leaseTemplate);
            $leaseTemplate->fill([
                'disk'              => null,
                'path'              => null,
                'original_filename' => null,
                'mime_type'         => null,
                'size_bytes'        => null,
            ]);
        }

        if ($request->hasFile('file')) {
            $this->deleteStoredFile($leaseTemplate);
            $leaseTemplate->fill($this->storeUploadedFile($request->file('file')));
        }

        $leaseTemplate->save();

        return redirect()
            ->route('documents.index', ['tab' => 'leases'])
            ->with('success', 'Lease template updated.');
    }

    public function destroy(LeaseTemplate $leaseTemplate)
    {
        $this->authorizeLandlord($leaseTemplate);

        $this->deleteStoredFile($leaseTemplate);
        $leaseTemplate->delete();

        return redirect()
            ->route('documents.index', ['tab' => 'leases'])
            ->with('success', 'Lease template deleted.');
    }

    public function file(LeaseTemplate $leaseTemplate)
    {
        $this->authorizeLandlord($leaseTemplate);

        if (! $leaseTemplate->hasFile() || ! Storage::disk($leaseTemplate->disk)->exists($leaseTemplate->path)) {
            abort(404);
        }

        return Storage::disk($leaseTemplate->disk)->response(
            $leaseTemplate->path,
            $leaseTemplate->original_filename,
            ['Content-Type' => $leaseTemplate->mime_type ?? 'application/octet-stream']
        );
    }

    private function authorizeLandlord(LeaseTemplate $leaseTemplate): void
    {
        if ($leaseTemplate->landlord_id !== Auth::id()) {
            abort(403);
        }
    }

    /** @return array<string, mixed> */
    private function storeUploadedFile(\Illuminate\Http\UploadedFile $file): array
    {
        $disk = config('filesystems.default', 'local');
        $path = $file->store('lease-templates/'.Auth::id(), $disk);

        return [
            'disk'              => $disk,
            'path'              => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getClientMimeType() ?? 'application/octet-stream',
            'size_bytes'        => $file->getSize() ?: 0,
        ];
    }

    private function deleteStoredFile(LeaseTemplate $leaseTemplate): void
    {
        if ($leaseTemplate->hasFile() && Storage::disk($leaseTemplate->disk)->exists($leaseTemplate->path)) {
            Storage::disk($leaseTemplate->disk)->delete($leaseTemplate->path);
        }
    }
}
