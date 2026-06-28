@php
  $record = $documentsByType[$docType] ?? null;
  $status = $record?->status ?? 'none';
  $statusBadge = match ($status) {
    'verified' => 'green',
    'pending'  => 'gold',
    'rejected' => 'red',
    default    => 'grey',
  };
  $canEditDoc = ! $record || $record->isEditable();
  $hasData = (bool) ($record?->file_path);
  $accountTab = $accountTab ?? request('tab');
  $accountSec = $accountSec ?? request('sec');
  if ($accountTab === 'trade') {
    $accountSec = $docType;
  }
@endphp
@component('dashboard.maintenance-portal.account.partials.section-shell', [
  'title' => $docDef['label'],
  'accountTab' => $accountTab,
  'accountSec' => $accountSec,
  'editTarget' => $docType,
  'canEdit' => $canEditDoc,
  'canDelete' => $canEditDoc && $hasData,
  'hasData' => $hasData,
  'addLabel' => 'Upload',
  'deleteUrl' => $hasData ? route('maint.account.documents.destroy', $record) : null,
  'deleteConfirm' => 'Remove this document upload? You can upload again afterward.',
  'headerExtra' => '<span class="badge badge-'.$statusBadge.'">'.($record?->statusLabel() ?? 'Not submitted').'</span>',
  'class' => 'rm-doc-card',
])
  @slot('view')
    @if($record && $record->status === 'rejected' && $record->rejection_reason)
      <div class="db-alert db-alert-error" style="margin-bottom:12px">{{ $record->rejection_reason }}</div>
    @endif
    <table class="rm-acc-table">
      <tbody>
        <tr><th>Status</th><td><span class="badge badge-{{ $statusBadge }}">{{ $record?->statusLabel() ?? 'Not submitted' }}</span></td></tr>
      @if($record?->reference_number)
        <tr><th>Reference</th><td>{{ $record->reference_number }}</td></tr>
      @endif
      @if($record?->expires_on)
        <tr><th>Expires</th><td>{{ $record->expires_on->format('M j, Y') }}</td></tr>
      @endif
      @if($record?->submitted_at)
        <tr><th>Submitted</th><td>{{ $record->submitted_at->format('M j, Y') }}</td></tr>
      @endif
        <tr><th>File</th><td>
          @if($hasData)
            <a href="{{ route('maint.account.documents.file', $record) }}" class="db-btn db-btn-ghost" style="font-size:13px;padding:6px 12px;text-decoration:none" target="_blank" rel="noopener">View file</a>
          @else
            <span style="color:var(--text-light)">Not uploaded</span>
          @endif
        </td></tr>
      </tbody>
    </table>
    @if(! empty($docDef['hint']))
      <p class="db-form-hint" style="margin-top:12px">{{ $docDef['hint'] }}</p>
    @endif
  @endslot
  @slot('edit')
    <form method="POST" action="{{ route('maint.account.documents.store', $docType) }}" class="db-form db-form--wide" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="redirect_tab" value="{{ $accountTab }}">
      <input type="hidden" name="redirect_sec" value="{{ $accountSec }}">
      <input type="hidden" name="edit" value="{{ $docType }}">
      @if(! empty($docDef['fields']['reference_number']))
        <div class="db-form-group">
          <label>{{ $docDef['fields']['reference_number'] }}</label>
          <input type="text" name="reference_number" class="db-input" maxlength="120" value="{{ old('reference_number', $record?->reference_number) }}">
        </div>
      @endif
      @if(! empty($docDef['fields']['expires_on']))
        <div class="db-form-group">
          <label>{{ $docDef['fields']['expires_on'] }}</label>
          <input type="date" name="expires_on" class="db-input" value="{{ old('expires_on', $record?->expires_on?->format('Y-m-d')) }}">
        </div>
      @endif
      <div class="db-form-group">
        <label>{{ $hasData ? 'Replace file' : 'Upload file' }} @if(! $hasData)<span class="req">*</span>@endif</label>
        <input type="file" name="file" class="db-input" accept=".jpg,.jpeg,.png,.webp,.pdf" {{ $hasData ? '' : 'required' }}>
        <span class="db-form-hint">{{ $docDef['hint'] }}</span>
      </div>
      <button type="submit" class="db-form-submit">{{ $hasData ? 'Submit replacement for review' : 'Submit for review' }}</button>
    </form>
  @endslot
@endcomponent
