@extends('dashboard.layout')
@section('page-title', 'Help — Helpline')

@push('styles')
<style>
.helpline-split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  align-items: stretch;
  min-height: calc(100vh - 180px);
}
.helpline-panel {
  display: flex;
  flex-direction: column;
  min-height: 520px;
}
.helpline-panel .db-card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
.helpline-intro {
  font-size: var(--fs-body);
  color: var(--text-mid);
  line-height: 1.55;
  margin: 0 0 16px;
}
.helpline-chat {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
  border: 1px solid var(--cream-dark);
  border-radius: 10px;
  background: var(--cream);
  overflow: hidden;
}
.helpline-chat-log {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-height: 280px;
  max-height: 420px;
}
.helpline-msg {
  max-width: 92%;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: var(--fs-body);
  line-height: 1.5;
}
.helpline-msg--bot {
  align-self: flex-start;
  background: var(--white);
  border: 1px solid var(--cream-dark);
  color: var(--text-mid);
}
.helpline-msg--user {
  align-self: flex-end;
  background: var(--terra-pale);
  border: 1px solid rgba(196, 98, 45, 0.2);
  color: var(--text-dark);
}
.helpline-msg-label {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-light);
  margin-bottom: 4px;
}
.helpline-sources {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--cream-dark);
  font-size: var(--fs-step);
  color: var(--text-light);
}
.helpline-sources strong { color: var(--text-mid); font-weight: 600; }
.helpline-sources ul { margin: 6px 0 0; padding-left: 18px; }
.helpline-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}
.helpline-chip {
  border: 1px solid var(--cream-dark);
  background: var(--white);
  color: var(--text-mid);
  font-family: 'Outfit', sans-serif;
  font-size: var(--fs-step);
  padding: 6px 12px;
  border-radius: 100px;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}
.helpline-chip:hover { border-color: var(--terra); color: var(--terra); }
.helpline-chat-compose {
  display: flex;
  gap: 10px;
  padding: 12px;
  border-top: 1px solid var(--cream-dark);
  background: var(--white);
}
.helpline-chat-compose input {
  flex: 1;
}
.helpline-chat-compose button {
  flex-shrink: 0;
  padding: 9px 18px;
}
.helpline-thinking {
  font-size: var(--fs-step);
  color: var(--text-light);
  font-style: italic;
  padding: 4px 0;
}
.helpline-feedback-form { max-width: none; gap: 16px; }
.helpline-rating {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}
.helpline-rating label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: var(--fs-body);
  font-weight: 400;
  color: var(--text-mid);
  cursor: pointer;
}
.helpline-rating input { accent-color: var(--terra); }
@media (max-width: 900px) {
  .helpline-split { grid-template-columns: 1fr; min-height: 0; }
  .helpline-panel { min-height: 0; }
  .helpline-chat-log { max-height: 320px; }
}
</style>
@endpush

@section('content')
@if(session('helpline_feedback_sent'))
  <div class="db-alert db-alert-success">Thanks — your feedback was sent to the product team.</div>
@endif
@if($errors->has('feedback'))
  <div class="db-alert db-alert-error">{{ $errors->first('feedback') }}</div>
@endif

