@php
  $accountTab = $accountTab ?? request('tab', 'profile');
  $accountSec = $accountSec ?? request('sec');
  $editTarget = $editTarget ?? $accountSec ?? $accountTab;
  $editing = $editing ?? ((string) request('edit') === (string) $editTarget);
  $canEdit = $canEdit ?? true;
  $showEdit = $showEdit ?? $canEdit;
  $canDelete = $canDelete ?? false;
  $deleteUrl = $deleteUrl ?? null;
  $deleteConfirm = $deleteConfirm ?? 'Remove this record?';
  $addLabel = $addLabel ?? 'Add';
  $hasData = $hasData ?? true;
  $editLabel = $editLabel ?? ($hasData ? 'Edit' : $addLabel);
  $query = array_filter(['tab' => $accountTab, 'sec' => $accountSec]);
  $viewUrl = route('maint.account', $query);
  $editUrl = route('maint.account', array_merge($query, ['edit' => $editTarget]));
@endphp
<div class="db-card {{ $class ?? '' }}">
  <div class="db-card-header" style="flex-wrap:wrap;gap:8px">
    <span class="db-card-title">{{ $title }}</span>
    @if(isset($headerExtra))
      <span style="margin-left:8px">{!! $headerExtra !!}</span>
    @endif
    <div style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap;align-items:center">
      @if($editing)
        <a href="{{ $viewUrl }}" class="db-btn db-btn-ghost" style="text-decoration:none;font-size:14px">Cancel</a>
      @elseif($showEdit)
        <a href="{{ $editUrl }}" class="db-btn db-btn-ghost" style="text-decoration:none;font-size:14px">{{ $editLabel }}</a>
      @endif
      @if($canDelete && $hasData && ! $editing && $deleteUrl)
        <form method="POST" action="{{ $deleteUrl }}" style="display:inline" onsubmit="return confirm(@json($deleteConfirm))">
          @csrf @method('DELETE')
          <button type="submit" class="db-btn db-btn-danger" style="font-size:14px">Delete</button>
        </form>
      @endif
    </div>
  </div>
  <div class="db-card-body">
    @if($editing)
      {{ $edit ?? '' }}
    @else
      {{ $view ?? '' }}
    @endif
  </div>
</div>
