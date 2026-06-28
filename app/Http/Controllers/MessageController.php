<?php

namespace App\Http\Controllers;

use App\Mail\LeaseThreadMessageMail;
use App\Mail\PropertyBroadcastMessageMail;
use App\Models\Lease;
use App\Models\Message;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isLandlord() || $user->isTenant(), 403);

        $activeTab = request('tab', 'property');
        if (! in_array($activeTab, ['property', 'lease'], true)) {
            $activeTab = 'property';
        }

        if ($user->isLandlord()) {
            $properties = Property::query()
                ->where('landlord_id', $user->id)
                ->whereHas('leases', fn ($q) => $q->whereIn('status', ['active', 'draft']))
                ->withCount([
                    'leases as unit_count' => fn ($q) => $q->whereIn('status', ['active', 'draft']),
                ])
                ->withMax(
                    ['messages as last_broadcast_at' => fn ($q) => $q->whereNull('lease_id')],
                    'created_at'
                )
                ->orderBy('name')
                ->get();

            $leases = Lease::query()
                ->with(['property', 'tenant'])
                ->whereHas('property', fn ($p) => $p->where('landlord_id', $user->id))
                ->whereIn('status', ['active', 'draft'])
                ->withCount([
                    'messages as unread_from_tenant' => fn ($q) => $q
                        ->whereNotNull('lease_id')
                        ->where('sender_id', '!=', $user->id)
                        ->whereNull('read_at'),
                ])
                ->withMax(['messages as messages_max_created_at' => fn ($q) => $q->whereNotNull('lease_id')], 'created_at')
                ->orderByDesc('messages_max_created_at')
                ->orderByDesc('leases.updated_at')
                ->get();

            return view('dashboard.messages.index', compact('properties', 'leases', 'user', 'activeTab'));
        }

        $leases = Lease::query()
            ->with(['property.landlord', 'tenant'])
            ->where('tenant_id', $user->id)
            ->whereIn('status', ['active', 'draft'])
            ->withCount([
                'messages as unread_for_me' => fn ($q) => $q
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->where(function ($q) {
                        $q->whereColumn('messages.lease_id', 'leases.id')
                            ->orWhere(function ($q2) {
                                $q2->whereNull('messages.lease_id')
                                    ->whereColumn('messages.property_id', 'leases.property_id');
                            });
                    }),
            ])
            ->withMax(['messages as messages_max_created_at' => fn ($q) => $q->whereNotNull('lease_id')], 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->orderByDesc('leases.updated_at')
            ->get();

        $propertyIds = $leases->pluck('property_id')->unique()->filter();

        $properties = Property::query()
            ->whereIn('id', $propertyIds)
            ->with('landlord')
            ->withMax(
                ['messages as last_broadcast_at' => fn ($q) => $q->whereNull('lease_id')],
                'created_at'
            )
            ->orderBy('name')
            ->get();

        return view('dashboard.messages.index', compact('leases', 'properties', 'user', 'activeTab'));
    }

    public function propertyShow(Property $property)
    {
        $this->authorize('broadcast', $property);

        $property->load(['messagingLeases.tenant']);

        $broadcasts = $property->messages()
            ->whereNull('lease_id')
            ->with('sender')
            ->orderByDesc('created_at')
            ->get();

        $leaseThreads = $property->leases()
            ->whereIn('status', ['active', 'draft'])
            ->with('tenant')
            ->withCount([
                'messages as unread_from_tenant' => fn ($q) => $q
                    ->whereNotNull('lease_id')
                    ->where('sender_id', '!=', Auth::id())
                    ->whereNull('read_at'),
            ])
            ->withMax(['messages as last_lease_message_at' => fn ($q) => $q->whereNotNull('lease_id')], 'created_at')
            ->orderByDesc('last_lease_message_at')
            ->get();

        return view('dashboard.messages.property', compact('property', 'broadcasts', 'leaseThreads'));
    }

    public function propertyStore(Request $request, Property $property)
    {
        $this->authorize('broadcast', $property);

        $validated = $request->validate([
            'body'       => 'required|string|max:5000',
            'also_email' => 'sometimes|boolean',
        ]);

        $message = $property->messages()->create([
            'property_id' => $property->id,
            'lease_id'    => null,
            'sender_id'   => Auth::id(),
            'body'        => $validated['body'],
        ]);

        $emailedCount = 0;
        if (! empty($validated['also_email'])) {
            $emailedCount = $this->emailPropertyBroadcast($property, $message);
            $message->update(['emailed_at' => $emailedCount > 0 ? now() : null]);
        }

        $flash = 'Building notice posted to all units in-app.';
        if (! empty($validated['also_email'])) {
            $flash .= $emailedCount > 0
                ? " Emailed {$emailedCount} tenant(s) (one email per person, not per lease)."
                : ' No tenant emails were sent — check addresses and mail settings.';
        }

        return redirect()->route('messages.property', $property)->with('success', $flash);
    }

    public function show(Lease $lease)
    {
        $this->authorize('view', $lease);

        $lease->load(['property.landlord', 'tenant']);

        Message::query()
            ->forLeaseThread($lease)
            ->where('sender_id', '!=', Auth::id())
            ->whereNotNull('lease_id')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::query()
            ->forLeaseThread($lease)
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $isLandlord = Auth::id() === $lease->property->landlord_id;
        $otherParty = $isLandlord ? $lease->tenant : $lease->property->landlord;

        return view('dashboard.messages.show', compact('lease', 'messages', 'otherParty', 'isLandlord'));
    }

    public function store(Request $request, Lease $lease)
    {
        $this->authorize('message', $lease);

        $lease->loadMissing('property.landlord', 'tenant');
        $isLandlord = Auth::id() === $lease->property->landlord_id;

        $rules = ['body' => 'required|string|max:5000'];
        if (! $isLandlord) {
            $rules['also_email'] = 'sometimes|boolean';
        }
        $validated = $request->validate($rules);

        $message = $lease->messages()->create([
            'property_id' => $lease->property_id,
            'lease_id'    => $lease->id,
            'sender_id'   => Auth::id(),
            'body'        => $validated['body'],
        ]);

        if (! $isLandlord && ! empty($validated['also_email'])) {
            $landlord = $lease->property->landlord;
            if ($landlord?->email) {
                try {
                    Mail::to($landlord->email)->send(new LeaseThreadMessageMail($lease, $message, Auth::user()));
                    $message->update(['emailed_at' => now()]);
                } catch (\Throwable $e) {
                    report($e);
                    $message->update(['emailed_at' => null]);
                }
            }
        }

        $flash = $isLandlord
            ? 'Reply saved to this unit\'s thread (in-app only). Use building notice to email all tenants.'
            : 'Message sent.';

        if (! $isLandlord && ! empty($validated['also_email'])) {
            $flash .= $message->fresh()->emailed_at
                ? ' A copy was emailed to your landlord.'
                : ' Email could not be sent; your message is saved in-app.';
        }

        return redirect()->route('messages.show', $lease)->with('success', $flash);
    }

    private function emailPropertyBroadcast(Property $property, Message $message): int
    {
        $sent = 0;
        $seenEmails = [];

        foreach ($property->messagingLeases()->get() as $lease) {
            $tenant = $lease->tenant;
            if (! $tenant?->email || isset($seenEmails[$tenant->email])) {
                continue;
            }

            try {
                Mail::to($tenant->email)->send(new PropertyBroadcastMessageMail(
                    $property,
                    $message,
                    Auth::user(),
                    $lease,
                ));
                $seenEmails[$tenant->email] = true;
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $sent;
    }
}
