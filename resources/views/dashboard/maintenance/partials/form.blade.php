@php
  $mr = $maintenanceRequest ?? null;
  $isEdit = (bool) $mr;
@endphp

<div class="db-form-group">
  <label for="mr_category">Category <span class="req">*</span></label>
  <select name="category" id="mr_category" class="db-select" required>
    <option value="">Select category</option>
    @foreach($categories as $cat)
      <option value="{{ $cat }}" {{ old('category', $mr?->category) === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
    @endforeach
  </select>
  @error('category')<span class="db-form-error">{{ $message }}</span>@enderror
</div>

@if(! $isEdit && isset($leases) && $leases->isNotEmpty())
<div class="db-form-group">
  <label for="lease_id">Lease <span class="req">*</span></label>
  <select name="lease_id" id="lease_id" class="db-select" required>
    <option value="">Select lease</option>
    @foreach($leases as $l)
      <option value="{{ $l->id }}" {{ old('lease_id') === $l->id ? 'selected' : '' }}>
        {{ $l->property->name }} — {{ $l->tenant?->fullName() ?? 'Tenant' }}@if($l->unit_label) · {{ $l->displayUnit() }}@endif
      </option>
    @endforeach
  </select>
  @error('lease_id')<span class="db-form-error">{{ $message }}</span>@enderror
</div>
@elseif(! $isEdit && isset($lease) && $lease)
  <div class="db-form-group">
    <label>Property</label>
    <p style="margin:0;font-size:15px"><strong>{{ $lease->property->name }}</strong> · {{ $lease->property->city }}</p>
  </div>
@endif

<div class="db-form-group">
  <label for="mr_title">Title <span class="req">*</span></label>
  <input type="text" name="title" id="mr_title" class="db-input" maxlength="200" required
    value="{{ old('title', $mr?->title) }}" placeholder="e.g. Leaking tap in bathroom">
  @error('title')<span class="db-form-error">{{ $message }}</span>@enderror
</div>

<div class="db-form-group">
  <label for="mr_description">Description <span class="req">*</span></label>
  <textarea name="description" id="mr_description" class="db-textarea" rows="6" required
    placeholder="Describe the issue, when it started, and any access instructions.">{{ old('description', $mr?->description) }}</textarea>
  @error('description')<span class="db-form-error">{{ $message }}</span>@enderror
</div>

<div class="db-form-group">
  <label for="mr_photos">Photos</label>
  <input type="file" name="photos[]" id="mr_photos" class="db-input" accept="image/*" multiple>
  <span class="db-form-hint">Up to 12 images, 10 MB each. {{ $isEdit ? 'Add more photos below.' : 'Photos help your landlord or maintenance team diagnose the issue.' }}</span>
  @error('photos')<span class="db-form-error">{{ $message }}</span>@enderror
  @error('photos.*')<span class="db-form-error">{{ $message }}</span>@enderror
</div>

@if($isEdit && $mr->documents->isNotEmpty())
<div class="db-form-group">
  <label>Current photos</label>
  <div class="mr-photos" style="margin-bottom:10px">
    @foreach($mr->documents as $doc)
      @if(str_starts_with((string) $doc->mime_type, 'image/'))
        <label style="display:inline-block;position:relative;cursor:pointer" title="Remove {{ $doc->original_filename }}">
          <input type="checkbox" name="remove_photos[]" value="{{ $doc->id }}" style="position:absolute;top:8px;left:8px;z-index:2">
          <img src="{{ route('documents.file', $doc) }}" alt="" style="max-width:120px;max-height:120px;object-fit:cover;border-radius:8px;border:2px solid var(--cream-dark)">
        </label>
      @endif
    @endforeach
  </div>
  <span class="db-form-hint">Check photos to remove when you save.</span>
</div>
@endif
