<?php

namespace App\Http\Controllers;

use App\Models\HelplineFeedback;
use App\Services\HelpCollateralSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function videos()
    {
        return view('dashboard.help.videos');
    }

    public function collateral()
    {
        return view('dashboard.help.collateral');
    }

    public function helpline()
    {
        return view('dashboard.help.helpline', [
            'suggestions' => HelpCollateralSearch::fromConfig()->initialSuggestions(),
        ]);
    }

    public function helplineAsk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $result = HelpCollateralSearch::fromConfig()->answer($data['question']);

        return response()->json($result);
    }

    public function helplineFeedback(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'working_well' => ['nullable', 'string', 'max:5000'],
            'not_working' => ['nullable', 'string', 'max:5000'],
            'additional' => ['nullable', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        if (trim(($data['working_well'] ?? '').($data['not_working'] ?? '').($data['additional'] ?? '')) === '') {
            return back()
                ->withInput()
                ->withErrors(['feedback' => 'Please share at least one comment in the feedback form.']);
        }

        $user = $request->user();
        $role = $user->isTenant() ? 'tenant' : ($user->isMaintenance() ? 'maintenance' : ($user->isLandlord() ? 'landlord' : 'user'));

        HelplineFeedback::create([
            'user_id' => $user->id,
            'user_role' => $role,
            'working_well' => $data['working_well'] ?? null,
            'not_working' => $data['not_working'] ?? null,
            'additional' => $data['additional'] ?? null,
            'rating' => $data['rating'] ?? null,
        ]);

        return back()->with('helpline_feedback_sent', true);
    }
}
