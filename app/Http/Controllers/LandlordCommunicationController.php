<?php

namespace App\Http\Controllers;

use App\Models\EmailSentLog;
use App\Models\EmailTemplate;
use App\Models\LandlordEmailPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandlordCommunicationController extends Controller
{
    public function index(): View
    {
        $landlordId = auth()->id();

        $templates = EmailTemplate::where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        // Preload this landlord's preferences keyed by template ID
        $prefs = LandlordEmailPreference::where('landlord_id', $landlordId)
            ->get()
            ->keyBy('email_template_id');

        // Recent email activity for this landlord (last 20)
        $recentLogs = EmailSentLog::where('landlord_id', $landlordId)
            ->with('template:id,name,slug')
            ->latest('sent_at')
            ->limit(20)
            ->get();

        return view('dashboard.communication.index', compact('templates', 'prefs', 'recentLogs'));
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        abort_unless($emailTemplate->is_published, 404);
        abort_unless($emailTemplate->landlord_can_edit, 403);

        $pref = LandlordEmailPreference::firstOrNew([
            'landlord_id'       => auth()->id(),
            'email_template_id' => $emailTemplate->id,
        ]);

        return view('dashboard.communication.edit', compact('emailTemplate', 'pref'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        abort_unless($emailTemplate->is_published, 404);
        abort_unless($emailTemplate->landlord_can_edit || $emailTemplate->landlord_can_disable, 403);

        $data = $request->validate([
            'is_enabled'       => 'boolean',
            'subject_override' => 'nullable|string|max:255',
            'body_html_override' => 'nullable|string',
        ]);

        // Enforce landlord restrictions
        if (! $emailTemplate->landlord_can_disable) {
            $data['is_enabled'] = true;
        }
        if (! $emailTemplate->landlord_can_edit) {
            unset($data['subject_override'], $data['body_html_override']);
        }

        $data['is_enabled'] = $request->boolean('is_enabled', true);

        LandlordEmailPreference::updateOrCreate(
            ['landlord_id' => auth()->id(), 'email_template_id' => $emailTemplate->id],
            $data
        );

        return redirect()->route('landlord.communication.index')
            ->with('success', 'Preferences saved for "' . $emailTemplate->name . '".');
    }

    public function reset(EmailTemplate $emailTemplate): RedirectResponse
    {
        LandlordEmailPreference::where('landlord_id', auth()->id())
            ->where('email_template_id', $emailTemplate->id)
            ->delete();

        return back()->with('success', 'Restored to platform defaults.');
    }
}
