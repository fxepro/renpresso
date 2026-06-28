<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PropertyMediaController extends Controller
{
    public function storePhoto(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $request->validate([
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:10240'],
            'unit_seq' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $custom = [];
        if ($request->filled('unit_seq')) {
            $seq = (int) $request->input('unit_seq');
            if (! $property->isMultiUnit() || ! $property->unit_capacity) {
                return response()->json([
                    'message' => 'Unit-specific photos are only for multi-unit properties with licensed capacity set.',
                ], 422);
            }
            $cap = (int) $property->unit_capacity;
            if ($seq < 1 || $seq > $cap) {
                return response()->json(['message' => 'Invalid unit slot.'], 422);
            }
            $custom['unit_seq'] = $seq;
        }

        $add = $property->addMediaFromRequest('file');
        if ($custom !== []) {
            $add = $add->withCustomProperties($custom);
        }
        $media = $add->toMediaCollection('photos');

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->file_name,
                'unit_seq' => $media->getCustomProperty('unit_seq'),
            ],
        ]);
    }

    public function storeVideo(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $request->validate([
            'file' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
        ]);

        $media = $property
            ->addMediaFromRequest('file')
            ->toMediaCollection('videos');

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->file_name,
            ],
        ]);
    }

    public function destroy(Property $property, Media $media)
    {
        $this->authorize('update', $property);

        abort_unless(
            $property->media()->whereKey($media->getKey())->exists(),
            404
        );

        $media->delete();

        return response()->json(['success' => true]);
    }
}
