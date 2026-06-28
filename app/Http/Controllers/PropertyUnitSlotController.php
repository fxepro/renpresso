<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyUnitSlotController extends Controller
{
    public function update(Request $request, Property $property, int $unit_seq)
    {
        $this->authorize('update', $property);

        if (! $property->isMultiUnit() || ! $property->unit_capacity) {
            abort(404);
        }

        $cap = (int) $property->unit_capacity;
        if ($unit_seq < 1 || $unit_seq > $cap) {
            abort(404);
        }

        $validated = $request->validate([
            'label'    => 'nullable|string|max:64',
            'blurb'    => 'nullable|string|max:500',
            'bedrooms' => 'nullable|integer|min:0|max:99',
        ]);

        $meta = $property->unit_slots_meta ?? [];
        $key = (string) $unit_seq;
        $slot = $meta[$key] ?? [];

        if (array_key_exists('label', $validated)) {
            $label = $validated['label'];
            if ($label === null || $label === '') {
                unset($slot['label']);
            } else {
                $slot['label'] = $label;
            }
        }
        if (array_key_exists('blurb', $validated)) {
            $blurb = $validated['blurb'];
            if ($blurb === null || $blurb === '') {
                unset($slot['blurb']);
            } else {
                $slot['blurb'] = $blurb;
            }
        }

        if (array_key_exists('bedrooms', $validated)) {
            $beds = $validated['bedrooms'];
            if ($beds === null) {
                unset($slot['bedrooms']);
            } else {
                $slot['bedrooms'] = (int) $beds;
            }
        }

        if ($slot === []) {
            unset($meta[$key]);
        } else {
            $meta[$key] = $slot;
        }

        $property->update(['unit_slots_meta' => $meta === [] ? null : $meta]);

        return response()->json(['success' => true]);
    }
}
