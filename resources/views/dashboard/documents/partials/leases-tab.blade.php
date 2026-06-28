@php
  $templateStats = $templateStats ?? ['total' => 0, 'master' => 0, 'sub_lease' => 0, 'short_term' => 0];
  $templates = $templates ?? collect();
@endphp

<p class="db-form-hint" style="margin:0 0 16px;max-width:42rem;line-height:1.55">
  Reusable lease agreement templates for new master leases, sub-leases, and short-term stays. Use these when creating or renewing a tenancy — not for one-off uploads (those live under <strong>Documents</strong>).
</p>

@if($templateStats['total'] > 0)
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-label">Templates</div>
    <div class="db-stat-value">{{ $templateStats['total'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Master lease</div>
    <div class="db-stat-value">{{ $templateStats['master'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Sub-lease</div>
    <div class="db-stat-value">{{ $templateStats['sub_lease'] }}</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-label">Short-term</div>
    <div class="db-stat-value">{{ $templateStats['short_term'] }}</div>
  </div>
</div>
@endif

<div class="db-card">
  @if($templates->isEmpty())
    <div class="db-empty" style="min-height:36vh;padding:48px 24px">
      <div class="db-empty-icon">📋</div>
      <h3>No lease templates yet</h3>
      <p style="color:var(--text-light)">Create a master, sub-lease, or short-term template with optional PDF/DOC upload and clause text.</p>
      <a href="{{ route('lease-templates.create') }}" class="db-btn db-btn-primary">+ New template</a>
    </div>
  @else
  <div class="db-table-wrap">
    <table class="db-table">
      <thead>
        <tr>
          <th class="db-col-num">#</th>
          <th>Name</th>
          <th>Type</th>
          <th>File</th>
          <th>Updated</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($templates as $tpl)
        <tr>
          <td class="db-col-num">{{ $loop->iteration }}</td>
          <td>
            <strong>{{ $tpl->name }}</strong>
            @if($tpl->description)
              <div class="doc-meta">{{ Str::limit($tpl->description, 80) }}</div>
            @endif
          </td>
          <td><span class="badge badge-navy">{{ $tpl->leaseTypeLabel() }}</span></td>
          <td>
            @if($tpl->hasFile())
              <a href="{{ route('lease-templates.file', $tpl) }}" class="db-table-link">{{ $tpl->original_filename }}</a>
              @if($tpl->formattedSize())
                <div class="doc-meta">{{ $tpl->formattedSize() }}</div>
              @endif
            @elseif($tpl->body)
              <span style="color:var(--text-light);font-size:13px">Text only</span>
            @else
              <span style="color:var(--text-light)">—</span>
            @endif
          </td>
          <td>{{ $tpl->updated_at->format('j M Y') }}</td>
          <td class="doc-actions" style="white-space:nowrap">
            <a href="{{ route('lease-templates.edit', $tpl) }}" class="db-table-link" style="margin-right:12px">Edit</a>
            <form method="POST" action="{{ route('lease-templates.destroy', $tpl) }}" style="display:inline" onsubmit="return confirm('Delete this lease template?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="db-table-link" style="background:none;border:none;padding:0;cursor:pointer;color:var(--terra)">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
