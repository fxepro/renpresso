<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMaintenanceTeam;
use App\Models\MaintenanceInvoice;
use App\Models\MaintenanceInvoiceAttachment;
use App\Models\MaintenanceInvoiceLine;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MaintenanceInvoiceController extends Controller
{
    use ResolvesMaintenanceTeam;

    public function index(Request $request)
    {
        $team = $this->maintenanceTeamOrAbort();

        $query = $team->invoices()
            ->with(['landlord', 'property', 'maintenanceRequest'])
            ->orderByDesc('issued_at')
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->get();

        $landlords = $team->engagedLandlords()->orderBy('first_name')->get();
        $defaultCurrency = config('countries.'.$team->country_code.'.currency', 'USD');

        $panelMode = null;
        $panelInvoice = null;
        $prefillRequest = null;

        if ($request->query('panel') === 'create') {
            $panelMode = 'create';
            if ($request->query('maintenance_request_id')) {
                $prefillRequest = MaintenanceRequest::query()
                    ->where('maintenance_team_id', $team->id)
                    ->with('lease.property')
                    ->find($request->query('maintenance_request_id'));
            }
        } elseif ($request->query('panel') === 'edit' && $request->query('invoice')) {
            $panelInvoice = $team->invoices()->with('lines')->find($request->query('invoice'));
            if ($panelInvoice?->isEditable()) {
                $panelMode = 'edit';
            }
        }

        return view('dashboard.maintenance-portal.payments.invoices.index', compact(
            'team',
            'invoices',
            'landlords',
            'defaultCurrency',
            'panelMode',
            'panelInvoice',
            'prefillRequest',
        ));
    }

    public function create(Request $request)
    {
        return redirect()->route('maint.payments.invoices', array_filter([
            'panel'                  => 'create',
            'maintenance_request_id' => $request->query('maintenance_request_id'),
        ]));
    }

    public function formOptions(Request $request)
    {
        $team = $this->maintenanceTeamOrAbort();

        $data = $request->validate([
            'landlord_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $landlordId = $data['landlord_id'];
        if (! $team->isEngagedWithLandlord($landlordId)) {
            throw ValidationException::withMessages([
                'landlord_id' => 'This landlord is not on your roster.',
            ]);
        }

        $properties = $team->billablePropertiesForLandlord($landlordId);
        $requests = $team->billableMaintenanceRequestsForLandlord($landlordId);

        return response()->json([
            'properties' => $properties->map(fn (Property $p) => [
                'id'    => $p->id,
                'label' => ($p->name ?: $p->address_line1).' — '.$p->city,
            ])->values(),
            'requests' => $requests->map(fn (MaintenanceRequest $mr) => [
                'id'    => $mr->id,
                'label' => $mr->title.($mr->lease?->property ? ' · '.$mr->lease->property->name : ''),
            ])->values(),
            'hint' => $properties->isEmpty()
                ? 'No properties in your operating cities for this landlord. Assign a maintenance job first, or ask the landlord to add a property in your service area.'
                : null,
        ]);
    }

    public function store(Request $request)
    {
        $team = $this->maintenanceTeamOrAbort();
        $user = $request->user();
        $data = $this->validateInvoicePayload($request, $team);

        $invoice = DB::transaction(function () use ($team, $user, $data) {
            $number = $this->nextInvoiceNumber($team);
            $taxMinor = (int) round((float) ($data['tax'] ?? 0) * 100);

            $invoice = $team->invoices()->create([
                'landlord_id'            => $data['landlord_id'],
                'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
                'property_id'            => $data['property_id'] ?? null,
                'invoice_number'         => $number,
                'currency_code'          => $data['currency_code'],
                'status'                 => 'draft',
                'due_date'               => $data['due_date'] ?? null,
                'description'            => $data['description'] ?? null,
                'bill_to_name'           => $data['bill_to_name'] ?? null,
                'bill_to_email'          => $data['bill_to_email'] ?? null,
                'notes_customer'         => $data['notes_customer'] ?? null,
                'notes_internal'         => $data['notes_internal'] ?? null,
                'tax_minor'              => $taxMinor,
                'subtotal_minor'         => 0,
                'amount_minor'           => 0,
                'created_by'             => $user->id,
            ]);

            $this->syncLines($invoice, $data['lines']);
            $invoice->recalculateFromLines();
            $invoice->recordEvent('created', ['status' => 'draft'], $user);

            return $invoice;
        });

        return redirect()
            ->route('maint.payments.invoices.show', $invoice)
            ->with('success', 'Invoice '.$invoice->invoice_number.' created.');
    }

    public function show(MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        $this->authorizeInvoice($team, $invoice);

        $invoice->load([
            'landlord',
            'property',
            'maintenanceRequest.lease.property',
            'lines',
            'attachments.uploader',
            'events.actor',
            'paymentsReceived.landlord',
            'creator',
        ]);

        return view('dashboard.maintenance-portal.payments.invoices.show', compact('team', 'invoice'));
    }

    public function edit(MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        $this->authorizeInvoice($team, $invoice);
        abort_unless($invoice->isEditable(), 403, 'Only draft invoices can be edited.');

        return redirect()->route('maint.payments.invoices', [
            'panel'  => 'edit',
            'invoice' => $invoice->id,
        ]);
    }

    public function update(Request $request, MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        $this->authorizeInvoice($team, $invoice);
        abort_unless($invoice->isEditable(), 403, 'Only draft invoices can be edited.');

        $data = $this->validateInvoicePayload($request, $team);
        $user = $request->user();
        $taxMinor = (int) round((float) ($data['tax'] ?? 0) * 100);

        DB::transaction(function () use ($invoice, $data, $taxMinor, $user) {
            $invoice->update([
                'landlord_id'            => $data['landlord_id'],
                'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
                'property_id'            => $data['property_id'] ?? null,
                'currency_code'          => $data['currency_code'],
                'due_date'               => $data['due_date'] ?? null,
                'description'            => $data['description'] ?? null,
                'bill_to_name'           => $data['bill_to_name'] ?? null,
                'bill_to_email'          => $data['bill_to_email'] ?? null,
                'notes_customer'         => $data['notes_customer'] ?? null,
                'notes_internal'         => $data['notes_internal'] ?? null,
                'tax_minor'              => $taxMinor,
            ]);

            $this->syncLines($invoice, $data['lines']);
            $invoice->recalculateFromLines();
            $invoice->recordEvent('updated', null, $user);
        });

        return redirect()
            ->route('maint.payments.invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }

    public function destroy(MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        $this->authorizeInvoice($team, $invoice);
        abort_unless($invoice->isDraft(), 403, 'Only draft invoices can be deleted.');

        foreach ($invoice->attachments as $attachment) {
            $this->deleteAttachmentFile($attachment);
        }

        $invoice->delete();

        return redirect()
            ->route('maint.payments.invoices')
            ->with('success', 'Invoice deleted.');
    }

    public function send(Request $request, MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        $this->authorizeInvoice($team, $invoice);
        abort_unless($invoice->isDraft(), 403);

        $invoice->update([
            'status'    => 'sent',
            'issued_at' => $invoice->issued_at ?? now(),
            'sent_at'   => now(),
        ]);

        $invoice->recordEvent('sent', null, $request->user());

        return back()->with('success', 'Invoice sent.');
    }

    public function cancel(Request $request, MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        $this->authorizeInvoice($team, $invoice);
        abort_if($invoice->isCancelled(), 403);
        abort_if($invoice->status === 'paid', 403);

        $invoice->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $invoice->recordEvent('status_changed', ['status' => 'cancelled'], $request->user());

        return back()->with('success', 'Invoice cancelled.');
    }

    public function storeAttachment(Request $request, MaintenanceInvoice $invoice)
    {
        $team = $this->maintenanceTeamOrAbort();
        $this->authorizeInvoice($team, $invoice);
        abort_if($invoice->isCancelled(), 403);

        $data = $request->validate([
            'kind'    => ['required', Rule::in(MaintenanceInvoiceAttachment::KINDS)],
            'file'    => ['required', 'file', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $dir = 'maintenance-invoice-attachments/'.$invoice->id;
        $path = $file->store($dir, 'local');

        $attachment = $invoice->attachments()->create([
            'uploaded_by'       => $request->user()->id,
            'kind'              => $data['kind'],
            'file_path'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'size_bytes'        => $file->getSize(),
            'caption'           => $data['caption'] ?? null,
        ]);

        $invoice->recordEvent('attachment_added', [
            'attachment_id' => $attachment->id,
            'kind'          => $attachment->kind,
        ], $request->user());

        return back()->with('success', 'Attachment uploaded.');
    }

    public function attachmentFile(MaintenanceInvoiceAttachment $attachment)
    {
        $team = $this->maintenanceTeamOrAbort();
        $invoice = $attachment->invoice;
        abort_unless($invoice && $invoice->maintenance_team_id === $team->id, 404);

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response(
            $attachment->file_path,
            $attachment->original_filename,
            ['Content-Type' => $attachment->mime_type ?: Storage::disk('local')->mimeType($attachment->file_path)]
        );
    }

    public function destroyAttachment(Request $request, MaintenanceInvoiceAttachment $attachment)
    {
        $team = $this->maintenanceTeamOrAbort();
        $invoice = $attachment->invoice;
        abort_unless($invoice && $invoice->maintenance_team_id === $team->id, 404);
        abort_if($invoice->isCancelled(), 403);

        $this->deleteAttachmentFile($attachment);
        $attachment->delete();

        $invoice->recordEvent('attachment_removed', [
            'filename' => $attachment->original_filename,
        ], $request->user());

        return back()->with('success', 'Attachment removed.');
    }

    private function authorizeInvoice($team, MaintenanceInvoice $invoice): void
    {
        abort_unless($invoice->maintenance_team_id === $team->id, 404);
    }

    /** @return array<string, mixed> */
    private function validateInvoicePayload(Request $request, $team): array
    {
        $landlordIds = $team->engagedLandlordIds();

        $data = $request->validate([
            'landlord_id'            => [
                'required',
                'uuid',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($landlordIds) {
                    if (! $landlordIds->contains($value)) {
                        $fail('The selected landlord is not on your roster.');
                    }
                },
            ],
            'maintenance_request_id' => ['nullable', 'uuid', 'exists:maintenance_requests,id'],
            'property_id'            => ['nullable', 'uuid', 'exists:properties,id'],
            'currency_code'          => ['required', 'string', 'size:3'],
            'due_date'               => ['nullable', 'date'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'bill_to_name'           => ['nullable', 'string', 'max:200'],
            'bill_to_email'          => ['nullable', 'email', 'max:200'],
            'notes_customer'         => ['nullable', 'string', 'max:5000'],
            'notes_internal'         => ['nullable', 'string', 'max:5000'],
            'tax'                    => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines'                  => ['required', 'array', 'min:1'],
            'lines.*.description'    => ['required', 'string', 'max:500'],
            'lines.*.quantity'       => ['required', 'numeric', 'min:0.001', 'max:99999'],
            'lines.*.unit_price'     => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $data['currency_code'] = strtoupper($data['currency_code']);

        if (! empty($data['property_id'])) {
            $allowedIds = $team->billablePropertiesForLandlord($data['landlord_id'])->pluck('id');
            if (! $allowedIds->contains($data['property_id'])) {
                throw ValidationException::withMessages([
                    'property_id' => 'Property is not in your service area for this landlord.',
                ]);
            }
        }

        if (! empty($data['maintenance_request_id'])) {
            $allowedRequestIds = $team->billableMaintenanceRequestsForLandlord($data['landlord_id'])->pluck('id');
            if (! $allowedRequestIds->contains($data['maintenance_request_id'])) {
                throw ValidationException::withMessages([
                    'maintenance_request_id' => 'This request does not belong to the selected landlord.',
                ]);
            }
        }

        if (empty($data['bill_to_name'])) {
            $landlord = User::find($data['landlord_id']);
            if ($landlord) {
                $data['bill_to_name'] = $landlord->fullName();
                $data['bill_to_email'] = $data['bill_to_email'] ?? $landlord->email;
            }
        }

        return $data;
    }

    /** @param  list<array{description: string, quantity: float|string, unit_price: float|string}>  $lines */
    private function syncLines(MaintenanceInvoice $invoice, array $lines): void
    {
        $invoice->lines()->delete();

        foreach (array_values($lines) as $index => $line) {
            $qty = (float) $line['quantity'];
            $unitMinor = (int) round((float) $line['unit_price'] * 100);
            $lineTotal = MaintenanceInvoiceLine::computeLineTotalMinor($qty, $unitMinor);

            $invoice->lines()->create([
                'sort_order'       => $index,
                'description'      => $line['description'],
                'quantity'         => $qty,
                'unit_price_minor' => $unitMinor,
                'line_total_minor' => $lineTotal,
            ]);
        }
    }

    private function deleteAttachmentFile(MaintenanceInvoiceAttachment $attachment): void
    {
        if ($attachment->file_path && Storage::disk('local')->exists($attachment->file_path)) {
            Storage::disk('local')->delete($attachment->file_path);
        }
    }

    private function nextInvoiceNumber($team): string
    {
        $year = now()->format('Y');
        $count = $team->invoices()->whereYear('created_at', $year)->count() + 1;

        return 'INV-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
