<?php

namespace App\Http\Controllers;

use App\Models\SubLease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubLeaseController extends Controller
{
    public function approve(SubLease $subLease): JsonResponse
    {
        $this->authorizeSubLease($subLease);

        if ($subLease->status !== 'pending_landlord_approval') {
            return response()->json(['message' => 'This sub-lease is not awaiting approval.'], 422);
        }

        $subLease->update([
            'status'               => 'active',
            'landlord_approved_at' => now(),
            'landlord_rejection_reason' => null,
        ]);

        return response()->json(['success' => true, 'status' => 'active']);
    }

    public function reject(Request $request, SubLease $subLease): JsonResponse
    {
        $this->authorizeSubLease($subLease);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if (! in_array($subLease->status, ['pending_landlord_approval', 'draft'], true)) {
            return response()->json(['message' => 'This sub-lease cannot be rejected.'], 422);
        }

        $subLease->update([
            'status'                    => 'cancelled',
            'landlord_rejection_reason' => $validated['reason'] ?? 'Rejected by landlord.',
            'landlord_approved_at'      => null,
        ]);

        return response()->json(['success' => true, 'status' => 'cancelled']);
    }

    private function authorizeSubLease(SubLease $subLease): void
    {
        $subLease->loadMissing('parentLease.property');
        abort_unless(
            auth()->id() === $subLease->parentLease->property->landlord_id,
            403
        );
    }
}
