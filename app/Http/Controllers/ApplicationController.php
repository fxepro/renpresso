<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BackgroundCheck;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $this->authorize('view', $property);

        $validated = $request->validate([
            'first_name'                  => 'required|string|max:100',
            'last_name'                   => 'required|string|max:100',
            'email'                       => 'required|email|max:255',
            'phone'                       => 'nullable|string|max:30',
            'move_in_date'                => 'nullable|date',
            'monthly_income_minor_units'  => 'nullable|integer|min:0',
            'income_currency'             => 'nullable|string|size:3',
            'message'                     => 'nullable|string|max:2000',
            'target_unit_label'           => $property->isMultiUnit()
                ? 'required|string|max:64'
                : 'nullable|string|max:64',
        ]);

        if (! $property->isMultiUnit()) {
            $validated['target_unit_label'] = null;
        }

        $application = $property->applications()->create($validated);

        return response()->json([
            'success'          => true,
            'application_id'   => $application->id,
        ]);
    }

    public function updateStatus(Request $request, Application $application)
    {
        $this->authorize('view', $application->property);

        $request->validate([
            'status'          => 'required|in:pending,reviewing,approved,rejected',
            'landlord_notes'  => 'nullable|string|max:1000',
        ]);

        $application->update([
            'status'         => $request->status,
            'landlord_notes' => $request->landlord_notes,
            'reviewed_at'    => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function requestCheck(Request $request, Application $application)
    {
        $this->authorize('view', $application->property);

        $request->validate([
            'type'   => 'required|in:credit,criminal,eviction,right_to_rent,employment,references,document_upload',
            'method' => 'required|in:checkr,experian,transunion,document_upload',
            'notes'  => 'nullable|string|max:500',
        ]);

        $check = $application->backgroundChecks()->create([
            'property_id' => $application->property_id,
            'type'        => $request->type,
            'method'      => $request->method,
            'notes'       => $request->notes,
            'status'      => 'pending',
        ]);

        // TODO: if method !== document_upload, call provider API
        // e.g. Checkr::invite($application->email, $check->id)

        return response()->json(['success' => true, 'check' => $check]);
    }

    public function updateCheck(Request $request, BackgroundCheck $check)
    {
        $this->authorize('view', $check->property);

        $request->validate([
            'status' => 'required|in:requested,pending,passed,failed,manual_review',
            'notes'  => 'nullable|string|max:500',
        ]);

        $check->update([
            'status'       => $request->status,
            'notes'        => $request->notes,
            'completed_at' => in_array($request->status, ['passed','failed']) ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }
}
