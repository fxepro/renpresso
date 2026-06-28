<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSentLog;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMessagesController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::withCount('sentLogs')
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'total'     => $templates->count(),
            'published' => $templates->where('is_published', true)->count(),
            'draft'     => $templates->where('is_published', false)->count(),
            'sent_total'=> EmailSentLog::where('status', 'sent')->count(),
        ];

        return view('admin.messages.index', compact('templates', 'stats'));
    }

    public function create(): View
    {
        $triggerEvents = $this->triggerEvents();

        return view('admin.messages.create', compact('triggerEvents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug'               => 'required|string|alpha_dash|unique:email_templates,slug',
            'name'               => 'required|string|max:120',
            'trigger_event'      => 'required|string',
            'trigger_days'       => 'nullable|integer|min:0|max:365',
            'trigger_direction'  => 'nullable|in:before,after,on',
            'subject'            => 'required|string|max:255',
            'body_html'          => 'required|string',
            'landlord_can_edit'  => 'boolean',
            'landlord_can_disable' => 'boolean',
            'sort_order'         => 'integer|min:0|max:255',
        ]);

        $data['is_published']        = false;
        $data['landlord_can_edit']   = $request->boolean('landlord_can_edit', true);
        $data['landlord_can_disable']= $request->boolean('landlord_can_disable', true);
        $data['created_by']          = auth()->id();
        $data['available_variables'] = $this->defaultVariables();

        EmailTemplate::create($data);

        return redirect()->route('admin.messages')->with('success', 'Template created. Publish it when ready.');
    }

    public function show(EmailTemplate $emailTemplate): View
    {
        $logs = EmailSentLog::where('email_template_id', $emailTemplate->id)
            ->with(['landlord:id,first_name,last_name', 'tenant:id,first_name,last_name'])
            ->latest('sent_at')
            ->paginate(30);

        return view('admin.messages.show', compact('emailTemplate', 'logs'));
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        $triggerEvents = $this->triggerEvents();

        return view('admin.messages.edit', compact('emailTemplate', 'triggerEvents'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:120',
            'trigger_event'        => 'required|string',
            'trigger_days'         => 'nullable|integer|min:0|max:365',
            'trigger_direction'    => 'nullable|in:before,after,on',
            'subject'              => 'required|string|max:255',
            'body_html'            => 'required|string',
            'landlord_can_edit'    => 'boolean',
            'landlord_can_disable' => 'boolean',
            'sort_order'           => 'integer|min:0|max:255',
        ]);

        $data['landlord_can_edit']    = $request->boolean('landlord_can_edit');
        $data['landlord_can_disable'] = $request->boolean('landlord_can_disable');

        $emailTemplate->update($data);

        return redirect()->route('admin.messages.show', $emailTemplate)->with('success', 'Template updated.');
    }

    public function togglePublish(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->update(['is_published' => ! $emailTemplate->is_published]);
        $label = $emailTemplate->is_published ? 'published' : 'unpublished';

        return back()->with('success', "Template {$label}.");
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->delete();

        return redirect()->route('admin.messages')->with('success', 'Template deleted.');
    }

    private function triggerEvents(): array
    {
        return [
            'rent_due_in_days'  => 'Rent due in N days (reminder)',
            'rent_overdue_days' => 'Rent overdue by N days',
            'payment_success'   => 'Payment received (confirmation)',
            'payment_failed'    => 'Payment failed',
            'late_fee_applied'  => 'Late fee applied',
            'lease_expiry_days' => 'Lease expiry in N days',
        ];
    }

    private function defaultVariables(): array
    {
        return [
            'tenant_first_name', 'tenant_name',
            'landlord_first_name', 'landlord_name',
            'property_name', 'property_address',
            'rent_amount', 'currency_code',
            'due_date', 'due_day', 'days_until_due', 'days_overdue',
            'late_fee_amount', 'lease_end_date', 'days_until_expiry',
            'platform_name',
        ];
    }
}