<div class="helpline-split">
  {{-- Product Q&A (collateral search) --}}
  <div class="db-card helpline-panel">
    <div class="db-card-header">
      <span class="db-card-title">Ask about the product</span>
      <span class="badge badge-navy">Help assistant</span>
    </div>
    <div class="db-card-body">
      <p class="helpline-intro">
        Get quick answers from our help docs and collateral. This searches product guides — not your lease or payment data.
      </p>
      <div class="helpline-chat" id="helpline-chat">
        <div class="helpline-chat-log" id="helpline-log" aria-live="polite">
          <div class="helpline-msg helpline-msg--bot">
            <div class="helpline-msg-label">Assistant</div>
            Hi{{ auth()->user()->first_name ? ', '.e(auth()->user()->first_name) : '' }} — ask how {{ config('app.name') }} works, or pick a suggestion below.
          </div>
        </div>
        <div class="helpline-chips" id="helpline-chips">
          @foreach($suggestions as $chip)
            <button type="button" class="helpline-chip" data-question="{{ e($chip) }}">{{ $chip }}</button>
          @endforeach
        </div>
        <form class="helpline-chat-compose" id="helpline-ask-form">
          @csrf
          <input type="text" class="db-input" id="helpline-question" name="question" placeholder="e.g. How do I add a maintenance request?" autocomplete="off" maxlength="500" required>
          <button type="submit" class="db-form-submit" id="helpline-ask-btn">Ask</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Feedback --}}
  <div class="db-card helpline-panel">
    <div class="db-card-header">
      <span class="db-card-title">Share feedback</span>
    </div>
    <div class="db-card-body">
      <p class="helpline-intro">
        Tell us what's working and what isn't. We use this to improve the product for landlords, tenants, and maintenance teams.
      </p>
      <form method="POST" action="{{ route('help.helpline.feedback') }}" class="db-form helpline-feedback-form">
        @csrf
        <div class="db-form-group">
          <label for="working_well">What's working well?</label>
          <textarea class="db-textarea" id="working_well" name="working_well" rows="4" placeholder="Features you love, workflows that save time…">{{ old('working_well') }}</textarea>
        </div>
        <div class="db-form-group">
          <label for="not_working">What's not working?</label>
          <textarea class="db-textarea" id="not_working" name="not_working" rows="4" placeholder="Bugs, confusion, missing features…">{{ old('not_working') }}</textarea>
        </div>
        <div class="db-form-group">
          <label for="additional">Anything else? <span style="font-weight:400;color:var(--text-light)">(optional)</span></label>
          <textarea class="db-textarea" id="additional" name="additional" rows="3" placeholder="Ideas, priorities, context…">{{ old('additional') }}</textarea>
        </div>
        <div class="db-form-group">
          <label>Overall experience</label>
          <div class="helpline-rating">
            @for($i = 1; $i <= 5; $i++)
              <label>
                <input type="radio" name="rating" value="{{ $i }}" @checked((int) old('rating') === $i)>
                {{ $i }}
              </label>
            @endfor
            <span class="db-form-hint" style="margin:0">1 = poor · 5 = excellent</span>
          </div>
        </div>
        <button type="submit" class="db-form-submit">Send feedback</button>
        <p class="db-form-hint">Submitted as {{ auth()->user()->email }}. We typically review feedback within a few business days.</p>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const log = document.getElementById('helpline-log');
  const form = document.getElementById('helpline-ask-form');
  const input = document.getElementById('helpline-question');
  const btn = document.getElementById('helpline-ask-btn');
  const chips = document.getElementById('helpline-chips');
  const askUrl = @json(route('help.helpline.ask'));
  const token = @json(csrf_token());

  function appendMsg(role, html) {
    const wrap = document.createElement('div');
    wrap.className = 'helpline-msg helpline-msg--' + role;
    const label = document.createElement('div');
    label.className = 'helpline-msg-label';
    label.textContent = role === 'user' ? 'You' : 'Assistant';
    wrap.appendChild(label);
    const body = document.createElement('div');
    body.innerHTML = html;
    wrap.appendChild(body);
    log.appendChild(wrap);
    log.scrollTop = log.scrollHeight;
    return wrap;
  }

  function setLoading(on) {
    btn.disabled = on;
    input.disabled = on;
    let el = document.getElementById('helpline-thinking');
    if (on && !el) {
      el = document.createElement('div');
      el.id = 'helpline-thinking';
      el.className = 'helpline-thinking';
      el.textContent = 'Searching help docs…';
      log.appendChild(el);
      log.scrollTop = log.scrollHeight;
    } else if (!on && el) {
      el.remove();
    }
  }

  async function ask(question) {
    const q = (question || '').trim();
    if (!q) return;
    appendMsg('user', escapeHtml(q));
    input.value = '';
    setLoading(true);
    try {
      const res = await fetch(askUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ question: q }),
      });
      if (!res.ok) throw new Error('Request failed');
      const data = await res.json();
      let html = escapeHtml(data.answer || '');
      if (data.sources && data.sources.length) {
        html += '<div class="helpline-sources"><strong>Sources</strong><ul>';
        data.sources.forEach(function (s) {
          html += '<li>' + escapeHtml(s.title) + '</li>';
        });
        html += '</ul></div>';
      }
      appendMsg('bot', html);
      if (data.suggestions && data.suggestions.length && chips) {
        chips.innerHTML = '';
        data.suggestions.forEach(function (s) {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'helpline-chip';
          b.dataset.question = s;
          b.textContent = s;
          b.addEventListener('click', function () { ask(s); });
          chips.appendChild(b);
        });
      }
    } catch (e) {
      appendMsg('bot', 'Something went wrong. Please try again or use the feedback form.');
    } finally {
      setLoading(false);
    }
  }

  function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    ask(input.value);
  });

  if (chips) {
    chips.addEventListener('click', function (e) {
      const chip = e.target.closest('.helpline-chip');
      if (chip && chip.dataset.question) ask(chip.dataset.question);
    });
  }
})();
</script>
@endpush
