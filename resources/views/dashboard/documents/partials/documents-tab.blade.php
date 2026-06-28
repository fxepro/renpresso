@php
  $sortLink = function (string $col, string $label) use ($sort, $dir) {
      $nextDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
      $url = request()->fullUrlWithQuery(['tab' => 'documents', 'sort' => $col, 'dir' => $nextDir, 'page' => null]);
      $arrow = $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
      return '<a class="doc-col-sort'.($sort === $col ? ' active' : '').'" href="'.e($url).'">'.$label.$arrow.'</a>';
  };
@endphp

<p class="db-form-hint" style="margin:0 0 16px;max-width:42rem;line-height:1.55">
  Files stored in block storage — inspections, insurance, compliance, receipts, and attachments from properties, leases, and maintenance. Lease agreement <strong>templates</strong> are managed on the <a href="{{ route('documents.index', ['tab' => 'leases']) }}">Leases</a> tab.
</p>

<div class="doc-drive">
  <div class="doc-toolbar">
    <form method="get" action="{{ route('documents.index') }}" id="docFilters">
      <input type="hidden" name="tab" value="documents">
      <input type="hidden" name="sort" value="{{ $sort }}">
      <input type="hidden" name="dir" value="{{ $dir }}">
      <input type="search" name="q" class="db-input doc-search" placeholder="Search files…" value="{{ $q }}" autocomplete="off">
      <select name="type" class="db-select" onchange="document.getElementById('docFilters').submit()">
        <option value="all" {{ $typeFilter === 'all' ? 'selected' : '' }}>All categories</option>
        @foreach($docTypes as $t)
          <option value="{{ $t }}" {{ $typeFilter === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
        @endforeach
      </select>
      <select name="location" class="db-select" onchange="document.getElementById('docFilters').submit()">
        <option value="all" {{ $locationFilter === 'all' ? 'selected' : '' }}>All locations</option>
        <option value="property" {{ $locationFilter === 'property' ? 'selected' : '' }}>Property</option>
        <option value="lease" {{ $locationFilter === 'lease' ? 'selected' : '' }}>Lease record</option>
        <option value="maintenance" {{ $locationFilter === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        <option value="followup" {{ $locationFilter === 'followup' ? 'selected' : '' }}>Follow-up</option>
      </select>
      <button type="submit" class="db-btn db-btn-primary" style="padding:9px 16px">Apply</button>
    </form>
  </div>

  @if($documents->isEmpty())
    <div class="db-empty" style="min-height:36vh;padding:48px 24px">
      <div class="db-empty-icon">📁</div>
      <h3>No files match</h3>
      <p style="color:var(--text-light)">Upload attachments from a property, lease record, or maintenance request, or clear filters.</p>
    </div>
  @else
  <div class="db-table-wrap">
    <table class="db-table" style="margin:0">
      <thead>
        <tr>
          <th class="db-col-num">#</th>
          <th style="width:40%">{!! $sortLink('name', 'Name') !!}</th>
          <th>Location</th>
          <th>Category</th>
          <th>{!! $sortLink('size', 'Size') !!}</th>
          <th>{!! $sortLink('date', 'Modified') !!}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($documents as $doc)
          @php
            $mime = $doc->mime_type ?? '';
            $icon = str_starts_with($mime, 'image/') ? '🖼' : (str_contains(strtolower($mime), 'pdf') ? '📄' : (str_starts_with($mime, 'video/') ? '🎬' : '📎'));
            $rowNum = ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration;
          @endphp
          <tr class="doc-row">
            <td class="db-col-num">{{ $rowNum }}</td>
            <td>
              <div class="doc-namecell">
                <span class="doc-icon" aria-hidden="true">{{ $icon }}</span>
                <div style="min-width:0">
                  <div class="doc-filename">{{ $doc->original_filename }}</div>
                  @if($doc->uploadedBy)
                    <div class="doc-meta">Uploaded by {{ $doc->uploadedBy->fullName() }}</div>
                  @endif
                </div>
              </div>
            </td>
            <td>
              <span class="badge badge-navy" style="font-size:11px">{{ $doc->locationLabel() }}</span>
              <div class="doc-meta" style="margin-top:4px">{{ Str::limit($doc->linkedEntityLabel(), 42) }}</div>
            </td>
            <td><span class="badge badge-grey">{{ $doc->type ?? 'other' }}</span></td>
            <td>{{ $doc->formattedSize() }}</td>
            <td>{{ $doc->created_at->format('j M Y, H:i') }}</td>
            <td class="doc-actions">
              <a href="{{ route('documents.file', $doc) }}" class="db-table-link">Open</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($documents->hasPages())
    <div style="padding:16px 18px;border-top:1px solid var(--cream-dark)">
      {{ $documents->links() }}
    </div>
  @endif
  @endif
</div>
